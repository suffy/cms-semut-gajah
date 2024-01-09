@extends('admin.layout.template')

@section('content')



<div role="dialog" aria-labelledby="option" aria-hidden="true">
  <div class="" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title">Upload Customer Excel</h5>
      </div>
      <div class="modal-body">
            <form action="@if(auth()->user()->account_role == 'manager'){{url('manager/customers/upload-excel')}}@else{{url('superadmin/customers/upload-excel')}}@endif" method="post" enctype="multipart/form-data">
				@csrf
				<div class="form-group">
					<label class="col-sm-12 col-form-label">File</label>
					<div class="col-sm-12">
                        <input type="hidden" name="url"  value="{{Request::url()}}" required>
						<input type="file" class="form-control" name="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
						<span>* format csv, xlsx, xls, dsb (excel format)</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-12 col-form-label"></label>
					<div class="col-sm-12">
						<button type="submit" class="btn btn-blue">Upload</button>
						<hr>
						<span>* Format upload harus sesuai dengan template berikut :</span><br>
						<hr>
						<a href="{{asset('customer_master.xlsx')}}"><span class="fa fa-download"></span> Download Contoh Excel</a>
						<hr>
						
						<img src="{{asset('example-import-customer.png')}}" style="max-width: 650px" class="img-fluid">
						<hr>
					</div>
				</div>
			</form>
      </div>
    </div>
  </div>
</div>

<br><br>
{{-- <div role="dialog" aria-labelledby="option" aria-hidden="true">
  <div class="" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title">Logs</h5>
      </div>
      <div class="modal-body">
          <div class="scroll-outer">
              <div class="scroll-inner">
                @if(!empty(Session::get('status')) && Session::get('status') == 1)
					@if(!empty(Session::get('data')))
						@foreach(Session::get('data') as $row)
							<li>{{$row[0]}} - {{$row[1]}} - {{$row[2]}} - {{$row[3]}} - {{$row[4]}}</li>
						@endforeach
					@endif
                @endif
              </div>
          </div>
      </div>
    </div>
  </div>
</div> --}}

    <style>
        .modal-outer{
            overflow: auto;
            max-height: 650px;
        }

        .modal-inner{
            height: 100%;
        }
	</style>
    
    

@stop