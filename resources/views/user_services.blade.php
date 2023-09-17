@extends("layouts.app")
@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">User Services</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        
      </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="card">
    <div class="card-body">
    	 <div class="row">
           <div class="col-md-12 m-2" style="text-align: right;">  
           	<Button class="btn btn-default" id="Export">Export</Button>
           </div>
    	 </div>
    	 <div class="row">
             <div class="col-md-12">
                <form id="searchForm" action="#">
                   <div class="row">
                      <div class="col-md-3">
                         <label for="charge_type">Msisdn:</label>
                         <input type="text" class="form-control" name="msisdn" id="msisdn"   />
                      </div>
                      <div class="col-md-3">
                         <label for="charge_type">Create date:</label>
                         <input type="text" class="form-control daterangepicker2" name="create_date" id="create_date"   />
                      </div>
                      <div class="col-md-3"><label for="charge_type">&nbsp;</label><br />
                         <button type="button" id="searchBtn" class="btn btn-success mr-2 searchBtn" style="margin-right:2rem">Search</button>
                         <button type="button" id="resetBtn" class="btn btn-danger mr-2 clearBtn" style="margin-right:2rem">Reset</button>
                      </div>
                   </div>
                </form>
                <input type="hidden" id="searchType" value="1" />
                <form id="advanceSearchForm" action="#" style="display:none">
                    
                </form>
             </div>
          </div>
          <div class="table-responsive">
             <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                   <tr>
                    <th>Msisdn</th>
                    <th>Name</th>
                    <th>Service</th>
                    <th>Service Option</th>
                    <th>Meter No</th>
                    <th>Account No</th>
                    <th>Bank</th>
                    <th>Payment Reference</th>
                    <th>Amount</th>
                    <th>Notification Type</th>
                    <th>Last Notification</th>
                    <th>Next Notification</th>
                    <th>Notificaton Message</th>
                    <th>Created Date</th>
                   </tr>
                </thead>
                <tbody>
                </tbody>
             </table>
          </div>
       </div>
    </div>
</div>


 
@endsection

 
@push('scripts')

<script type="text/javascript">	
var table = $('#dataTable').DataTable({
	"scrollX": true,
	"paging": true,
	"processing": true,
	"serverSide": true,
	"ajax": {
		url: '{{url('user_services/grid')}}',
		type:'GET',
		  'headers': {
			  'X-CSRF-TOKEN': '{{ csrf_token() }}'
		  },
	},
	"columns": [
		{ data: 'msisdn' },
		{ data: 'name' },
		{ data: 'service' },
		{ data: 'service_option' },
		{ data: 'meter_no' },
		{ data: 'account_no' },
		{ data: 'bank' },
		{ data: 'payment_reference' },
		{ data: 'amount' },
		{ data: 'notification_type' },
		{ data: 'last_notification' },
		{ data: 'next_notification' },
		{ data: 'notificaton_message' },
		{ data: 'created_at' },
	],
	language : {"zeroRecords": "&nbsp;"},
	dom: 'rtlip',
	order: [[6, 'desc']]
});
 
$('.searchBtn').on('click', function (e) { 
	
	var msisdn = $("#msisdn").val();
	var start_create_date = "";
	var end_create_date = "";
	if($('#create_date').val() != "")
	{
		start_create_date = $('#create_date').data('daterangepicker').startDate.format('YYYY-MM-DD');
		end_create_date = $('#create_date').data('daterangepicker').endDate.format('YYYY-MM-DD');
	}
	$('#dataTable').DataTable().ajax.url( "{{url('user_services/grid')}}/?searchItem=true&msisdn="+msisdn+"&start_create_date="+start_create_date+"&end_create_date="+end_create_date).load();

});

 
$('.clearBtn').on('click', function (e) { 
	$('#dataTable').DataTable().ajax.url( "{{url('user_services/grid')}}/").load();
	$('#searchForm')[0].reset();
	$('#advanceSearchForm')[0].reset();
});

$(document).on('click', '#Export', function (e) { 
	var msisdn = $("#msisdn").val();
	var start_create_date = "";
	var end_create_date = "";
	if($('#create_date').val() != "")
	{
		start_create_date = $('#create_date').data('daterangepicker').startDate.format('YYYY-MM-DD');
		end_create_date = $('#create_date').data('daterangepicker').endDate.format('YYYY-MM-DD');
	}
	
	var URL = "{{url('user_services/export')}}/?searchItem=true&msisdn="+msisdn+"&start_create_date="+start_create_date+"&end_create_date="+end_create_date;
	
	downloadURI(URL, "User Services");
});
 

</script>

@endpush