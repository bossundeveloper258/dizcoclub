<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
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

    public function index()
    {
        return view('order');
    }

    public function paymentOptions(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'total' => 'required',
            'event_id' => 'required',
        ]);

        if($validator->fails()) {          
            return $this->sendError('Error Validacion', ['error'=> $validator->errors() ]);
        }

        $calculate = $this->calculateAmount($request->event_id, $request->total);

        if( $calculate["calculate"] ){
            $response = array(
                "session"           => "",
                "purchaseNumber"    => config('visa.VISA_PUCHARSERNUMBER'),
                "merchantid"        => config('visa.VISA_MERCHANT_ID'),
                "totalAmount"       => $calculate["total"]
            );
            return $this->sendResponse($response, 'Session Nuibiz');
        }else{
            return $this->sendError( $calculate["message"], ['error'=> []] , 400);
        }

       
    }

    private function calculateAmount($event_id ,  $total)
    {
        $event = Event::find($event_id);
        if(!$event) return $this->sendError('Evento no existe', ['error'=> []] , 400);
        
        $now = strtotime(Carbon::now());
        $event_date = strtotime($event->date .' '. $event->time);

        $date = Carbon::now();
        $date_event = Carbon::parse(  $event->date)->subDays(3);
        if($event->isdiscount){
            if( strtotime( $date ) > strtotime($date_event) ){
                $event["isdiscount"] = false;
            }
        }

        if($now >= $event_date) return  array("calculate"=> false , "total"=> 0 ,"message" => 'El Evento ya inicio, no se puedo realizar compra');
        
        $totalAmount = $event->price * $total;

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

            if( $q_total_disponible <= $total){
                return array("calculate"=> false , "total"=> 0 ,"message" => 'Supera la cantidad permitira de promocion');
            }else{
                $totalAmount = ($event->price * $total) * ( 1 -($event->discount / 100)) ;
            }
        }

        return  array("calculate"=> true , "total"=> number_format((float)$totalAmount, 2, '.', '') ,"message" => '');
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
                'token'         => $cryp_event,
            ]);

            $clients = array();

            $currentEmail = "";
            $currentDNI = "";

            foreach ($request->clients as $key => $g) {
                if( $currentEmail  == "" )  $currentEmail = $g['email'];
                if( $currentDNI  == "" )  $currentDNI = $g['dni'];

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
            
            $codCli = "D".str_pad( "-".$currentDNI, 10, "0", STR_PAD_LEFT);
            $isAuth = $userId ? true : false;
            $diffdate = 0;
            if($userId)
            {
                $user = User::find($userId);
                $date = Carbon::parse($user->created_at);
                $now = Carbon::now();

                $diffdate = $date->diffInDays($now);
            }
            
            $token = $this->generateToken();

            $calculate = $this->calculateAmount($request->event_id, $request->quantity);

            

            $totalAmount = 0;

            if( $calculate["calculate"] ){

                $totalAmount = $calculate["total"];

            }else{
                return $this->sendError( $calculate["message"], ['error'=> []] , 400);
            }

            $session = $this->generateSesion($totalAmount , $token, $currentEmail, $codCli, $isAuth, $diffdate, $currentDNI);

            return $this->sendResponse(
                array( 
                    "session" => $session,
                    "order" => $order_new->id,
                    "merchantid"        => config('visa.VISA_MERCHANT_ID')
            ), 
                'Orden creado correctamente');

        } catch (\Throwable $th) {
            return $this->sendError('Error del servidor', ['error'=> $th] , 404);
        }
    }

    public function payment(Request $request){

        // 
        $validator = Validator::make($request->all(),[
            'transactionToken' => 'required',
            'customerEmail'=> 'required',
        ]);

        if($validator->fails()) {          
            $this->createOrderError($request->description , null , null);
            return Redirect::to(env('APP_URL').'/payment-error?d='.$request->description);
        }

        $orderId  = $request->query('o');
        $amount  = $request->query('a');

        $token = $this->generateToken();

        try {
        
            if( $orderId == "" ){
                $this->createOrderError("Orden no creada" , $request->transactionToken , $request->customerEmail);
                return Redirect::to(env('APP_URL').'/payment-error?d=Orden no creada');
            }

            $order = Order::with('event')->find($orderId);
            if( $orderId == null){
                $this->createOrderError("Orden no existe" , $request->transactionToken , $request->customerEmail);
                return Redirect::to(env('APP_URL').'/payment-error?d=Orden no existe');
            }

            $authorization = $this->generateAuthorization($amount, $orderId, $request->transactionToken, $token);

            if( !isset( $authorization->dataMap ) ){
                $message = isset($authorization->data->ACTION_DESCRIPTION) ?  $authorization->data->ACTION_DESCRIPTION : "Operacion no permitida";
                $this->createOrderError($message , $request->transactionToken , $request->customerEmail); 
                return Redirect::to(env('APP_URL').'/payment-error?d='.$message);  
            }

            if( $authorization->dataMap->ACTION_CODE != "000" ){
                $message = $authorization->dataMap->ACTION_DESCRIPTION;
                $this->createOrderError($message , $request->transactionToken , $request->customerEmail); 
                return Redirect::to(env('APP_URL').'/payment-error?d='.$message);
            }

            OrderPayments::create([
                "transactionToken"  => $request->transactionToken,
                "customerEmail"     => $request->customerEmail,
                "order_id"          => $order->id
            ]);

            $_clients = OrderGuests::where("order_id" , "=" , $order->id)->orderBy('id', 'asc')->get();

            $clients = array();

            $to_email = "";

            $extension_qr = "png";

            foreach ($_clients as $key => $client) {
                # code...
                if($to_email == "") $to_email = $client->email;
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

            Mail::to($to_email)->bcc(['reservas@dizcoclub.com'])->send(
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
            $this->createOrderError("Error codigo" , "" , "");
            return Redirect::to(env('APP_URL').'/payment-error?d=Error transacción');
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
        $admin = Auth::user()->isadmin;
        $orders = OrderGuests::with(['order', 'order.event'])->select('order_guests.*') 
                ->join('orders', 'order_guests.order_id', '=', 'orders.id') 
                ->join('order_payments', 'order_payments.order_id', '=', 'orders.id');
        if($admin == 0) $orders = $orders->where('orders.user_id', '=' , $userId);

        $orders = $orders->get();
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
                "id" => $order->hash,
                "price" => $order->order->event->price,
                "confirm" => $order->assist
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
        if($admin == 0) $orders = $orders->where('orders.user_id', '=' , $userId);
        
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
                "id" => $order->hash,
                "assist" => $order->assist,
            );
        }
        return $this->sendResponse($_tickets, '');
    }

    public function sendemailqr(Request $request)
    {
        $orderId  = $request->query('order');
        $order = Order::with('event')->find($orderId);
        $_clients = OrderGuests::where("order_id" , "=" , $orderId)->orderBy('id', 'asc')->get();
        $clients = array();
        $to_email = "";
        foreach ($_clients as $key => $client) {

            if($to_email == "") $to_email = $client->email;
            $clients[] = (object) array( 
                "qr"  => env('APP_URL') .'/'.$client->qr_path,
                "ticket"  => $client->ticket,
                "name"  => $client->name,
                "dni"  => $client->dni
            );
        }
        
        Mail::to("$to_email")->bcc(['reservas@dizcoclub.com'])->send(
            new \App\Mail\OrderMail( 
                $order->event->title, 
                Carbon::parse($order->event->date)->format('d F Y'), 
                Carbon::parse($order->event->time)->format('H:i'), 
                env('APP_URL') .'/public/'. $order->event->avatar_path, 
                $clients)
            );
        return $this->sendResponse([] , '');
    }

    public function assist(Request $request)
    {
        $userId = Auth::id();
        $admin = Auth::user()->isadmin;
        if( $admin == 0){
            return $this->sendError('No tiene permisos necesarios para hacer una peticion', ['error'=> [] ]);
        }

        $validator = Validator::make($request->all(),[
            'id' => 'required',
        ]);
        
        if($validator->fails()) {          
            return $this->sendError('Es necesario el token del ticket', ['error'=> $validator->errors() ]);
        }

        $orderGuests = OrderGuests::where('hash', '=' , $request->id)->get();
        if( count($orderGuests) == 0 )  return $this->sendError('No existe cliente', ['error'=> $validator->errors() ]);

        OrderGuests::where('hash', '=' , $request->id)->update(['assist' => true]);

        return $this->sendResponse([] , 'Se confirmo asistencia del cliente');
        
    }

    public function generateQR(Request $request)
    {
        $userId = Auth::id();
        $admin = Auth::user()->isadmin;
        if($admin == 1){
            $validator = Validator::make($request->all(),[
                'order_id' => 'required',
            ]);
    
            if($validator->fails()) {          
                return $this->sendError("order requerido");
            }

            $order = Order::with('event')->find($request->order_id);
            if($order == null) return $this->sendError("order no exsite");

            OrderPayments::create([
                "transactionToken"  => "generado por api",
                "customerEmail"     => "",
                "order_id"          => $request->order_id
            ]);

            $_clients = OrderGuests::where("order_id" , "=" , $request->order_id)->orderBy('id', 'asc')->get();

            $clients = array();

            $to_email = "";

            $extension_qr = "png";

            foreach ($_clients as $key => $client) {
                # code...
                if($to_email == "") $to_email = $client->email;
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

            Mail::to($to_email)->bcc(['reservas@dizcoclub.com'])->send(
                new \App\Mail\OrderMail( 
                    $order->event->title, 
                    Carbon::parse($order->event->date)->format('d F Y'), 
                    Carbon::parse($order->event->time)->format('H:i'), 
                    env('APP_URL') .'/public/'. $order->event->avatar_path, 
                    $clients)
                );

        }
        return $this->sendError("No tiene permisos");
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
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
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

    private function generateSesion($amount, $token, $email, $codCli, $isAuth, $diffdate, $numberDoc) {
        $session = array(
            'amount' => $amount,
            'antifraud' => array(
                'clientIp' => $_SERVER['REMOTE_ADDR'],
                'merchantDefineData' => array(
                    'MDD4' => $email,
                    'MDD21' => "0",
                    'MDD32' => $codCli,
                    'MDD75' => $isAuth ? "Registrado": "Invitado",
                    'MDD77' => $diffdate,
                    'MDD33' => "DNI",
                    'MDD34' => $numberDoc
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
