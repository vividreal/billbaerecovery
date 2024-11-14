<div id="serviceCategory-modal" class="modal">
    <form id="{{$page->entity}}Form" name="{{$page->entity}}Form" role="form" method="POST" action="" class="ajax-submit">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">{{ $page->title ?? ''}} Form</h4> </div>
            <div class="alert alert-danger alert-messages print-error-msg"><ul></ul></div>
            <div class="alert alert-success fade alert-messages print-success-msg"></div>
            
                {{ csrf_field() }}
                {!! Form::hidden('serviceCategory_id', '' , ['id' => 'serviceCategory_id'] ); !!}
                <div class="card-body">
                    <div class="form-group">
                    <label for="name" class="label-placeholder">Name <span class="red-text">*</span></label>
                    {!! Form::text('name', '', ['id' => 'name']) !!}
                    
                    </div>
                </div>
            <div class="modal-footer">
                <button class="btn waves-effect waves-light modal-action modal-close" type="reset" id="resetForm">Close</button>
                <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit <i class="material-icons right">send</i></button>
            </div>
        </div>
        
    </form>
  </div>