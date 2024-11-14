<div id="import-browse-modal" class="modal">
    <form id="importCustomerForm" name="importCustomerForm" role="form" method="POST" action="{{ route('customer.import') }}" enctype="multipart/form-data">
        <div class="modal-content">
          <a class="btn-floating mb-1 waves-effect waves-light right modal-close"><i class="material-icons">clear</i></a>
          <div class="modal-header"><h4 class="modal-title">Upload Customers </h4> </div> 
          <a href="{{ url('/') }}/sample/customers.csv" class="">Download sample CSV file.</p></a>
            {{ csrf_field() }}
              <div class="card-body" id="formFields">
                <div class="row">                      
                  <div class="input-field">
                    <!-- <div class="btn"> -->
                      <span>File</span>
                      <input class="errorDiv" type="file" name="file">
                  </div>
                </div>
              </div>
        </div>
        <div class="modal-footer">
          <button class="btn waves-effect waves-light modal-action modal-close" type="reset" id="resetForm">Close</button>
          <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="import-submit-btn">Submit <i class="material-icons right">send</i></button>
        </div>
    </form>
  </div>

  
@push('page-scripts')
<!-- date-time-picker -->
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
<script>





</script>
@endpush