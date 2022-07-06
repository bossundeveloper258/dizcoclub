<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderErrors extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'order_id',
        'transactionToken',
        'customerEmail', 
        'total', 
        'message'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
