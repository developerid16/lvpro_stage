<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class SalesCSVError implements FromCollection
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //
        return collect($this->data);
    }
    public function headings(): array
    {
        return [
            "Day of Date",
            "System Time",
            "Loyalty",
            "Location",
            "Storage Location",
            "POS No",
            "Original Recpt No",
            "Recpt Ref No",
            "Voucher No",
            "SKU_Code",
            "Net Sales Anount(DOC)",
            "Quantity Purchased",
            "Error",

        ];
    }
}
