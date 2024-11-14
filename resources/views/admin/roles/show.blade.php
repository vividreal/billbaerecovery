@extends('layouts.app')

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/roles') }}">{{ Str::plural($page->title) ?? '' }}</a>
        </li>
        <li class="breadcrumb-item active">View</li>
    </ol>
@endsection

<style>
    .flex-container {
        display: flex;
    }

    .flex-container>div {
        margin: 10px;
        padding: 20px;
        font-size: 30px;
    }
</style>

@section('page-action')
@endsection

<div class="section section-data-tables">
    <div class="card">
        <div class="card-content">
            <p class="caption mb-0">{{ Str::plural($page->title) ?? '' }}. Lorem ipsum is used for the ...</p>
        </div>
    </div>
    <!-- DataTables example -->
    <div class="row" style="column-gap: 30px;">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ $role->name }} Permissions </h4>
                    <div class="row">
                        <div class="col s12">
                            <div class="container">
                                <div class="row">
                                    @php
                                        $groupedPermissions = [];
                                        foreach ($rolePermissions as $permission) {
                                            $groupedPermissions[$permission->head][] = $permission;
                                        }

                                        $columnCount = 0;
                                    @endphp

                                    @foreach ($groupedPermissions as $head => $permissions)
                                        @if ($columnCount % 3 === 0 && $columnCount !== 0)
                                </div>
                                <div class="row">
                                    @endif
                                    <div class="col s12 m4">
                                        <h6 class="mt-5 mb-5">{{ $head }}</h6>

                                        <!-- List all permissions under this head -->
                                        @foreach ($permissions as $permission)
                                            <p>
                                                <input type="checkbox" @if (auth()->user()->can($permission->name)) checked @endif
                                                    disabled>
                                                <span>{{ $permission->permission }}</span>
                                            </p>
                                        @endforeach
                                    </div>

                                    @php $columnCount++; @endphp
                                    @endforeach


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('page-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var elems = document.querySelectorAll('.collapsible');
                var instances = M.Collapsible.init(elems);
            });
        </script>
    @endpush
