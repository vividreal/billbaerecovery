@extends('auth.auth_app')

@section('content')

<div id="login-page" class="row">
        
        <div class="col s12 m6 l4 z-depth-4 card-panel border-radius-6 login-card bg-opacity-8">
        
        
            <form method="POST" action="{{ route('forget.password.post') }}" id="resetPasswordmailForm" >
                @csrf
                <div class="row">
                    <div class="input-field col s12">
                    <h5 class="ml-4">Reset Password</h5>
                    </div>

                </div>

                @if(session()->has('error'))<div class="card-alert card red lighten-5 alert alert-danger"><div class="card-content red-text"><p>{{ session()->get('error') }}</p></div></div>@endif


                
                <div class="row margin">
                    <div class="input-field col s12">
                    <i class="material-icons prefix pt-2">person_outline</i>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="validate" autocomplete="off" required>
                    <label for="username" class="center-align">Email</label>
                    @if ($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                    @endif
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                    <button class="btn waves-effect waves-light border-round gradient-45deg-purple-deep-orange col s12" type="submit" id="submit-btn" name="action">Send Password Reset Link </button>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s6 m6 l6">
                    <p class="margin right-align medium-small"><a href="{{url('login')}}">Login</a></p>
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


 
