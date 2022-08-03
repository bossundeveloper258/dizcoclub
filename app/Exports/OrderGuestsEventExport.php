<?php

namespace App\Exports;

use App\Models\OrderGuests;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
class OrderGuestsEventExport implements FromCollection, WithHeadings
{

    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */

    public function headings():array{
        return[
            'DNI',
            'Nombre',
            'Apellidos',
            'Evento',
            'Ticket',
            'Asistencia',
        ];
    }

    public function collection()
    {
        $orders = OrderGuests::with(['order', 'order.event'])->select(
            'order_guests.dni',
            'order_guests.name',
            'order_guests.lastname',
            'events.title',
            'order_guests.ticket',
            'order_guests.assist'
            ) 
                ->join('orders', 'order_guests.order_id', '=', 'orders.id') 
                ->leftJoin('events', 'events.id', '=', 'orders.event_id')
                ->join('order_payments', 'order_payments.order_id', '=', 'orders.id')
                ->where('events.id', '=' , $this->event)
                ->get();

        return $orders;
    }
}
