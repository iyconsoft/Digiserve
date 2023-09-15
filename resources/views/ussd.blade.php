@extends("layouts.app")
@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">USSD</h1>
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
                         <label for="charge_type">Problem:</label>
                         <input type="text" class="form-control" name="problem" id="problem"   />
                      </div>
                      <div class="col-md-3">
                         <label for="charge_type">Project:</label>
                         <input type="text" class="form-control" name="priorty_project" id="priorty_project"   />
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
                    <th>Local govt</th>
                    <th>Problem</th>
                    <th>Priorty project</th>
                    <th>Pledged</th>
                    <th>Creation Date</th>
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
		url: '{{url('ussd/grid')}}',
		type:'GET',
		  'headers': {
			  'X-CSRF-TOKEN': '{{ csrf_token() }}'
		  },
	},
	"columns": [
		{ data: 'msisdn' },
		{ data: 'name' },
		{ data: 'local_area' },
		{ data: 'problem' },
		{ data: 'priorty_project' },
		{ data: 'amount' },
		{ data: 'created_at' },
	],
	language : {"zeroRecords": "&nbsp;"},
	dom: 'rtlip',
	order: [[6, 'desc']]
});
 
$('.searchBtn').on('click', function (e) { 
	
	var msisdn = $("#msisdn").val();
	var problem = $("#problem").val();
	var priorty_project = $("#priorty_project").val();
	var start_create_date = "";
	var end_create_date = "";
	if($('#create_date').val() != "")
	{
		start_create_date = $('#create_date').data('daterangepicker').startDate.format('YYYY-MM-DD');
		end_create_date = $('#create_date').data('daterangepicker').endDate.format('YYYY-MM-DD');
	}
	$('#dataTable').DataTable().ajax.url( "{{url('ussd/grid')}}/?searchItem=true&msisdn="+msisdn+"&problem="+problem+"&priorty_project="+priorty_project+"&start_create_date="+start_create_date+"&end_create_date="+end_create_date).load();

});

 
$('.clearBtn').on('click', function (e) { 
	$('#dataTable').DataTable().ajax.url( "{{url('ussd/grid')}}/").load();
	$('#searchForm')[0].reset();
	$('#advanceSearchForm')[0].reset();
});

$(document).on('click', '#Export', function (e) { 
	var msisdn = $("#msisdn").val();
	var problem = $("#problem").val();
	var priorty_project = $("#priorty_project").val();
	var start_create_date = "";
	var end_create_date = "";
	if($('#create_date').val() != "")
	{
		start_create_date = $('#create_date').data('daterangepicker').startDate.format('YYYY-MM-DD');
		end_create_date = $('#create_date').data('daterangepicker').endDate.format('YYYY-MM-DD');
	}
	
	var URL = "{{url('ussd/export')}}/??searchItem=true&msisdn="+msisdn+"&problem="+problem+"&priorty_project="+priorty_project+"&start_create_date="+start_create_date+"&end_create_date="+end_create_date;
	
	downloadURI(URL);
});
 

</script>

@endpush