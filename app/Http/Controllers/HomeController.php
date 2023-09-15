<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UssdUser;
use DB;
use App\Exports\UssdExport;
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
    public function ussd()
    {
        return view('ussd');
    }
	public function ussdDownload(Request $request)
	{
		if(isset($request->searchItem) || 1==1)
		{
			$Where = " 1=1 ";
			if(isset($request->msisdn) && $request->msisdn != "")
			{
				$Where .= ' and msisdn = "'.$request->msisdn.'"';
			}
			if(isset($request->problem) && $request->problem != "")
			{
				$Where .= ' and problem = "'.$request->problem.'"';
			}
			if(isset($request->priorty_project) && $request->priorty_project != "")
			{
				$Where .= ' and priorty_project = "'.$request->priorty_project.'"';
			}
			if(isset($request->start_create_date) && $request->start_create_date != "" && isset($request->end_create_date) && $request->end_create_date != "")
			{
				$Where .= ' and DATE(created_at) between  "'.$request->start_create_date.'" and "'.$request->end_create_date.'"';
			}
			
			$Query = "SELECT msisdn, name, local_area, problem, priorty_project, amount, created_at FROM `ussd_users`
where ".$Where;

		}
		else
		{
			$total_amount = 0;
			$totalRecords = $totalRecordswithFilter = 0;
			$info_Datas =  [];
		}
		
		$exp_UssdExport = new UssdExport;
		$exp_UssdExport->Query = $Query;
		return Excel::download($exp_UssdExport, 'USSD.xlsx');
	}
	public function ussdGrid(Request $request)
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
				$Where .= ' and msisdn = "'.$request->msisdn.'"';
			}
			if(isset($request->start_create_date) && $request->start_create_date != "" && isset($request->end_create_date) && $request->end_create_date != "")
			{
				$Where .= ' and DATE(created_at) between  "'.$request->start_create_date.'" and "'.$request->end_create_date.'"';
			}
			if(isset($request->problem) && $request->problem != "")
			{
				$Where .= ' and problem = "'.$request->problem.'"';
			}
			if(isset($request->priorty_project) && $request->priorty_project != "")
			{
				$Where .= ' and priorty_project = "'.$request->priorty_project.'"';
			}
			$info_Datas = DB::Select("select count(*) as cnt from (SELECT * FROM `ussd_users`
where ".$Where.") data");
			
			$totalRecords = $totalRecordswithFilter = isset($info_Datas[0]->cnt) ? $info_Datas[0]->cnt : '0';
			
			$info_Datas = DB::Select("SELECT * FROM `ussd_users`
where ".$Where."
order by $columnName $columnSortOrder
limit ".$start.", ".$rowperpage);
			//$columnName, $columnSortOrder

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
	
	public function payment()
    {
        return view('payment');
    }
	public function paymentDownload(Request $request)
	{
		if(isset($request->searchItem) || 1==1)
		{
			$Where = " 1=1 ";
			if(isset($request->msisdn) && $request->msisdn != "")
			{
				$Where .= ' and msisdn = "'.$request->msisdn.'"';
			}
			if(isset($request->is_paid) && $request->is_paid != "")
			{
				$Where .= ' and is_paid = "'.$request->is_paid.'"';
			}
			if(isset($request->problem) && $request->problem != "")
			{
				$Where .= ' and problem = "'.$request->problem.'"';
			}
			if(isset($request->priorty_project) && $request->priorty_project != "")
			{
				$Where .= ' and priorty_project = "'.$request->priorty_project.'"';
			}
			if(isset($request->start_payment_date) && $request->start_payment_date != "" && isset($request->end_payment_date) && $request->end_payment_date != "")
			{
				$Where .= ' and DATE(payment_date) between  "'.$request->start_payment_date.'" and "'.$request->end_payment_date.'"';
			}
			
			$Query = "SELECT msisdn, name, local_area, amount, CASE WHEN is_paid='1' THEN 'Yes' ELSE 'No' END as is_paid, created_at FROM `ussd_users`
where ".$Where;

		}
		else
		{
			$total_amount = 0;
			$totalRecords = $totalRecordswithFilter = 0;
			$info_Datas =  [];
		}
		
		$exp_PaymentExport = new PaymentExport;
		$exp_PaymentExport->Query = $Query;
		return Excel::download($exp_PaymentExport, 'Payment.xlsx');
	}
	public function paymentGrid(Request $request)
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
				$Where .= ' and msisdn = "'.$request->msisdn.'"';
			}
			if(isset($request->is_paid) && $request->is_paid != "")
			{
				$Where .= ' and is_paid = "'.$request->is_paid.'"';
			}
			if(isset($request->problem) && $request->problem != "")
			{
				$Where .= ' and problem = "'.$request->problem.'"';
			}
			if(isset($request->priorty_project) && $request->priorty_project != "")
			{
				$Where .= ' and priorty_project = "'.$request->priorty_project.'"';
			}
			if(isset($request->start_payment_date) && $request->start_payment_date != "" && isset($request->end_payment_date) && $request->end_payment_date != "")
			{
				$Where .= ' and DATE(payment_date) between  "'.$request->start_payment_date.'" and "'.$request->end_payment_date.'"';
			}
			
			$info_Datas = DB::Select("select count(*) as cnt from (SELECT * FROM `ussd_users`
where ".$Where.") data");
			
			$totalRecords = $totalRecordswithFilter = isset($info_Datas[0]->cnt) ? $info_Datas[0]->cnt : '0';
			
			$info_Datas = DB::Select("SELECT * FROM `ussd_users`
where ".$Where."
order by $columnName $columnSortOrder
limit ".$start.", ".$rowperpage);
			//$columnName, $columnSortOrder

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
			$info_Data->is_paid = $info_Data->is_paid == '1' ? 'Yes' : 'No';
		}
		 
		return $response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordswithFilter,
			"aaData" => $info_Datas,			
		);
    }
}
