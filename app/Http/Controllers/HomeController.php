<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UssdUser;
use DB;
use App\Exports\UserServiceExport;
use App\Exports\PaymentExport;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function userServices()
    {
        return view('user_services');
    }
	public function userServicesDownload(Request $request)
	{
		if(isset($request->searchItem) || 1==1)
		{
			$Where = " 1=1 ";
			if(isset($request->msisdn) && $request->msisdn != "")
			{
				$Where .= ' and user_services.msisdn like "%'.$request->msisdn.'%"';
			}
			if(isset($request->start_create_date) && $request->start_create_date != "" && isset($request->end_create_date) && $request->end_create_date != "")
			{
				$Where .= ' and DATE(user_services.created_at) between  "'.$request->start_create_date.'" and "'.$request->end_create_date.'"';
			}
			
			$Query = "SELECT msisdn, name, service, service_option, meter_no, account_no, bank, payment_reference, amount, CASE 
	when notification_type=1 THEN 'Weekly' 
	when notification_type=2 THEN 'Biweekly' 
	when notification_type=3 THEN 'Montly' 
END as 'notification_type', last_notification, next_notification, notificaton_message, created_at
FROM user_services
			where ".$Where;

			$userServiceExport = new UserServiceExport;
			$userServiceExport->Query = $Query;
			return Excel::download($userServiceExport, 'User Services.xlsx');

		}
		else
		{
			return Excel::download([], 'User Services.xlsx');
		}
	}
	public function userServicesGrid(Request $request)
    {
		//print_r($request->All());
		$draw = $request->get('draw');
		$start = $request->get("start");
		$rowperpage = $request->get("length"); // Rows display per page

		$columnIndex_arr = $request->get('order');
		$columnName_arr = $request->get('columns');
		$order_arr = $request->get('order');
		$search_arr = $request->get('search');

		$columnIndex = $columnIndex_arr[0]['column']; // Column index
		$columnName = $columnName_arr[$columnIndex]['data']; // Column name
		$columnSortOrder = $order_arr[0]['dir']; // asc or desc
		$searchValue = $search_arr['value']; // Search value
		
		if(isset($request->searchItem) || 1==1)
		{
			$Where = " 1=1 ";
			if(isset($request->msisdn) && $request->msisdn != "")
			{
				$Where .= ' and user_services.msisdn like "%'.$request->msisdn.'%"';
			}
			if(isset($request->start_create_date) && $request->start_create_date != "" && isset($request->end_create_date) && $request->end_create_date != "")
			{
				$Where .= ' and DATE(user_services.created_at) between  "'.$request->start_create_date.'" and "'.$request->end_create_date.'"';
			}
			
			$Query = "SELECT msisdn, name, service, service_option, meter_no, account_no, bank, payment_reference, amount, CASE 
	when notification_type=1 THEN 'Weekly' 
	when notification_type=2 THEN 'Biweekly' 
	when notification_type=3 THEN 'Montly' 
END as 'notification_type', last_notification, next_notification, notificaton_message, created_at
FROM user_services
			where ".$Where;
			
			$info_Datas = DB::Select("SELECT Count(*) as cnt from (".$Query.") a");
			
			$totalRecords = $totalRecordswithFilter = $info_Datas[0]->cnt;
			
			$info_Datas = DB::Select($Query."
			order by $columnName $columnSortOrder
			limit ".$start.", ".$rowperpage);

		}
		else
		{
			$total_amount = 0;
			$totalRecords = $totalRecordswithFilter = 0;
			$info_Datas =  [];
		}
		
		foreach($info_Datas as $info_Data)
		{
			$info_Data->created_at = date('Y-m-d',strtotime($info_Data->created_at));
		}
		 
		return $response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordswithFilter,
			"aaData" => $info_Datas,			
		);
    }
	 
}
