<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DataTables;
use DB;
use Auth;
use Session;
use Excel;
use App\Models\Service;
use App\Exports\ServiceExport;

class ServiceController extends Controller
{
 
	public function index()
    {		
        return view('services.index');
    }
	public function Download(Request $request)
	{
		if(isset($request->searchItem) || 1==1)
		{
			$Where = " 1=1 ";
			if(isset($request->name) && $request->name != "")
			{
				$Where .= ' and services.name like "%'.$request->name.'%"';
			}
			
			$Query = "SELECT name, provider, 
CASE 
	when notification_type=1 THEN 'Weekly' 
	when notification_type=2 THEN 'Biweekly' 
	when notification_type=3 THEN 'Montly' 
END as 'notification_type', 
CASE 
	when status=1 THEN 'Active' 
	when status=0 THEN 'Inactive' 
END as 'status',
format,
(select GROUP_CONCAT(name) from options left join service_options on (service_options.option_id=options.id) where service_options.service_id=services.id) as options
FROM services
			where ".$Where;

			$serviceExport = new ServiceExport;
			$serviceExport->Query = $Query;
			return Excel::download($serviceExport, 'Services.xlsx');

		}
		else
		{
			return Excel::download([], 'Services.xlsx');
		}
		
	}
	public function Grid(Request $request)
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
			$Where = ' 1=1 ';
			if(isset($request->name) && $request->name != "")
			{
				$Where .= ' and services.name like "%'.$request->name.'%"';
			}
			
			$Query = "SELECT id, name, provider, 
CASE 
	when notification_type=1 THEN 'Weekly' 
	when notification_type=2 THEN 'Biweekly' 
	when notification_type=3 THEN 'Montly' 
END as 'notification_type', 
CASE 
	when status=1 THEN 'Active' 
	when status=0 THEN 'Inactive' 
END as 'status',
format,
(select GROUP_CONCAT(name) from options left join service_options on (service_options.option_id=options.id) where service_options.service_id=services.id) as options
FROM services
			where ".$Where;
			
			$info_Datas = DB::Select("SELECT Count(*) as cnt from (".$Query.") a");
			
			$totalRecords = $totalRecordswithFilter = $info_Datas[0]->cnt;
			
			$info_Datas = DB::Select($Query."
			order by $columnName $columnSortOrder
			limit ".$start.", ".$rowperpage);
		}
		else
		{
			$totalRecords = $totalRecordswithFilter = 0;
			$info_Datas =  [];
		}
		foreach($info_Datas as $info_Data)
		{
			$info_Data->name = '<a href="'.url('/services/edit/').'/'.$info_Data->id.'">'.$info_Data->name.'</a>';			
			$info_Data->action = '<a class="btn btn-danger" href="javascript:void(0)" title="Delete Data" id="btnDelete" name="btnDelete" data-remote="services/delete/' . $info_Data->id . '"><i class="fa fa-trash"></i></a>';
			
		}
		return $response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordswithFilter,
			"aaData" => $info_Datas
		);
    }
	
	public function create(Request $request)
    {
        return view('services.create', array(
			'isEdit' => '0'
		));
    }
	
	public function store(Request $request)
    {
        $request->validate([
			  'name' => 'required|unique:services,name',
		]);
		$db_Service = new Service;
		$db_Service->name = $request->name;
		$db_Service->provider = $request->provider;
		$db_Service->notification_type = $request->notification_type;
		$db_Service->format = $request->format;
		$db_Service->status = (isset($request->status) ? '1' : '0');
		$db_Service->save();
		
		$db_Service->Option()->Sync($request->options);
		
		return Redirect('/services/');
    }
	
	public function edit(Service $service, Request $request)
    {
        return view('services.create', array(
			'isEdit' => '1',
			'info_Service' => $service
		));
    }
	
	public function update(Service $service, Request $request)
    {
        $request->validate([
			  'name' => 'required|unique:services,name,'.$service->id,
		]);
		$service->name = $request->name;
		$service->provider = $request->provider;
		$service->notification_type = $request->notification_type;
		$service->format = $request->format;
		$service->status = (isset($request->status) ? '1' : '0');
		$service->save();
		
		$service->Option()->Sync($request->options);
		
		return Redirect('/services/');
    }
	
	public function delete(Service $service)
    {
        $service->Option()->Sync([]);
		$service->Delete();
		return 'Ok';
    }
}
