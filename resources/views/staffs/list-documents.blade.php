@if(isset($documents)) 
  @foreach($documents as $document)
    @php
      if(pathinfo($document->name, PATHINFO_EXTENSION) == 'pdf'){
        $document_image = asset('admin/images/demo_pdf.png') ;
      }else{
        $document_image = ($document->name != null) ? asset('storage/store/users/documents/' . $document->name) : asset('admin/images/image-not-found.png');
      }
    @endphp

    <div class="col s12 l2">
      <div class="card">
        <div class="card-image waves-effect waves-block waves-light">

          <img class="activator" src="{{$document_image}}" alt="office" height="200px" style="max-height:100%;" />
        </div>
        <div class="card-content">
          <label class="card-content red-text document-error" id="document-div-{{$document->id}}"></label>
          <span class="card-title activator grey-text text-darken-4" id="span-"{{$document->id}}"> 
            {!! Form::text('details', $document->details ?? '',  ['class' => 'detail-input', 'id' => 'details-'.$document->id, 'placeholder' => 'Please enter document details']) !!} </span>
            <!-- <input type="number" id="num-{{$document->id}}" onChange="getValidation(3, {{$document->id}})" name="num" /> -->
          
          <a class="btn-floating mb-1 btn-flat waves-effect waves-light  blue accent-2 white-text" id="{{$document->id}}" onclick="updateDetails(this.id)" href="javascript:" data-target="dropdown2"><i class="material-icons">add</i></a>
          <a class="btn-floating mb-1 btn-flat waves-effect waves-light  blue accent-2 white-text" href="{{ route('download-files', ['document' => $document->name])}}" data-target="dropdown2"><i class="material-icons">cloud_download</i></a>
          <a class="btn-floating mb-1 btn-flat waves-effect waves-light  red accent-2 white-text" id="{{$document->name}}" onclick="deleteDocument(this.id)" href="javascript:" data-target="dropdown2"><i class="material-icons">delete</i></a>

        </div>
      </div>
    </div>
  @endforeach


@endif