<div id="business-types-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
    
        <!-- <div class="modal-content">
            
            
            <form id="departmentForm" name="departmentForm" role="form" method="POST" action="" class="ajax-submit">
                {{ csrf_field() }}

                {!! Form::hidden('department_id', '' , ['id' => 'department_id'] ); !!}
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Name*', ['class' => 'col-sm-2 col-form-label text-alert']) !!}
                        {!! Form::text('name', '', ['class' => 'form-control form-control-lg mb-2', 'id' => 'name', 'placeholder'=>'Department Name']) !!}
                    </div>                        		
                </div>
                <div class="modal-footer">					
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-success ajax-submit">Submit</button>
                </div>
            </form>
        </div> -->
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">{{ $page->title ?? ''}} Form</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger alert-messages print-error-msg" style="display:none;"><ul></ul></div>
                <div class="alert alert-success fade alert-messages print-success-msg" style="display:none;"></div>
            <form id="businessTypesForm" name="businessTypesForm" role="form" method="POST" action="" class="ajax-submit">
                {{ csrf_field() }}
                {!! Form::hidden('business_types_id', '' , ['id' => 'business_types_id'] ); !!}
                <div class="card-body">
                  <div class="form-group">
                    {!! Form::label('name', 'Enter Business type*', ['class' => '']) !!}
                    {!! Form::text('name', '', ['class' => 'form-control', 'id' => 'name', 'placeholder'=>'Enter Business type']) !!}
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="modal-footer">					
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-success ajax-submit">Submit</button>
                </div>
            </form>
            </div>

          </div>
          <!-- /.modal-content -->
    </div>
</div>