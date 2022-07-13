<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

Route::get('events', [EventController::class, 'index']);
Route::get('events/{id}', [EventController::class, 'edit']);

// Orders
Route::post('orders/create', [OrderController::class, 'store']);
Route::post('orders/options', [OrderController::class, 'paymentOptions']);
Route::post('orders/payment', [OrderController::class, 'payment']);
Route::post('orders/success', [OrderController::class, 'paymentSuccess']);

Route::group([
    'prefix' => 'events',
    'middleware' => 'auth:api'
  ], function() {
    
    Route::post('', [EventController::class, 'store']);
    Route::get('form/{id}', [EventController::class, 'editForm']);
    Route::post('update/{id}', [EventController::class, 'update']);
});

Route::group([
    'prefix' => 'orders',
    'middleware' => 'auth:api'
  ], function() {
    
    Route::get('tickets', [OrderController::class, 'tickets']);
    Route::get('tickets/{token}', [OrderController::class, 'ticketByToken']);
    Route::post('tickets/assist', [OrderController::class, 'assist']);
});

Route::get('orders/send-mail', [OrderController::class, 'sendemailqr']);

Route::get('send-mail', function () {

    $details = [
        'title' => 'Mail from codecheef.org',
        'body' => 'This is for testing email using smtp'
    ];
   
    $mail = Mail::to('bossundeveloper258@gmail.com')->send(new \App\Mail\MyTestMail($details));
    var_dump($mail);
    return new JsonResponse(
        [
            'success' => true, 
            'message' => "Thank you for subscribing to our email, please check your inbox"
        ], 
        200
    );
});
