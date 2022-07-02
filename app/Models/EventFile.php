<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 
        'file_id',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'user_id'
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
