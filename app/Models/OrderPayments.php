<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'transactionToken',
        'customerEmail',
        'order_id', 
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
