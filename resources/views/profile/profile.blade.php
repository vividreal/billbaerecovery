@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css" />
@endsection

{{-- page style --}}
@section('page-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/pages/page-users.css') }}">
@endsection

@section('page-css')
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ url($page->route) }}">{{ $page->title ?? '' }}</a></li>
        <li class="breadcrumb-item active">Update</li>
    </ol>
@endsection
<div class="section users-edit section-data-tables">

    <div class="card">
        <div class="card-content">
            <ul class="tabs mb-2 row">
                <li class="tab">
                    <a class="display-flex align-items-center active" id="account-tab" href="#account">
                        <i class="material-icons mr-1">account_circle</i><span> Profile</span>
                    </a>
                </li>
                <li class="tab">
                    <a class="display-flex align-items-center" id="information-tab" href="#changePasswordTab">
                        <i class="material-icons mr-2">lock_open</i><span>Change Password</span>
                    </a>
                </li>
            </ul>
            <div class="divider mb-3"></div>
            <div class="row">
                @if ($user)
                    <div class="col s12" id="account">
                        <div class="media display-flex align-items-center mb-2">
                            <a class="mr-2" href="javascript:">
                                <img src="{{ auth()->user()->profile_url }}" class="border-radius-4" alt="profile image"
                                    id="user_profile" height="64" width="64">
                            </a>
                            <div class="media-body">
                                <form id="storeAdminImageForm" name="storeAdminImageForm" action="" method="POST"
                                    enctype="multipart/form-data" class="ajax-submit">
                                    {{ csrf_field() }}
                                    {!! Form::hidden('photoRoute', url('update-user-photo'), ['id' => 'photoRoute']) !!}
                                    <h5 class="media-heading mt-0">Photo</h5>
                                    <div class="user-edit-btns display-flex">
                                        <a id="select-files" class="btn indigo mr-2"><span>Browse</span></a>
                                        <a href="#" class="btn-small btn-light-pink logo-action-btn"
                                            id="removeLogoDisplayBtn" style="display:none;">Remove</a>
                                        <button type="submit" class="btn btn-success logo-action-btn"
                                            id="uploadLogoBtn" style="display:none;">Upload</button>
                                    </div>
                                    <small>Allowed JPG, JPEG or PNG extension only.</small>
                                    <div class="upfilewrapper" style="display:none;">
                                        <input id="profile" type="file" accept="image/png, image/jpeg, image/jpg"
                                            name="image" class="image" />
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- users edit account form start -->
                        <h4 class="card-title">{{ $page->title ?? '' }} Form</h4>
                        <form id="profileForm" name="profileForm" class="ajax-submit">
                            {{ csrf_field() }}
                            {!! Form::hidden('user_id', $user->id ?? '', ['id' => 'user_id']) !!}
                            {!! Form::hidden('pageRoute', url($page->route), ['id' => 'pageRoute']) !!}
                            <div class="row">
                                <div class="col s6">
                                    <div class="input-field">
                                        {!! Form::text('name', $user->name ?? '', ['id' => 'name']) !!}
                                        <label for="name" class="label-placeholder active">Name <span
                                                class="red-text">*</span></label>
                                    </div>
                                </div>
                                <div class="col s6">
                                    <div class="input-field">
                                        {!! Form::text('email', $user->email ?? '') !!}
                                        <label for="email" class="label-placeholder active">Email <span
                                                class="red-text">*</span></label>
                                        <small class="errorTxt2"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col s2">
                                    <div class="input-field">
                                        {!! Form::select('phone_code', $variants->phoneCode, $user->phone_code ?: $store->country_id ?? '', [
                                            'id' => 'phone_code',
                                        ]) !!}
                                        <label for="mobile" class="label-placeholder active">Phone code <span
                                                class="red-text">*</span></label>
                                        <span class="helper-text" data-error="wrong" data-success="right">Currently
                                            service is supported in India only!</span>


                                    </div>
                                </div>
                                <div class="col s4">
                                    <div class="input-field">
                                        {!! Form::text('mobile', $user->mobile ?? '', ['class' => '']) !!}
                                        <label for="mobile" class="label-placeholder active"> Mobile <span
                                                class="red-text">*</span></label>
                                        <small class="errorTxt2"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <button class="btn waves-effect waves-light" type="button" name="reset"
                                        id="profile-reset-btn">Reset <i
                                            class="material-icons right">refresh</i></button>
                                    <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                        id="profile-submit-btn">Submit <i
                                            class="material-icons right">keyboard_arrow_right</i></button>
                                </div>
                            </div>
                        </form>
                        <!-- users edit account form ends -->
                    </div>
                    <div class="col s12" id="changePasswordTab" style="display:none">
                        @include('layouts.error')
                        <h4 class="card-title">Update Password </h4>
                        {!! Form::open(['class' => 'ajax-submit', 'id' => 'passwordForm']) !!}
                        {{ csrf_field() }}
                        {!! Form::hidden('passwordRoute', url('update-password'), ['id' => 'passwordRoute']) !!}
                        <div class="row">
                            <div class="col s12">
                                <div class="input-field">
                                    {!! Form::password('old_password', ['class' => 'form-control', 'id' => 'old_password']) !!}
                                    <label for="old_password" class="label-placeholder active">Old Password<span
                                            class="red-text"> *</span></label>
                                </div>
                            </div>
                            <div class="col s12">
                                <div class="input-field">
                                    {!! Form::password('new_password', ['class' => 'form-control', 'id' => 'new_password']) !!}
                                    <label for="new_password" class="label-placeholder active">New Password<span
                                            class="red-text"> *</span></label>
                                </div>
                            </div>
                            <div class="col s12">
                                <div class="input-field">
                                    {!! Form::password('new_password_confirmation', [
                                        'id' => 'new_password_confirmation',
                                        'class' => 'form-control',
                                    ]) !!}
                                    <label for="new_password_confirmation" class="label-placeholder active">Confirm
                                        Password<span class="red-text"> *</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <button class="btn waves-effect waves-light" type="reset" name="reset"
                                    id="password-reset-btn">Reset <i class="material-icons right">refresh</i></button>
                                <button class="btn cyan waves-effect waves-light" type="submit" name="action"
                                    id="password-submit-btn">Submit <i
                                        class="material-icons right">keyboard_arrow_right</i></button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                @endif
            </div>
            <!-- </div> -->
        </div>
    </div>
</div>
@include('profile.crop-modal')

@endsection
{{-- vendor scripts --}}
@section('vendor-script')

@endsection

@push('page-scripts')
<script>
    var get_unique_email="{{route('customer.uniqueEmail')}}";
    var is_unique_email="{{route('isUniqueEmail')}}";
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
<script src="{{ asset('admin/js/cropper-script.js') }}"></script>
<script src="{{ asset('admin/js/custom/profile/profile.js') }}"></script>

@endpush
