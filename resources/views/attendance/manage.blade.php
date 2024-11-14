<div id="edit-marking-modal" class="modal">
  <form id="editMarkingForm" name="editMarkingForm" role="form" method="POST" action="" class="ajax-submit">
    <div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">Update Time</h4> </div>
        {{ csrf_field() }}
        {!! Form::hidden('pageTitle', Str::camel($page->title), ['id' => 'pageTitle'] ); !!}
        {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute'] ); !!}
        {!! Form::hidden('attendanceId', '', ['id' => 'attendanceId'] ); !!}
        {!! Form::hidden('markingAction', '', ['id' => 'markingAction'] ); !!}
        {!! Form::hidden('staffID', '', ['id' => 'staffID'] ); !!}
        <div class="card-body" id="additionalTaxFields">
          <div class="row">
            <div class="input-field col s6">
              <label for="attendance_time" class="label-placeholder active" id="attendance_time_label"> Time <span class="red-text">*</span></label>
              <input type="text" name="attendance_time" id="attendance_time" class="form-control" onkeydown="return false" autocomplete="off" value="" />
            </div>
          </div>
        </div>
    </div>
    <div class="modal-footer">
      <button class="btn waves-effect waves-light modal-action modal-close" type="button" id="edit-marking-reset-btn">Close</button>
      <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="edit-marking-submit-btn">Update <i class="material-icons right">send</i></button>
    </div>
  </form>
</div>