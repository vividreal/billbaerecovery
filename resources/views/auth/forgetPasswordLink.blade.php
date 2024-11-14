@extends('auth.auth_app')

@section('content')
@dd("Dfgjfhgfh")
<div class="card">
        <div class="card-body login-card-body">
        <p class="login-box-msg">Reset Password</p>


            @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
            @endif
            
        <form method="POST" action="{{ route('reset.password.post') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="input-group mb-3">
            <input id="email_address" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                
            <div class="input-group-append">
                <div class="input-group-text">
                <span class="fas fa-envelope"></span>
                </div>
            </div>
            
            </div>
            @if ($errors->has('email'))
                <span class="text-danger">{{ $errors->first('email') }}</span>
            @endif
            <div class="input-group mb-3">
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
            <div class="input-group-append">
                <div class="input-group-text">
                <span class="fas fa-lock"></span>
                </div>
            </div>
            </div>
                                    @if ($errors->has('password'))
                                      <span class="text-danger">{{ $errors->first('password') }}</span>
                                  @endif


            <div class="input-group mb-3">
            <input id="password-confirm" type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation" required autocomplete="current-password">
        
            <div class="input-group-append">
                <div class="input-group-text">
                <span class="fas fa-lock"></span>
                </div>
            </div>
            </div>
            @if ($errors->has('password_confirmation'))
                                      <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                                  @endif


            <div class="row">

            <div class="col-6">
            <button type="submit" class="btn btn-primary">
                                  Reset Password
                              </button>
            </div>
            </div>
        </form>
        </div>

    </div>
@endsection

@push('page-scripts')
<script type="text/javascript">

$(".alert-danger").delay(1000).addClass("in").toggle(true).fadeOut(3000);

</script>
@endpush




