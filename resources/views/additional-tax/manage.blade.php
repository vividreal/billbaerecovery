  <div id="additionalTax-modal" class="modal">
    <form id="additionalTaxForm" name="additionalTaxForm" role="form" method="POST" action="" class="ajax-submit">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">Additional Tax Form</h4> </div>
              {{ csrf_field() }}
              {!! Form::hidden('additionalTax_id', '', ['id' => 'additionalTax_id'] ); !!}
              {!! Form::hidden('additionalTaxRoute', url($page->additionalTaxRoute), ['id' => 'additionalTaxRoute'] ); !!}
                <div class="card-body" id="additionalTaxFields">
                    <div class="row">
                      <div class="input-field col s12">
                        {!! Form::text('name', '', ['id' => 'name']) !!}
                        <label for="name" class="label-placeholder">Tax name<span class="red-text">*</span></label>
                      </div>
                    </div>
                    <div class="row">
                      <div class="input-field col s12">
                        {!! Form::text('percentage', '', ['id' => 'percentage', 'class' => 'check_numeric']) !!}
                        <label for="percentage" class="label-placeholder">Tax % <span class="red-text">*</span></label>
                      </div>
                    </div>
                    <div class="row">
                      <div class="input-field col s12">
                        {!! Form::textarea('information', '', ['id' => 'information','class' => 'materialize-textarea', 'rows'=>3]) !!}  
                        <label for="information" class="label-placeholder">Additional information</label>
                      </div>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <button class="btn waves-effect waves-light modal-action modal-close" type="button" id="additionalTax-reset-btn">Close</button>
            <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="additionalTax-submit-btn">Submit <i class="material-icons right">send</i></button>
        </div>
    </form>
  </div>