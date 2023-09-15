<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class UssdExport implements FromQuery, WithHeadings
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
			"Problem",
			"Priorty project",
			"Pledged",
			"Creation Date"
        ];
    }
}
