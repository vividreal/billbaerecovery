<div id="add-cash-modal" class="modal">
  <form id="addCashForm" name="addCashForm" role="form" method="POST" action="" class="ajax-submit">
    {{ csrf_field() }}
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">Add Cash Form</h4> </div>
            <div class="card-alert card red lighten-5 print-error-msg" style="display:none"><div class="card-content red-text"><ul></ul></div></div>            
                {{ csrf_field() }}
                <div class="card-body">
                      <div class="row">
                          <div class="input-field col m12 s12">
                            {!! Form::select('cash_book', [1 => 'Business Cash', 2 => 'Petty Cash'] , '' , ['id' => 'add_cash_book' ,'class' => 'select2 browser-default', 'placeholder' => "Add cash to"]) !!}
                            <!-- <label for="add_cash_book" class="label-placeholder">Add cash to <span class="red-text">*</span> </label> -->
                          </div>            
                      </div>

                      <div class="row" id="cashOptionDiv" style="display:none;">
                        <div class="input-field col m12 s12"> 
                          <label for="transaction" class="label-placeholder active">Choose Cash option</label>   
                              <p> 
                                <label>
                                  <input type="radio" id="radioPrimary1" value="add_cash" name="transaction" checked="" />
                                  <span>Cash From Hand </span>
                                </label>             
                                <label>
                                  <input type="radio" id="radioPrimary2" value="move_cash" name="transaction"  />
                                  <span> Move from <span id="move_from"></span> </span>
                                </label>     
                              </p>
                        </div>
                      </div>


                      <div class="row">
                        <div class="input-field col m12 s12">
                        {!! Form::text('amount',  '' , ['id' => 'add_amount' ,'class' => 'check_numeric']) !!}
                          <label for="add_amount" class="label-placeholder">Amount</label>
                        </div>
                      </div>

                      <div class="row">
                        <div class="input-field col m12 s12">
                          {!! Form::textarea('details', '', ['id'=>'details', 'class' => 'materialize-textarea']) !!}
                          <label for="details" class="label-placeholder">Details</label>
                        </div>
                      </div>

              </div>
            <div class="modal-footer">
                <button class="btn waves-effect waves-light modal-action modal-close" type="reset" id="resetForm">Close</button>
                <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit <i class="material-icons right">send</i></button>
            </div>
        </div>
    </form>
  </div>