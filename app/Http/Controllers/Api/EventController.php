<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventFile;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventController extends BaseController
{
    //
    public function __construct()
    {
        // $this->middleware('admin');
    }

    public function index()
    {
        
        $events = Event::with('files')->get();
        return $this->sendResponse($events, 'List');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'title' => 'required|string',
            'date' => 'required',
            'time' => 'required',
            'address' => 'required|string',
            'description' => 'required|string',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'file' => 'required|max:2048'
        ]); 

        $folder = 'files';

        if($validator->fails()) {          
            return $this->sendError('Error Validacion', ['error'=> $validator->errors() ]);
        }

        try {
            
            $userId = Auth::id();
            $id_images = [];
            if ($file = $request->file('file')) {
                $extension = $file->getClientOriginalExtension(); 
                $name_file = Str::random(50) . '.' . $extension;
                // $name_file = $file->getClientOriginalName();
                $file->storeAs('public/' . $folder, $name_file);
                $path = $folder. '/' . $name_file;
                
                $event = Event::create([
                    'title'             => $request->title,
                    'date'              => $request->date,
                    'time'              => $request->time,
                    'address'           => $request->address,
                    'avatar_name'       => $name_file,
                    'avatar_path'       => $path,
                    'stock'             => $request->stock,
                    'price'             => $request->price,
                    'description'       => $request->description,
                    'isdiscount'        => $request->isdiscount ?? false,
                    'discount'          => $request->discount ,
                    'discount_stock'    => $request->discount_stock,
                    'user_id'           => $userId
                ]);

                
                $fileT = File::create([
                    'path' => $path,
                    'type' => 1,
                    'user_id' => $userId
                ]);

                $id_images[] = $fileT->id;

                if( $files = $request->file('files') ){
                    foreach ($files as $key => $_file) {
                        // $_name = $_file->getClientOriginalName();
                        $_extension = $_file->getClientOriginalExtension(); 
                        $_name = Str::random(50) . '.' . $_extension;
                        $_file->storeAs('public/'. $folder , $_name);
                        $_path = $folder. '/' . $_name;
    
                        $fileM = File::create([
                            'path' => $_path,
                            'type' => 2,
                            'user_id' => $userId
                        ]);
    
                        $id_images[] = $fileM->id;
                        # code...
                    }
                }

                $event->files()->attach($id_images);
                //store your file into directory and db
    
                
      
            }

            return $this->sendResponse([], 'Evento creado correctamente');
        } catch (\Throwable $th) {
            return $this->sendError('Error del servidor', ['error'=> $th] , 400);
        } 

         
    }
}
