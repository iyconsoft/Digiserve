@extends("layouts.app")
@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Payment</h1>
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
                         <label for="msisdn">Msisdn:</label>
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
                      	<label for="is_paid">Payment:</label>
                         <select id="is_paid" name="is_paid" class="form-control">
                         	<option value="">Select</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                         </select>
                      </div>
                      <div class="col-md-3">
                         <label for="payment_date">Payment date:</label>
                         <input type="text" class="form-control daterangepicker2" name="payment_date" id="payment_date"   />
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
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Payment Date</th>
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
		url: '{{url('payment/grid')}}',
		type:'GET',
		  'headers': {
			  'X-CSRF-TOKEN': '{{ csrf_token() }}'
		  },
	},
	"columns": [
		{ data: 'msisdn' },
		{ data: 'name' },
		{ data: 'local_area' },
		{ data: 'amount' },
		{ data: 'is_paid' },
		{ data: 'payment_date' },
	],
	language : {"zeroRecords": "&nbsp;"},
	dom: 'rtlip',
	order: [[5, 'desc']]
});
 
$('.searchBtn').on('click', function (e) { 
	
	var msisdn = $("#msisdn").val();
	var is_paid = $("#is_paid").val();
	var problem = $("#problem").val();
	var priorty_project = $("#priorty_project").val();
	var start_payment_date = "";
	var end_payment_date = "";
	if($('#payment_date').val() != "")
	{
		start_payment_date = $('#payment_date').data('daterangepicker').startDate.format('YYYY-MM-DD');
		end_payment_date = $('#payment_date').data('daterangepicker').endDate.format('YYYY-MM-DD');
	}
	$('#dataTable').DataTable().ajax.url( "{{url('payment/grid')}}/?searchItem=true&msisdn="+msisdn+"&problem="+problem+"&priorty_project="+priorty_project+"&start_payment_date="+start_payment_date+"&end_payment_date="+end_payment_date+"&is_paid="+is_paid).load();

});

 
$('.clearBtn').on('click', function (e) { 
	$('#dataTable').DataTable().ajax.url( "{{url('payment/grid')}}/").load();
	$('#searchForm')[0].reset();
	$('#advanceSearchForm')[0].reset();
});

$(document).on('click', '#Export', function (e) { 
	var msisdn = $("#msisdn").val();
	var is_paid = $("#is_paid").val();
	var problem = $("#problem").val();
	var priorty_project = $("#priorty_project").val();
	var start_payment_date = "";
	var end_payment_date = "";
	if($('#payment_date').val() != "")
	{
		start_payment_date = $('#payment_date').data('daterangepicker').startDate.format('YYYY-MM-DD');
		end_payment_date = $('#payment_date').data('daterangepicker').endDate.format('YYYY-MM-DD');
	}
	
	var URL = "{{url('payment/export')}}/?searchItem=true&msisdn="+msisdn+"&problem="+problem+"&priorty_project="+priorty_project+"&start_payment_date="+start_payment_date+"&end_payment_date="+end_payment_date+"&is_paid="+is_paid;
	
	downloadURI(URL);
});
 

</script>

@endpush