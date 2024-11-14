<style>
    #manage-refund-modal {
        padding: 20px;
        overflow-x: hidden;
    }
</style>
<div id="manage-bill-refund-modal" class="modal">
    <form id="manageRefundForm" name="manageRefundForm" role="form" method="POST" action="" class="ajax-submit">
        <div class="modalcontent">
            <div class="modal-header">
                <a class="btn-floating mb-1 waves-effect waves-light right modal-close"><i
                        class="material-icons">clear</i></a>
                <h4 class="modal-title">Refund Form</h4>
            </div>
            <div class="card-body">
                {{ csrf_field() }}
                {!! Form::hidden('bill_id', '', ['id' => 'billing_id']) !!}
                {!! Form::hidden('service_item_id', '', ['id' => 'service_item_id']) !!}
                {!! Form::hidden('schedule_refund_id', '', ['id' => 'schedule_refund_id']) !!}
                {!! Form::hidden('schedule_package_id', '', ['id' => 'schedule_package_id']) !!}

                <table>
                    <tbody>
                        <tr>
                            <td colspan="2" id="service_append">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" id="package_append">
                            </td>
                        </tr>
                        <tr id="itemDiv" style="border-bottom: none;">
                            <td colspan="4">
                                <table class="responsive-table" id="itemList">
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
                            </td>
                        </tr>

                        <tr class="paymentType" style='border-bottom: 0px solid rgba(0, 0, 0, 0.12);'>
                            <td colspan="4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>Refund Using</td>
                                        </tr>
                                        @foreach ($variants->paymentTypes as $payment_type)
                                            <tr>
                                                <td>
                                                    <h6>{{ $payment_type->name }}</h6>
                                                </td>
                                                <td>
                                                    <input name="refund_amount[]" type="text" id="refund_amount"
                                                        data-name="{{ $payment_type->name }}"
                                                        data-id="{{ $payment_type->id }}" placeholder="Amount"
                                                        class="form-control" value="">
                                                    <label class="payment-value-error red-text"></label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h6>Total Refund: </h6>
                            </td>
                            <td><span id="total_paid_refund"></span></td>
                        </tr>
                        <tr>
                            <td>
                                <h6>Cancellation Fee:</h6>
                            </td>
                            <td> <span id="total_cancellation_fee"></span></td>

                        </tr>
                        <tr>
                            <td colspan="4">
                                <h6>Reason for Cancellation <span style="color: red;">*</span></h6>
                                <textarea name="comment" id="comment" cols="30" rows="10"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>




            </div>
            <div class="modal-footer">
                <button class="btn orange waves-effect waves-light modal-action modal-close" type="button"
                    id="cancelRefund">Cancel </button>

                <button class="btn cyan waves-effect waves-light form-action-btn" type="submit" name="action"
                    id="refund-submit-btn">Submit<i class="material-icons right">send</i></button>
            </div>
        </div>
    </form>
</div>
