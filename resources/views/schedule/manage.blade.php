<style>
    #manage-schedule-modal {
        padding: 20px;
        overflow-x: hidden;
    }
    .service_list {
    background-color: #f9f9f9; /* Light background color */
    padding: 10px; /* Padding around the content */
    border: 1px solid #ddd; /* Border for better definition */
    border-radius: 4px; /* Rounded corners */
}

/* Additional styling for the disabled state */
.service_list.disabled {
    background-color: #e9ecef; /* Light grey background for disabled state */
    border-color: #ccc; /* Lighter border color */
    color: #6c757d; /* Text color indicating disabled state */
    pointer-events: none; /* Disable all mouse events */
    opacity: 0.6; /* Slightly transparent */
}
</style>
<div id="manage-schedule-modal" class="modal">
    <div class="card-alert card red lighten-5 print-error-msg" style="display:none">
        <div class="card-content red-text">
            <ul></ul>
        </div>
    </div>
    <div class="modalcontent">
        <div class="modal-header">
            <a class="btn-floating mb-1 waves-effect waves-light right modal-close"><i
                    class="material-icons">clear</i></a>
            <h4 class="modal-title">Schedule Form</h4>
        </div>
        <div class="card-body">
            <form id="manageScheduleForm" name="manageScheduleForm" role="form" method="POST" action=""
                class="ajax-submit">
                {{ csrf_field() }}
                {!! Form::hidden('schedule_id', '', ['id' => 'schedule_id']) !!}
                {!! Form::hidden('grand_total', '', ['id' => 'grand_total']) !!}
                {!! Form::hidden('total_minutes', '', ['id' => 'total_minutes']) !!}
                {!! Form::hidden('receive_payment', '', ['id' => 'receive_payment']) !!}
                {!! Form::hidden('start', '', ['id' => 'start']) !!}
                {!! Form::hidden('customer_id', '', ['id' => 'customer_id']) !!}
                {!! Form::hidden('package_id', '', ['id' => 'package_id']) !!}
                {!! Form::hidden('bill_id', '', ['id' => 'bill_id']) !!}
                {!! Form::hidden('service_id', '', ['id' => 'service_id']) !!}
                {!! Form::hidden('payment_status', '', ['id' => 'payment_status']) !!}
                <div class="row">
                    <div class="input-field col m8 s12" id="custom-templates">
                        <input type="text" name="search_customer" id="search_customer" class="typeahead autocomplete"
                            autocomplete="off" value=""
                            placeholder = "Search Name or Mobile for existing Customers...">
                        <label for="search_customer"
                            class="typeahead label-placeholder active searchCustomerLabel">Search Name or Mobile for
                            existing Customers...</label>
                    </div>
                    <div class="input-field col m2 s12" id="customerActionDiv" style="display:none;">
                        <a class="btn-floating mb-1 btn-flat waves-effect waves-light pink accent-2 white-text amber darken-4 tooltipped"
                            data-position="bottom" data-tooltip="View Customer" target="_blank" id="customerViewLink">
                            <i class="material-icons">remove_red_eye</i>
                        </a>
                        <a class="btn-floating mb-1 btn-flat waves-effect waves-light pink accent-2 white-text tooltipped"
                            data-position="bottom" data-tooltip="Remove Customer" onclick="removeCustomer()">
                            <i class="material-icons">cancel</i>
                        </a>
                    </div>
                    <div class="input-field col m4 s12" id="newCustomerBtn">
                        <a class="waves-effect waves-light btn-small cyan" style="margin-top: 12px;"
                            id="add_new_customer">Add New Customer</a>
                    </div>
                </div>
                <div class="row">
                    <div class="row new-customer-form" style="display:none;">
                        <div class="input-field col m6 s6 l6 ">
                            <input type="text" name="customer_name" id="customer_name"
                                class="autocomplete disabled ignore-validation" autocomplete="off" value="">
                            <label for="customer_name" class="label-placeholder active">Customer Name<span
                                    class="red-text">*</span></label>
                        </div>
                        <div class="input-field col m6 s6 l6">
                            <input id="mobile" name="mobile" type="text" class="check_numeric disabled">
                            <label for="mobile" class="label-placeholder active"> Mobile </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="row">
                        <div class="input-field col m6 s12 user-details">
                            {!! Form::select('user_id', $variants->therapists, '', [
                                'id' => 'user_id',
                                'class' => 'select2 browser-default',
                                'placeholder' => 'Please select Therapist',
                            ]) !!}
                        </div>
                        <div class="input-field col m6 s12 user-details">
                            {!! Form::select('room_id', $variants->rooms, '', [
                                'id' => 'room_id',
                                'class' => 'select2 browser-default',
                                'placeholder' => 'Please select Room',
                            ]) !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col m6 s12  user-details">
                            <input type="text" name="start_time" id="start_time" class="form-control"
                                onkeydown="return false" autocomplete="off" value="" />
                        </div>
                        <div class="input-field col m6 l4" id="check_label">
                            <p><label>
                                    <input class="validate" name="checked_in" id="checked_in" value="1"
                                        type="checkbox">
                                    <span>Customer Checked In</span>
                                </label> </p>
                            <div class="input-field">
                            </div>
                        </div>

                    </div>
                    <!-- <div class="row">
                      <div class="input-field col m6 s12 user-details">
                       {!! Form::select('customer_id', $variants->customers, '', [
                           'id' => 'customer_id',
                           'class' => 'select2 browser-default',
                           'placeholder' => 'Please select Customer',
                       ]) !!}
                      </div>
                      <div class="input-field col m4 s12 user-details" id="newCustomerBtn">
                        <p>
                          <label for="add_new_customer">
                            <input type="checkbox" name="add_new_customer" id="add_new_customer" value="" />
                            <span>Create New Customer</span>
                          </label>
                        </p>
                      </div>
                    </div> -->
                    <div class="row">
                        <div class="input-field col m6 s6">
                            <div class="select-wrapper">
                                <select class="select2 browser-default service_list" name="service_type" id="service_type">
                                    <option value="">Please select type</option>
                                    <option value="1">Services</option>
                                    <option value="2">Packages</option>
                                </select>

                            </div>
                        </div>

                        <div class="input-field col m6 s6">
                            <div class="select-wrapper">
                                <div id="services_block">
                                    @include('layouts.loader')
                                    <select class="select2 browser-default service-type " data-type="services"
                                        name="bill_item[]" id="services" multiple="multiple"> </select>
                                </div>
                                <div id="packages_block" style="display:none;">
                                    @include('layouts.loader')
                                    <select class="select2 browser-default service-type package_items"
                                        data-type="packages" name="bill_item[]" id="packages" multiple="multiple">
                                    </select>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="service_from_package" style="display:none;">
                        <div class="input-field col m6 s6">
                            <div class="select-wrapper">
                                {!! Form::select('package_of_service', $variants->services, '', [
                                    'id' => 'package_of_service',
                                    'class' => 'select2 browser-default',
                                    'disabled' => 'disabled',
                                ]) !!}

                            </div>
                        </div>

                    </div>
                    <div class="row" id="itemDetailsDiv" style="display:none">
                        <div class="input-field col s12">
                            <table class="responsive-table" id="itemDetails">
                                <thead>
                                    <tr>
                                        <!-- <th>#</th> -->
                                        <th>Name</th>
                                        {{-- <th class="packageCount">Number Of Items</th> --}}
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- <div class="row" id="itemDetailsDiv" style="display:none;">
                      <div class="input-field col m12 s6"><ul class="collection" id="itemDetails"></ul></div>
                    </div> --}}
                </div>
        </div>
        @can('schedule-delete')
            <button class="btn orange waves-effect waves-light modal-action" type="button" id="cancelSchedule"
                style="display:none;">Cancel Schedule</button>
        @endcan
        @can('schedule-create')
            <button class="btn waves-effect waves-light modal-action " type="button" id="receivePaymentBtn"
                style="display:none;">Receive payment</button>
            <button class="btn cyan waves-effect waves-light form-action-btn" type="submit" name="action"
                id="schedule-submit-btn">Submit<i class="material-icons right">send</i></button>
        @endcan
    </div>
    {{-- modal content close --}}

    </form>
</div>
