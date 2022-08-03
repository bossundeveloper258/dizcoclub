<?php

namespace App\Exports;

use App\Models\OrderGuests;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
class OrderGuestsEventExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
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

    public function registerEvents(): array
    {
        
        
        return [
            AfterSheet::class  => function(AfterSheet $event) {

                $stylesArray = [
                    'borders' => [
                        // 'allBorders' => [
                        //     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        //     'color' => ['argb' => 'FFFFFF'],
                        // ],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType'  => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E94985']
                    ]
                ];

                $cellRange = 'A1:F1'; // All headers

                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($stylesArray);
            },
        ];
    }
}
