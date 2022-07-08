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
        'hash',
        'qr_path',
        'assist'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
