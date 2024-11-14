{{ csrf_field() }}
{!! Form::hidden('instore_id', $customerInstoreCredit->id ??  '', ['id' => 'instore_id']) !!}
<div class="row">
    <div class="input-field col m6 s12">
        {!! Form::text('in_store_credit', $customerInstoreCredit->over_paid ?? '', [
            'class' => 'check_numeric',
            'id' => 'in_store_credit',
        ]) !!}
        <label for="in-score-credit" class=" label-placeholder active instoreLable" id="credit_lable"> Credit Amount </label>
    </div>
    <div>
        <div class="input-field col m6 s12 ">
            <input type="text" name="validity_from" id="validity_from" class="form-control" onkeydown="return false"
                autocomplete="off" value="{{ $customerInstoreCredit->validity_from ?? '' }}" />
            <label for="validity_from" class="label-placeholder active instoreLable">Validity Starting Date </label>
        </div>

        <div class="input-field col m6 s12 ">
            {!! Form::select(
                'validity',
                [
                    '2' => '2 Days',
                    '5' => '5 Days',
                    '7' => '7 Days',
                    '10' => '10 Days',
                    '15' => '15 Days',
                    '30' => '30 Days',
                    '60' => '60 Days',
                    '90' => '90 Days',
                    '180' => '6 Month',
                    '365' => '1 Year',
                ],
                $customerInstoreCredit->validity ?? '',
                ['class' => 'validity form-control'],
            ) !!}
            <label for="validity" class="label-placeholder label-placeholder active instoreLable">Validity Period</label>
        </div>
    </div>

</div>
