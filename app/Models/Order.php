<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'ticket_order',
        'event_id',
        'user_id', 
        'quantity',
        'total', 
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'user_id'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
