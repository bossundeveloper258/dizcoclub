<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'date',
        'time', 
        'address',
        'description', 
        'avatar_path',
        'stock', 
        'price',
        'isdiscount', 
        'discount',
        'discount_stock',
        'avatar_name',
        'user_id'
    ];


    protected $hidden = [
        'created_at', 'updated_at', 'user_id'
    ];

    public function files()
    {
        return $this->belongsToMany(File::class, 'event_files');
    }

}
