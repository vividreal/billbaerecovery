<div id="discount-modal" class="modal">
    <form id="discountForm" name="discountForm" role="form" method="POST" action="" class="ajax-submit">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">Add Discount Form</h4> </div>
            <div class="alert alert-danger alert-messages print-error-msg"><ul></ul></div>
            <div class="alert alert-success fade alert-messages print-success-msg"></div>
            
                {!! Form::hidden('billing_id', $billing->id , ['id' => 'billing_id'] ); !!}
                {!! Form::hidden('billing_item_id', '' , ['id' => 'billing_item_id'] ); !!}
                {!! Form::hidden('discount_action', '' , ['id' => 'discount_action'] ); !!}

                {{ csrf_field() }}
                <div class="card-body">
                    <div class="row">
                        <div class="input-field col m6 s12">
                            {!! Form::select('discount_type', ['amount' => 'Amount', 'percentage' => 'Percentage'] , '' , ['id' => 'discount_type' ,'class' => 'select2 browser-default', 'placeholder'=>'Please select Discount type']) !!}
                        </div>
                        <div class="input-field col m6 s12">
                            {!! Form::text('discount_value', '', array('id' => 'discount_value')) !!}  
                            <label for="discount_value" class="label-placeholder">Discount value <span class="red-text">*</span></label> 
                        </div>
                    </div>
                </div>
            <div class="modal-footer">
                <button class="btn waves-effect waves-light modal-action modal-close" type="button" id="discount-reset-btn">Close</button>
                <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="discount-submit-btn">Submit <i class="material-icons right">send</i></button>
            </div>
        </div>
        
    </form>
  </div>