<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
class OrdersEventExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function headings():array{
        return[
            'Evento',
            'Nombre cliente',
            'Apellido cliente',
            'Correo cliente',
            'Cantidad',
            'Precio Total de Evento',
            'Precio de compra',
            'Descuento',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Order::with([])->select(
            'events.title',
            DB::raw("(SELECT og.name FROM order_guests og WHERE og.order_id = orders.id LIMIT 1) AS username"),
            DB::raw("(SELECT og.lastname FROM order_guests og WHERE og.order_id = orders.id LIMIT 1) AS userlastname"),
            DB::raw("(SELECT og.email FROM order_guests og WHERE og.order_id = orders.id LIMIT 1) AS useremail"),
            'orders.quantity',
            'orders.total_price',
            'orders.total',
            DB::raw("(CASE WHEN orders.discount = 1 THEN 'SI' ELSE 'NO' END) AS discount")
            )->join('order_guests', 'order_guests.order_id', '=', 'orders.id')
            ->join('events', 'events.id', '=', 'orders.event_id')
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->where('events.id', '=' , $this->event)
            ->distinct()
            ->get();
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

                $cellRange = 'A1:H1'; // All headers

                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($stylesArray);
            },
        ];
    }
}
