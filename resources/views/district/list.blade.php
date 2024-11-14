@extends('layouts.app')

@section('content')

@section('breadcrumb')
  <li class="nav-item">
    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
  </li>
  <li class="nav-item d-none d-sm-inline-block">
    <a href="{{ url(ROUTE_PREFIX.'/home') }}" class="nav-link">Home</a>
  </li>
  <li class="nav-item d-none d-sm-inline-block">
    <a href="{{ url(ROUTE_PREFIX.'/'.$page->route) }}" class="nav-link">{{ $page->title ?? ''}} </a>
  </li>
@endsection

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">{{ $page->title ?? ''}}</h1>
          </div><!-- /.col -->

          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
            <button class="btn btn-success ajax-submit" onclick="manageState(null)">Add {{ $page->title ?? ''}}</button>

            </ol>
          </div>

        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

     <!-- Main content -->
     <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ $page->title ?? ''}} Table</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                        <table class="table table-hover table-striped table-bordered data-tables"
                               data-url="{{ $page->link.'/lists' }}" data-form="page" data-length="20">
                               <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Country</th>
                                    <th>State</th>
                                    <th>District</th>
                                    <th width="100px">Action</th>
                                </tr>
                            </thead>
                        </table>

                        
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
      

@include('district.manage')
@endsection
@push('page-scripts')
<script src="{{ asset('admin/js/common-script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<script>

  var table;
  var role        = '{{ROUTE_PREFIX}}';
  var link        = '{{$page->link}}';
  var entity      = '{{$page->entity}}';
  var selected    = '';

  $(function () {
    table = $('.data-tables').DataTable({
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
            {data: 'country', name: 'name'},
            {data: 'state', name: 'name'},
            {data: 'name', name: 'name'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
  });
  

  function manageState(district_id){
    validator.resetForm();
    $('input').removeClass('error');
    $('select').removeClass('error');

    if (district_id === null) {
        $("#{{$page->entity}}Form")[0].reset();
        $('#{{$page->entity}}Form').find("input[type=text]").val("");
        $("#district_id").val('');
        $("#business-types-modal").modal("show");
    } else {
        $.ajax({url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}/" + district_id + "/edit", type: "GET", dataType: "html"})
            .done(function (a) {
                var data = JSON.parse(a);
                if(data.flagError == false){
                    $("#district_id").val(data.data.id);
                    $("#country_id").val(data.data.state.country.id);  
                    $("#{{$page->entity}}Form input[name=name]").val(data.data.name);
                    $("#state_block").html('');
                    var selectTerms = '<select id="country_id" class="form-control valid" name="country_id" aria-invalid="false"><option value="">Select a state</option>';
                    $.each(data.states, function(key, value) {
                      selected = '';
                      if (value.id == data.data.state.id) {
                        selected = 'selected';
                      }
                      selectTerms += '<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>';
                    });
                    selectTerms +='</select>';
                    $("#state_block").html(selectTerms);
                    $("#business-types-modal").modal("show");
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
            },
            country_id: {
                  required: true,
            },
            state_id: {
                  required: true,
            },
            
        },
        messages: { 
          name: {
            required: "Please enter district name",
            maxlength: "Length cannot be more than 30 characters",
            },
          country_id: {
            required: "Please select country",
            },
          state_id: {
            required: "Please select state",
            }
        },
        submitHandler: function (form) {
          id = $("#district_id").val();
          district_id   = "" == id ? "" : "/" + id;
          formMethod  = "" == id ? "POST" : "PUT";
          var forms = $("#{{$page->entity}}Form");
          $.ajax({ url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}" + district_id, type: formMethod, processData: false, 
          data: forms.serialize(), dataType: "html",
          }).done(function (a) {
            var data = JSON.parse(a);
            if(data.flagError == false){
                showSuccessToaster(data.message);                
                $("#business-types-modal").modal("hide");
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
           
    Swal.fire({
      title: 'Are you sure want to delete ?',
      text: "You won't be able to revert this!",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
      }).then(function(result) {
          if (result.value) {
              $.ajax({url: "{{ url(ROUTE_PREFIX.'/'.$page->route) }}/" + b, type: "DELETE", dataType: "html"})
                  .done(function (a) {
                      var data = JSON.parse(a);
                      if(data.flagError == false){
                        showSuccessToaster(data.message);          
                        setTimeout(function () {
                          table.ajax.reload();
                          }, 2000);

                    }else{
                      showErrorToaster(data.message);
                      printErrorMsg(data.error);
                    }   
                  }).fail(function () {
                          showErrorToaster("Somthing went wrong!");
                  });
          }
      });
  }

  $(document).on('change', '#country_id', function () {
    $.ajax({
          url: "{{ url(ROUTE_PREFIX.'/common/get-states') }}/",
          type: "GET",
          data:{'country_id':this.value },
          dataType: "html"
      }).done(function (data) {
      console.log(data);
        $("#state_block").html(data);
      })
});

</script>
@endpush


