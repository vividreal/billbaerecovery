@extends('layouts.admin.app')

@section('content')

@section('breadcrumb')
<h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
<ol class="breadcrumbs mb-0">
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX.'/roles') }}">{{ Str::plural($page->title) ?? '' }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

<style>
.admin-permissions [type=checkbox]:not(:checked), .admin-permissions [type=checkbox]:checked {
    position: inherit; 
    opacity: inherit; 
    pointer-events: none;
}
.admin-permissions input { margin-right: 10px }
.admin-permissions { margin-bottom: 15px!important; }
</style>

<div class="section section-data-tables">
    <div class="card">
        <div class="card-content">
            <p class="caption mb-0">{{ Str::plural($page->title) ?? '' }}. Lorem ipsum is used for the ...</p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">Manage {{ $role->name }} Permissions </h4>
                  
                    {!! Form::model($role, ['method' => 'PATCH','route' => ['admin.roles.update', $role->id]]) !!}
                        @csrf
                        <div class="row">
                            @include('layouts.success') 
                            @include('layouts.error')
                            <div class="col m6 s12">
                                {!! Form::text('name', $role->name ?? '', ['required' => 'required']) !!}
                            </div>
                            <div class="col m6 s12">
                                <label for="checkAll">
                                    <input type="checkbox" id="checkAll" />
                                    <span>Select All Permissions</span>
                                </label>
                            </div>

                            <div class="col s12">                    
                                <div class="container">
                                    <div class="row">
                                        @php
                                            // Group permissions by head
                                            $groupedPermissions = [];
                                            foreach ($permissions as $permission) {
                                                $groupedPermissions[$permission->head][] = $permission;
                                            }
                                        @endphp
                              
                                        @foreach ($groupedPermissions as $head => $permissionsGroup)
                                            <div class="col s12 m4">
                                                <h6 class="mt-5 mb-5">{{ $head }}</h6>
                                                @foreach ($permissionsGroup as $permission)
                                                    <p class="admin-permissions">
                                                        <label for="permission_{{ $permission->id }}">
                                                            <input name="permission[]" type="checkbox" class="permission-checkbox" id="permission_{{ $permission->id }}" value="{{ $permission->name }}" @if (in_array($permission->name, $rolePermissions->pluck('name')->toArray())) checked @endif>
                                                            {{ $permission->permission }}
                                                        </label>
                                                    </p>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col s12">
                                <button class="btn waves-effect waves-light" type="reset" name="reset">Reset 
                                    <i class="material-icons right">refresh</i>
                                </button>
                                <button class="btn cyan waves-effect waves-light" type="submit" name="action" id="submit-btn">Submit 
                                    <i class="material-icons right">send</i>
                                </button>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    // "Select All" functionality for checkboxes
    $('#checkAll').on('change', function() {
        $('.permission-checkbox').prop('checked', this.checked);
    });

    // Ensure "Select All" updates correctly based on individual checkbox changes
    $('.permission-checkbox').on('change', function() {
        $('#checkAll').prop('checked', $('.permission-checkbox:checked').length === $('.permission-checkbox').length);
    });

    // Initialize the state of the "Select All" checkbox on page load
    $('#checkAll').prop('checked', $('.permission-checkbox:checked').length === $('.permission-checkbox').length);
});
</script>
@endpush
