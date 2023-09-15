<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class PaymentExport implements FromQuery, WithHeadings
{
	public $Query;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
		return DB::table(DB::RAW(' ('.$this->Query.') a'))->orderby('msisdn');
    }
	
	public function headings(): array
    {
        return [
			"Msisdn",
			"Name",
			"Local govt",
			"Amount",
			"Payment",
			"Payment Date"
        ];
    }
}
