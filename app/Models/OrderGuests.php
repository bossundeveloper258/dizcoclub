<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGuests extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'ticket',
        'order_id',
        'name', 
        'lastname',
        'email',
        'dni', 
        'hash'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'id'
    ];
}
