  <div id="paymentType-modal" class="modal">
  <form id="paymentTypeForm" name="paymentTypeForm" role="form" method="POST" action="" class="ajax-submit">
    {{ csrf_field() }}
    {!! Form::hidden('paymentType_id', '' , ['id' => 'paymentType_id'] ); !!}
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">Payment Type Form</h4> </div>
            <div class="card-alert card red lighten-5 print-error-msg" style="display:none"><div class="card-content red-text"><ul></ul></div></div>            
                <div class="card-body" id="paymentTypeFields">

                      <div class="row">
                        <div class="input-field col m12 s12">
                          {!! Form::text('name',  '' , ['id' => 'paymentTypeName' ,'class' => '']) !!}
                          <label for="paymentTypeName" class="label-placeholder">Payment Type</label>
                        </div>
                      </div>

                      
              </div>
            <div class="modal-footer">
                <button class="btn waves-effect waves-light modal-action modal-close" type="reset" id="paymentFormReset">Close</button>
                <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="paymentTypename-submit-btn">Submit <i class="material-icons right">send</i></button>
            </div>
        </div>
    </form>
  </div>