@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '') 
@section('search-title') {{ $page->title ?? ''}} @endsection


{{-- vendor styles --}}
@section('vendor-style')

@endsection

{{-- page style --}}
@section('page-style')
  
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
  <a href="javascript:" onclick="manageserviceCategory(null)" class="btn waves-effect waves-light cyan breadcrumbs-btn right" type="submit" name="action">Add<i class="material-icons right">payment</i></a>
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
                      <table id="{{$page->entity}}-data-table" class="display data-tables">
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
@include('service-category.manage')
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
    table = $('#{{$page->entity}}-data-table').DataTable({
        pagination: true,
        pageLength: 10,
        responsive: true,
        searchDelay: 500,
        processing: true,
        serverSide: true,
        ajax: "{{ url(ROUTE_PREFIX.'/'.$page->route.'/lists') }}",
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false, width:20},
            {data: 'name', name: 'name'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width:100},
        ]
    });
  });
  

  function manageserviceCategory(serviceCategory_id){
    validator.resetForm();
    $('input').removeClass('error');

    if (serviceCategory_id === null) {
        $("#{{$page->entity}}Form")[0].reset();
        $('#{{$page->entity}}Form').find("input[type=text]").val("");
        $("#serviceCategory_id").val('');
        $("#serviceCategory-modal").modal("open");
    } else {
        $.ajax({url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}/" + serviceCategory_id + "/edit", type: "GET", dataType: "html"})
            .done(function (a) {
                var data = JSON.parse(a);
                if(data.flagError == false){
                    $("#serviceCategory_id").val(data.data.id);
                    $("#{{$page->entity}}Form input[name=name]").val(data.data.name);
                    $("#serviceCategory-modal").modal("open");
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
                  maxlength: 30,
            }
        },
        messages: { 
          name: {
            required: "Please enter Service category name",
            maxlength: "Length cannot be more than 30 characters",
            }
        },
        submitHandler: function (form) {
          id = $("#serviceCategory_id").val();
          serviceCategory_id   = "" == id ? "" : "/" + id;
          formMethod  = "" == id ? "POST" : "PUT";
          var forms = $("#{{$page->entity}}Form");
          $.ajax({ url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}" + serviceCategory_id, type: formMethod, processData: false, 
          data: forms.serialize(), dataType: "html",
          }).done(function (a) {
            var data = JSON.parse(a);
            if(data.flagError == false){
                showSuccessToaster(data.message);                
                $("#serviceCategory-modal").modal("close");
                setTimeout(function () {
                  table.ajax.reload();
                  }, 2000);

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

