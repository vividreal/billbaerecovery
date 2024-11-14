<div id="new-customer-modal" class="modal">
    <form id="newCustomerForm" name="newCustomerForm" role="form" method="POST" action="" class="ajax-submit">
        {{ csrf_field() }}
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Customer Form</h4>
            </div>
            <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
                <div class="card-content red-text">
                    <ul></ul>
                </div>
            </div>
            <div class="alert alert-success fade alert-messages print-success-msg"></div>
            <div class="card-body">
                <div class="row">
                    <div class="input-field col m12 s12">
                        {!! Form::text('name', '', ['id' => 'name']) !!}
                        <label for="name" class="label-placeholder active">Customer Name <span
                                class="red-text">*</span></label>
                    </div>
                    <div class="input-field col m6 s12">
                    {!! Form::select('phone_code',$variants->phonecode, null, [
                        'id' => 'phone_code',
                        'class' => 'select2 browser-default',
                        'placeholder' => 'Please select phone code',
                    ]) !!}
                    
                    </div>

                    <div class="input-field col m6 s12">
                        {!! Form::text('new_customer_mobile', '', ['id' => 'new_customer_mobile', 'class' => 'check_numeric']) !!}
                        <label for="new_customer_mobile" class="label-placeholder active">Customer Mobile <span
                                class="red-text">*</span></label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col m6 s12">
                        <input type="text" name="dob" id="dob" class="form-control"
                            onkeydown="return false" autocomplete="off" value="" />
                        <label for="dob" class="label-placeholder active">Customer DOB <span
                                class="red-text">*</span></label>
                    </div>
                    <div class="input-field col m6 s12">
                        {!! Form::text('new_customer_email', '', ['id' => 'new_customer_email']) !!}
                        <label for="new_customer_email" class="label-placeholder active">Customer Email</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col m6 s12">
                        <p>
                            <label>
                                <input value="1" id="male" name="gender" type="radio" checked />
                                <span> Male </span>
                            </label>
                            <label>
                                <input value="2" id="female" name="gender" type="radio" />
                                <span> Female </span>
                            </label>
                            <label>
                                <input value="3" id="others" name="gender" type="radio" />
                                <span> Others </span>
                            </label>
                        </p>
                    </div>
                    <div class="input-field col m6 s12">
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button class="btn waves-effect waves-light modal-action modal-close" type="reset"
                id="resetForm">Close</button>
            <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit <i
                    class="material-icons right">send</i></button>
        </div>
    </form>
</div>
