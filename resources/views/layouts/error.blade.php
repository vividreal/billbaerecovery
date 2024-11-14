
<!-- Validator Return  -->
@if (count($errors) > 0)
  <div class="card-alert card red">
    <div class="card-content white-text">
      @foreach($errors->all() as $error)
        <p><i class="material-icons">error</i> {{ $error }}</p>
      @endforeach
    </div>
    <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">×</span>
    </button>
  </div>
@endif

<!-- Validator Return with ajax-->
<div class="card-alert card red print-error-msg" style="display:none">
  <div class="card-content white-text">
    <ul></ul>
  </div>
  <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">×</span>
  </button>
</div>

<!-- Validator Return with redirect -->
@if (Session::has('error'))
  <div class="card-alert card red">
    <div class="card-content white-text">
      <p><i class="material-icons">error</i> {!! Session::get('error') !!}</p>
    </div>
    <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
    </button>
  </div>
@endif


@if (Session::has('document-archived'))
<div class="card-alert card orange">
    <div class="card-content white-text">
      <p><i class="material-icons">error</i> {!! Session::get('document-archived') !!}</p>
    </div>
    <button type="button" class="close white-text" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
    </button>
  </div>
  @endif



