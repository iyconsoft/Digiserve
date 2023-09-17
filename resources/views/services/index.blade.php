@extends("layouts.app")
@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Services</h1>
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
           	<a href="{{ url('services/create') }}" class="btn btn-default" id="Export">New</a>
            <Button class="btn btn-default" id="Export">Export</Button>
           </div>
    	 </div>
    	 <div class="row">
             <div class="col-md-12">
                <form id="searchForm" action="#">
                   <div class="row">
                      <div class="col-md-3">
                         <label for="charge_type">Name:</label>
                         <input type="text" class="form-control" name="name" id="name"   />
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
                    <th>Name</th>
                    <th>Provider</th>
                    <th>Notification Type</th>
                    <th>Notification Format</th>
                    <th>Options</th>
                    <th>Action</th>
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
		url: '{{url('services/grid')}}',
		type:'GET',
		  'headers': {
			  'X-CSRF-TOKEN': '{{ csrf_token() }}'
		  },
	},
	"columns": [
		{ data: 'name' },
		{ data: 'provider' },
		{ data: 'notification_type' },
		{ data: 'format' },
		{ data: 'options' },
		{ data: 'action' },
	],
	language : {"zeroRecords": "&nbsp;"},
	dom: 'rtlip',
	order: [[0, 'desc']]
});
 
$('.searchBtn').on('click', function (e) { 
	
	var name = $("#name").val();
	$('#dataTable').DataTable().ajax.url( "{{url('services/grid')}}/?searchItem=true&name="+name).load();

});

 
$('.clearBtn').on('click', function (e) { 
	$('#dataTable').DataTable().ajax.url( "{{url('services/grid')}}/").load();
	$('#searchForm')[0].reset();
	$('#advanceSearchForm')[0].reset();
});

$(document).on('click', '#Export', function (e) { 
	var name = $("#name").val();
	var URL = "{{url('services/export')}}/??searchItem=true&name="+name;
	
	downloadURI(URL, "Services");
});
 

</script>

@endpush