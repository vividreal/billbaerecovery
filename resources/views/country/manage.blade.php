<div id="business-types-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
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
            <form id="{{$page->entity}}Form" name="{{$page->entity}}Form" role="form" method="POST" action="" class="ajax-submit">
                {{ csrf_field() }}
                {!! Form::hidden('country_id', '' , ['id' => 'country_id'] ); !!}
                <div class="card-body">
                  <div class="form-group">
                    {!! Form::label('name', 'Enter Country *', ['class' => '']) !!}
                    {!! Form::text('name', '', ['class' => 'form-control', 'id' => 'name', 'placeholder'=>'Enter Country']) !!}
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