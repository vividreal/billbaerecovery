@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection


{{-- vendor styles --}}
@section('vendor-style')
  <link rel="stylesheet" type="text/css" href="{{asset('admin/vendors/flag-icon/css/flag-icon.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('admin/vendors/data-tables/css/jquery.dataTables.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('admin/vendors/data-tables/extensions/responsive/css/responsive.dataTables.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('admin/vendors/data-tables/css/select.dataTables.min.css')}}">
@endsection

{{-- page style --}}
@section('page-style')
  <link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/data-tables.css')}}">
@endsection

@section('content')

@section('breadcrumb')
  <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? ''}}</span></h5>
  <ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? ''}}</a></li>
    <li class="breadcrumb-item active">List</li>
  </ol>
@endsection

@section('page-action')
  <a href="javascript:" onclick="managePaymentType(null)" class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">Add<i class="material-icons right">payment</i></a>
@endsection


<div class="section section-data-tables">
  <div class="card">
    <div class="card-content">
      <p class="caption mb-0">{{ Str::plural($page->title) ?? ''}}. Lorem ipsume is used for the ...</p>
    </div>
  </div>
    <!-- DataTables example -->
    <div class="row">
      <div class="col s12 m12 l12">
          <div id="button-trigger" class="card card card-default scrollspy">
            <div class="card-content">
                <h4 class="card-title">{{ Str::plural($page->title) ?? ''}} Table</h4>
                <div class="row">
                  <div class="col s12">
                      <table id="page-length-option" class="display">
                        <thead>
                            <tr>
                              <th>No</th>
                              <th>Name</th>
                              <th>Action</th>
                            </tr>
                        </thead>
                      </table>
                  </div>
                </div>
            </div>
          </div>
      </div>
    </div>

</div>
@include('payment-type.manage')
@endsection

{{-- vendor scripts --}}
@section('vendor-script')
<script src="{{asset('admin/vendors/data-tables/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('admin/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('admin/vendors/data-tables/js/dataTables.select.min.js')}}"></script>
@endsection

@push('page-scripts')

<script>

  var table;
  var role        = '{{ROUTE_PREFIX}}';
  var link        = '{{$page->link}}';
  var entity      = '{{$page->entity}}';

  $(function () {
    table = $('#page-length-option').DataTable({
        bSearchable: true,
        pagination: true,
        pageLength: 10,
        responsive: true,
        searchDelay: 500,
        processing: true,
        serverSide: true,
        ajax: "{{ url(ROUTE_PREFIX.'/'.$page->route.'/lists') }}",
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
  });
  
  function managePaymentType(paymentType_id){
    validator.resetForm();
    $('input').removeClass('error');

    if (paymentType_id === null) {
        $("#{{$page->entity}}Form")[0].reset();
        $('#{{$page->entity}}Form').find("input[type=text]").val("");
        $("#paymentType_id").val('');
        $('#paymentType-types-modal').modal('open');
    } else {
        $.ajax({url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}/" + paymentType_id + "/edit", type: "GET", dataType: "html"})
            .done(function (a) {
                var data = JSON.parse(a);
                if(data.flagError == false){
                    $("#paymentType_id").val(data.data.id);
                    $("#{{$page->entity}}Form input[name=name]").val(data.data.name);
                      $("#paymentTypeFields .label-placeholder").hide();
                      $("#paymentTypeFields .label-placeholder").hide();
                    $("#paymentType-types-modal").modal("open");

                }
            }).fail(function () {
                printErrorMsg("Please try again...", "error");
        });
    }
  }

  if ($("#{{$page->entity}}Form").length > 0) {
    var validator = $("#{{$page->entity}}Form").validate({ 
        rules: {
            name: {
                  required: true,
                  maxlength: 100,
            },
        },
        messages: { 
          name: {
            required: "Please enter payment type",
            maxlength: "Length cannot be more than 100 characters",
          },
        },
        submitHandler: function (form) {
          $('#submit-btn').html('Please Wait...');
          $("#submit-btn"). attr("disabled", true);
          id = $("#paymentType_id").val();
          paymentType_id   = "" == id ? "" : "/" + id;
          formMethod  = "" == id ? "POST" : "PUT";
          var forms = $("#{{$page->entity}}Form");
          $.ajax({ url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}" + paymentType_id, type: formMethod, processData: false, 
          data: forms.serialize(), dataType: "html",
          }).done(function (a) {
            $('#submit-btn').html('Submit <i class="material-icons right">send</i>');
            $("#submit-btn"). attr("disabled", false);
            var data = JSON.parse(a);
            if(data.flagError == false){
                showSuccessToaster(data.message);                
                $("#paymentType-types-modal").modal("close");
                setTimeout(function () {
                  table.ajax.reload();
                }, 1000);

            }else{
              showErrorToaster(data.message);
              printErrorMsg(data.error);
            }
          });
      }
    })
  }

  function softDelete(b) {
		swal({ title: "Are you sure?",icon: 'warning', dangerMode: true,
			buttons: {
				cancel: 'No, Please!',
				delete: 'Yes, Delete It'
			}
		}).then(function (willDelete) {
			if (willDelete) {
			  $.ajax({url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}/" + b, type: "DELETE", dataType: "html"})
            .done(function (a) {
              var data = JSON.parse(a);
              if(data.flagError == false){
                showSuccessToaster(data.message);          
                setTimeout(function () {
                  table.ajax.reload();
                  }, 1000);
              }else{
                showErrorToaster(data.message);
                printErrorMsg(data.error);
              }   
            }).fail(function () {
                    showErrorToaster("Something went wrong!");
            });
			} 
		});
  }

</script>
@endpush

