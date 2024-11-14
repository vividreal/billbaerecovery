<div id="profile-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Profile Form</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger alert-messages print-error-msg" style="display:none;"><ul></ul></div>
                <div class="alert alert-success fade alert-messages print-success-msg" style="display:none;"></div>
                <form id="profileForm" name="profileForm" role="form" method="POST" action="" enctype="multipart/form-data" class="ajax-submit">
                    {{ csrf_field() }}
                    {!! Form::hidden('store_id', $store->id ?? '' , ['id' => 'store_id'] ); !!}
                    {!! Form::hidden('image_text', isset($store) && $store->image?$store->image:'' , ['id' => 'image_text'] ); !!}
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-file">
                            <input type="file" class="custom-file-input" name="image" id="image">
                            <label class="custom-file-label" for="customFile">Choose Image</label>
                            </div>
                            <div id="imgPreviewDiv" style="display:none;"> <img id="imgPreview" width="200px" height="150px" src="#" alt="your image" /></div>
                        </div>
                    </div>
                    <div class="modal-footer">					
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button class="btn btn-success ajax-submit">Submit</button>
                    </div>
                </form>
            </div>
          </div>
          <!-- /.modal-content -->
    </div>
</div>