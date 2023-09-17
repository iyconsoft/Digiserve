<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ServiceExport implements FromQuery, WithHeadings
{
	public $Query;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
		return DB::table(DB::RAW(' ('.$this->Query.') a'))->orderby('name');
    }
	
	public function headings(): array
    {
        return [
			"Name",
			"Provider",
			"Notification Type",
			"Notification Format",
			"Options"
        ];
    }
}
