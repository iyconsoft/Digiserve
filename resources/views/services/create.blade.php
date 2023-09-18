@extends("layouts.app")
@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Services</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ url('services') }}">Services</a></li>
        <li class="breadcrumb-item active">Create</li>
        </ol>
      </div>
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<div class="card">
   <form class="form-horizontal"
      action="{{ $isEdit ? url('services/update/' . $info_Service->id) : url('services/store') }}"
      id="quotationForm" method="post">
      @csrf
      <div class="card-body">
         <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                  <label for="name">Name</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="Name" required value="{{ old('name') ?? ($info_Service->name ?? '') }}">
               </div>
               <div class="form-group">
                  <label for="provider">Provider</label>
                  <input type="text" class="form-control" id="provider" name="provider" placeholder="Provider" required value="{{ old('provider') ?? ($info_Service->provider ?? '') }}">
               </div>
               <div class="form-group">
                  <label for="notification_type">Notification Type</label>
                  <select class="form-control" id="notification_type" name="notification_type" required>
                  <option {{ $isEdit ? ($info_Service->notification_type == 1 ? 'selected' : '') : '' }} value="1">Weekly</option>
                  <option {{ $isEdit ? ($info_Service->notification_type == 2 ? 'selected' : '') : '' }}  value="2">Biweekly</option>
                  <option {{ $isEdit ? ($info_Service->notification_type == 3 ? 'selected' : '') : '' }}  value="3">Montly</option>
                  </select>
               </div>
               
               <div class="form-group">
               <label class="text-left">Active</label>
               <div class="input-group afield col-sm-8 col-xs-12 ">
                  <div class="icheck-primary d-inline">
                     <input type="checkbox" name="status" id="status" {{ $isEdit ? ($info_Service->status == 1 ? 'checked' : '') : 'checked' }} />
                     <label for="status">
                     </label>
                  </div>
               </div>
              </div>
               
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label for="provider">Notification Format</label>
                  <input type="text" class="form-control" id="format" name="format" placeholder="Notification Format" required value="{{ old('format') ?? ($info_Service->format ?? '') }}">
               </div>
               <div class="form-group">
                  <label for="options">Options</label>
                  <select class="form-control select2" multiple="multiple" id="options" name="options[]" required>
                     @foreach(App\Models\Option::ALL() as $Option)
                     @if($isEdit)
                     <option {{ $info_Service->Option()->Where('option_id',$Option->id)->First() ? 'selected' : '' }} value="{{ $Option->id }}">{{ $Option->name }}</option>
                     @else
                     <option value="{{ $Option->id }}">{{ $Option->name }}</option>
                     @endif
                     @endforeach
                  </select>
               </div>
            </div>
         </div>
      </div>
      <div class="card-footer">
         <button type="submit" class="btn btn-primary">Submit</button>
      </div>
   </form>
</div>

 
@endsection

@push('scripts')

<script type="text/javascript">	
 
</script>

@endpush