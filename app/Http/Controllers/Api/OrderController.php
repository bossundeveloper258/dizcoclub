<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderGuests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

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
            'clients.*.lastname' => 'required|string',
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

                if( $q_total_disponible > $request->quantity ) return $this->sendError('Supera la cantidad permitira de promocion', ['error'=> []] , 400);                
                
            }
            
            $to_email = "";
            foreach ($request->clients as $key => $g) {
                if($key == 0) {
                    if( $g['email'] == "") return $this->sendError('El primer cliente debe ingresar su correo electronico', ['error'=> []] , 400);
                    $to_email = $g['email'];
                }
            }

            $userId = Auth::id();
            
            $order_new = Order::create([
                'event_id'      => $event->id,
                'user_id'       => $userId ? $userId : null,
                'quantity'      => $request->quantity,
                'total'         => $request->quantity * $event->price
            ]);

            $clients = array();

            foreach ($request->clients as $key => $g) {
                
                $_token = Str::random(25)."-".$this->base64url_encode($g['dni']);
                
                $order_g = OrderGuests::create([
                    'order_id'  => $order_new->id,
                    'name'      => $g['name'], 
                    'lastname'  => $g['lastname'],
                    'email'     => isset($g['email']) ? $g['email'] : "",
                    'dni'       => $g['dni'], 
                    'hash'      => $_token
                ]);
                
                
                $html = QrCode::size(300)->generate(env('APP_URL').'/validate-token'.'/'.$_token.'', public_path('/qrcodes/') .$_token.'.svg');
                
                
                $clients[] = array(
                    "name"      => $g['name'],
                    "qr"        => env('APP_URL') .'/'.'qrcodes/'.$_token.'.svg',
                    "dni"       => $g['dni'],
                    "ticked"    => str_pad( $order_g->id, 8, "0", STR_PAD_LEFT),
                    
                );
            }

            $data = array(
                'email'     => $to_email,
                "title"     => $event->title,
                "date"      => $event->date,
                "hour"      => $event->time,
                "image"     => env('APP_URL') .'/public/'. $event->avatar_path,
                'clients' => $clients
            );

            // \Mail::to($to_email)->send(new \App\Mail\OrderMail( $event->title, $event->date, $event->time, env('APP_URL') .'/public/'. $event->avatar_path, $clients));


            return $this->sendResponse([], 'Orden creado correctamente');

        } catch (\Throwable $th) {
            return $this->sendError('Error del servidor', ['error'=> $th] , 404);
        }
    }

    public function paymentOptions(Request $request){
        $validator = Validator::make($request->all(),[
            'total' => 'required',
        ]);

        
        if($validator->fails()) {          
            return $this->sendError('Error Validacion', ['error'=> $validator->errors() ]);
        }

        $token = $this->generateToken();

        $response = array(
            "session" => $this->generateSesion($request->total, $token),
            "purchaseNumber" => "3412312229",
            "merchantid" => config('visa.VISA_MERCHANT_ID')
        );

        return $this->sendResponse($response, 'Session Nuibiz');
    }

    public function payment(Request $request){
        $validator = Validator::make($request->all(),[
            'transactionToken' => 'required',
            'customerEmail'=> 'required',
        ]);
        $amount  = $request->query('amount');
        $purchaseNumber  = $request->query('purchaseNumber');
        
        if($validator->fails()) {          
            return $this->sendError('Error Validacion', ['error'=> $validator->errors() ]);
        }
        return $this->sendResponse(array('transactionToken' => $request->transactionToken,
        'customerEmail'=> $request->customerEmail,), 'Pago completado');
    }

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

    private function generatePurchaseNumber(){
        $archivo = "assets/purchaseNumber.txt"; 
        $purchaseNumber = 222;
        $fp = fopen($archivo,"r"); 
        $purchaseNumber = fgets($fp, 100);
        fclose($fp); 
        ++$purchaseNumber; 
        $fp = fopen($archivo,"w+"); 
        fwrite($fp, $purchaseNumber, 100); 
        fclose($fp);
        return $purchaseNumber;
    }
}
