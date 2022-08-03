<?php

namespace App\Exports;

use App\Models\OrderGuests;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrdersEventExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return OrderGuests::all();
    }
}
