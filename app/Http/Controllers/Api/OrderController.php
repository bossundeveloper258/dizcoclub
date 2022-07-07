<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderGuests;
use App\Models\OrderErrors;
use App\Models\OrderPayments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Mail;

class OrderController extends BaseController
{
    //
    public function __construct()
    {
        // $this->middleware('admin');
    }

    public function index(){
        return view('order');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'event_id' => 'required',
            'quantity' => 'required',
            'clients' => 'present|array',
            'clients.*.name' => 'required|string',
            // 'clients.*.lastname' => 'required|string',
            'clients.*.dni' => 'required|string',
        ]);
        
        if($validator->fails()) {          
            return $this->sendError('Error Validacion', ['error'=> $validator->errors() ]);
        }

        try {
            $event = Event::find($request->event_id);
            if(!$event) return $this->sendError('Evento no existe', ['error'=> []] , 400);
            
            $now = strtotime(Carbon::now());
            $event_date = strtotime($event->date .' '. $event->time);

            if($now >= $event_date) return $this->sendError('El Evento ya inicio, no se puedo realizar compra', ['error'=> []] , 400);
            
            $orders = Order::where('event_id', '=' , $event->id)->get();
            
            if($event->isdiscount){
                $q = 0;
                foreach ($orders as $key => $order) 
                {
                    $q += $order->discount_stock;
                }

                $q_total_disponible = $event->stock - $q;

                if( $q_total_disponible <= $request->quantity ) return $this->sendError('Supera la cantidad permitira de promocion', ['error'=> []] , 400);                
                
            }
            
            $to_email = "";

            foreach ($request->clients as $key => $g) {
                if($key == 0) {
                    if( $g['email'] == "") return $this->sendError('El primer cliente debe ingresar su correo electronico', ['error'=> []] , 400);
                    $to_email = $g['email'];
                }
            }

            $userId = $request->user_id;

            $cryp_event = Crypt::encryptString(json_encode($event));
            
            $order_new = Order::create([
                'event_id'      => $event->id,
                'user_id'       => $userId ? $userId : null,
                'quantity'      => $request->quantity,
                'total'         => $request->quantity * $event->price,
                'token'         => $cryp_event
            ]);

            $clients = array();

            foreach ($request->clients as $key => $g) {
                    
                $order_g = OrderGuests::create([
                    'order_id'  => $order_new->id,
                    'name'      => $g['name'], 
                    'lastname'  => isset($g['lastname']) ? $g['lastname'] : "",
                    'email'     => isset($g['email']) ? $g['email'] : "",
                    'dni'       => $g['dni'], 
                    'hash'      => "",
                    'qr_path'   => ""
                ]);

                $ticket = str_pad( 1000 > $order_g->id ? (1000 + $order_g->id) : $order_g->id, 8, "0", STR_PAD_LEFT);

                OrderGuests::where("id" , "=" , $order_g->id )
                    ->update(['ticket' =>  $ticket]);
                
            }

            return $this->sendResponse(array( "order" => $order_new->id), 'Orden creado correctamente');

        } catch (\Throwable $th) {
            return $this->sendError('Error del servidor', ['error'=> $th] , 404);
        }
    }

    public function paymentOptions(Request $request){
        $validator = Validator::make($request->all(),[
            'total' => 'required',
            'event_id' => 'required',
        ]);

        $event = Event::find($request->event_id);
        if(!$event) return $this->sendError('Evento no existe', ['error'=> []] , 400);
        
        $now = strtotime(Carbon::now());
        $event_date = strtotime($event->date .' '. $event->time);

        if($now >= $event_date) return $this->sendError('El Evento ya inicio, no se puedo realizar compra', ['error'=> []] , 400);
        
        if($validator->fails()) {          
            return $this->sendError('Error Validacion', ['error'=> $validator->errors() ]);
        }

        $token = $this->generateToken();

        $totalAmount = $event->price * $request->total;

        if($event->isdiscount){
            $orders = Order::select('orders.*')
                ->join('order_payments', 'order_payments.order_id', '=', 'orders.id')    
                ->where('event_id', '=' , $event->id)->get();
            $q = 0;
            foreach ($orders as $key => $order) 
            {
                $q += $order->quantity;
            }

            $q_total_disponible = $event->stock - $q;

            if( $q_total_disponible <= $request->total){
                return $this->sendError('Supera la cantidad permitira de promocion', ['error'=> []] , 400);
            }else{
                $totalAmount = ($event->price * $request->total) * ( 1 -($event->discount / 100)) ;
            }
        }

        $totalAmount = number_format((float)$totalAmount, 2, '.', '');

        $response = array(
            "session"           => $this->generateSesion($totalAmount , $token),
            "purchaseNumber"    => config('visa.VISA_PUCHARSERNUMBER'),
            "merchantid"        => config('visa.VISA_MERCHANT_ID'),
            "totalAmount"       => $totalAmount
        );

        return $this->sendResponse($response, 'Session Nuibiz');
    }

    public function payment(Request $request){

        // 
        $validator = Validator::make($request->all(),[
            'transactionToken' => 'required',
            'customerEmail'=> 'required',
        ]);

        if($validator->fails()) {          
            $this->createOrderError("No se recibio transactionToken y customerEmail de Niubiz" , null , null);
            return Redirect::to(env('APP_URL').'/payment-error');
        }

        $orderId  = $request->query('o');
        
        try {
        
            if( $orderId == "" ){
                $this->createOrderError("Orden no creada" , $request->transactionToken , $request->customerEmail);
                return Redirect::to(env('APP_URL').'/payment-error');
            }

            $order = Order::with('event')->find($orderId);
            if( $orderId == null){
                $this->createOrderError("Orden no existe" , $request->transactionToken , $request->customerEmail);
                return Redirect::to(env('APP_URL').'/payment-error');
            }

            OrderPayments::create([
                "transactionToken"  => $request->transactionToken,
                "customerEmail"     => $request->customerEmail,
                "order_id"          => $order->id
            ]);

            $_clients = OrderGuests::where("order_id" , "=" , $order->id)->get();

            $clients = array();

            $to_email = "";

            $extension_qr = "png";

            foreach ($_clients as $key => $client) {
                # code...
                if($key == 0) $to_email = $client->email;
                $_token = Str::random(25)."-".$this->base64url_encode($client->dni);
                $clients[] = (object) array( 
                    "qr"  => env('APP_URL') .'/'.'qrcodes/' .$_token.'.'.$extension_qr,
                    "ticket"  => $client->ticket,
                    "name"  => $client->name,
                    "dni"  => $client->dni
                );

                OrderGuests::where("id" , "=" , $client->id )
                    ->update(['qr_path' => 'qrcodes/' .$_token.'.'.$extension_qr , 'hash' => $_token]);

                $html = QrCode::format($extension_qr)->size(300)->generate(env('APP_URL').'/validate-token'.'/'.$_token.'', public_path('/qrcodes/') .$_token.'.'.$extension_qr);
            }

            

            Mail::to($to_email)->send(
                new \App\Mail\OrderMail( 
                    $order->event->title, 
                    Carbon::parse($order->event->date)->format('d F Y'), 
                    Carbon::parse($order->event->time)->format('H:i'), 
                    env('APP_URL') .'/public/'. $order->event->avatar_path, 
                    $clients)
                );

            // http://localhost:4200/
            // env('APP_URL')
            return Redirect::to(env('APP_URL').'/payment-success/'.$order->token);

        } catch (\Throwable $th) {
            $this->createOrderError("Error codigo" , null , null);
            return Redirect::to(env('APP_URL').'/payment-error');
        }
    }

    public function paymentSuccess(Request $request){
        $validator = Validator::make($request->all(),[
            't' => 'required',
        ]);

        if($validator->fails()) {          
            return $this->sendError('No existe este pago', ['error'=> []] , 400);
        }
        try {
            $decrypted = Crypt::decryptString($request->t);
            return $this->sendResponse(json_decode($decrypted) , '');
        } catch (DecryptException $e) {
            return $this->sendError('No existe este pago', ['error'=> []] , 400);
        }

    }

    public function tickets()
    {
        $userId = Auth::id();

        $orders = OrderGuests::with(['order', 'order.event'])->select('order_guests.*') 
                ->join('orders', 'order_guests.order_id', '=', 'orders.id') 
                ->join('order_payments', 'order_payments.order_id', '=', 'orders.id')    
                ->where('orders.user_id', '=' , $userId)->get();

        $_tickets = array();        
        foreach ($orders as $key => $order) {
            $_tickets[] = array(
                "name" => $order->name,
                "lastname" => $order->lastname,
                "email" => $order->email,
                "dni" => $order->dni,
                "qr_path" => $order->qr_path,
                "ticket" => $order->ticket,
                "event_path" => $order->order->event->avatar_path,
                "event_title" => $order->order->event->title,
                "id" => $order->hash
            );
        }
        return $this->sendResponse($_tickets, '');
    }

    public function ticketByToken($token)
    {

        $userId = Auth::id();
        $admin = Auth::user()->isadmin;
        $orders = OrderGuests::with(['order', 'order.event'])->select('order_guests.*') 
                ->join('orders', 'order_guests.order_id', '=', 'orders.id') 
                ->join('order_payments', 'order_payments.order_id', '=', 'orders.id');
        if($admin == 1) $orders = $orders->where('orders.user_id', '=' , $userId);
        
        $orders = $orders->where('order_guests.hash', '=' , $token)
                ->get();

        $_tickets = array();        
        foreach ($orders as $key => $order) {
            $_tickets[] = array(
                "name" => $order->name,
                "lastname" => $order->lastname,
                "email" => $order->email,
                "dni" => $order->dni,
                "qr_path" => $order->qr_path,
                "ticket" => $order->ticket,
                "event_path" => $order->order->event->avatar_path,
                "event_title" => $order->order->event->title,
                "event_date" => $order->order->event->date,
                "event_time" => $order->order->event->time,
                "id" => $order->hash
            );
        }
        return $this->sendResponse($_tickets, '');
    }

    public function sendemailqr(Request $request)
    {
        $orderId  = $request->query('order');
        $order = Order::with('event')->find($orderId);
        $_clients = OrderGuests::where("order_id" , "=" , $orderId)->get();
        $clients = array();
        $to_email = "";
        foreach ($_clients as $key => $client) {

            if($key == 0) $to_email = $client->email;
            $clients[] = (object) array( 
                "qr"  => env('APP_URL') .'/'.$client->qr_path,
                "ticket"  => $client->ticket,
                "name"  => $client->name,
                "dni"  => $client->dni
            );
        }
        
        Mail::to($to_email)->send(
            new \App\Mail\OrderMail( 
                $order->event->title, 
                Carbon::parse($order->event->date)->format('d F Y'), 
                Carbon::parse($order->event->time)->format('H:i'), 
                env('APP_URL') .'/public/'. $order->event->avatar_path, 
                $clients)
            );
        return $this->sendResponse([] , '');
    }

    /*=======================================================================================*/

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    private function generateToken() {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => config('visa.VISA_URL_SECURITY'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            'Authorization: '.'Basic '.base64_encode(config('visa.VISA_USER').":".config('visa.VISA_PWD'))
            ),
        ));
        $response = curl_exec($curl);

        return $response;
    }

    private function generateSesion($amount, $token) {
        $session = array(
            'amount' => $amount,
            'antifraud' => array(
                'clientIp' => $_SERVER['REMOTE_ADDR'],
                'merchantDefineData' => array(
                    'MDD4' => "bossun258@gmail.com",
                    'MDD33' => "DNI",
                    'MDD34' => '47152795'
                ),
            ),
            'channel' => 'web',
        );
        $json = json_encode($session);
        $response = json_decode($this->postRequest(config('visa.VISA_URL_SESSION') . config('visa.VISA_MERCHANT_ID'), $json, $token));
        return $response->sessionKey;
    }

    private function generateAuthorization($amount, $purchaseNumber, $transactionToken, $token) {
        $data = array(
            'antifraud' => null,
            'captureType' => 'manual',
            'channel' => 'web',
            'countable' => true,
            'order' => array(
                'amount' => $amount,
                'currency' => 'PEN',
                'purchaseNumber' => $purchaseNumber,
                'tokenId' => $transactionToken
            ),
            'recurrence' => null,
            'sponsored' => null
        );
        $json = json_encode($data);
        $session = json_decode($this->postRequest(config('visa.VISA_URL_AUTHORIZATION'). config('visa.VISA_MERCHANT_ID'), $json, $token));
        return $session;
    }

    private function postRequest($url, $postData, $token) {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                'Authorization: '.$token,
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => $postData
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function createOrderError($msg , $transactionToken , $customerEmail, $order_id = null,$total = null ){
        OrderErrors::create([
            "order_id" => $order_id,
            "transactionToken" => $transactionToken,
            "customerEmail" => $customerEmail,
            "total" => $total,
            "message" => $msg,
        ]);
    }
}
