@extends('auth.auth_app')

@section('page-style')
<link rel="stylesheet" type="text/css" href="{{asset('admin/css/pages/forgot.css')}}">
@endsection
@section('content')

<div id="forgot-password" class="row">
  <div class="col s12 m6 l4 z-depth-4 offset-m4 card-panel border-radius-6 forgot-card bg-opacity-8">
    <form method="POST" action="{{ route('forget.password.post') }}" id="resetPasswordmailForm" >
        @csrf
        <div class="row">
            <div class="input-field col s12">
            <h5 class="ml-4">Forgot Password</h5>
            <p class="ml-4">You can reset your password</p>
            </div>
        </div>

        <div class="row">
            @if ($errors->has('email'))
            <div class="card-alert card red lighten-5 alert-danger"><div class="card-content red-text">
                
            {{ $errors->first('email') }}
            
        
            </div></div>
        @endif
        </div>

        <div class="row">
            <div class="input-field col s12">
            <i class="material-icons prefix pt-2">person_outline</i>
            <input id="email" type="email"  name="email" value=""  autocomplete="email" autofocus>
            <label for="email" class="center-align">Email</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12">
            <button type="submit" class="btn waves-effect waves-light border-round gradient-45deg-purple-deep-orange col s12 mb-1" id="submit-btn">Send Password Reset Link </button>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s6 m6 l6">
            <p class="margin medium-small"><a href="{{url('login')}}">Login</a></p>
            </div>
            <div class="input-field col s6 m6 l6">
            <!-- <p class="margin right-align medium-small"><a href="">Register</a></p> -->
            </div>
        </div>
    </form>
  </div>
</div>
@endsection

@push('page-scripts')
<script type="text/javascript">

$("#submit-btn").on("click", function(event){
    event.preventDefault();
    $('#submit-btn').html('Please Wait...');
    $("#submit-btn"). attr("disabled", true);
    $( "#resetPasswordmailForm" ).submit();
});

$(".alert-danger").delay(1000).addClass("in").toggle(true).fadeOut(3000);

</script>
@endpush


 
