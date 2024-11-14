@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
@section('search-title') {{ $page->title ?? '' }} @endsection

{{-- vendor styles --}}
@section('vendor-style')
@endsection

{{-- page style --}}
@section('page-style')
    <style>
        .validity {
            display: block !important
        }

        .image-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        #modal-image {
            max-width: 90%;
            max-height: 90%;
        }

        #modal-close {
            color: white;
        }

        .section-data-tables .profile-header-card.card {
            min-height: 100px !important;
        }
    </style>
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0">
        <span>{{ Str::plural($page->title) ?? '' }}</span>
    </h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">show</li>
    </ol>
@endsection

@section('page-action')

@endsection

<!-- users view start users-view section-data-tables invoice-list-wrapper -->
<div class="section section-data-tables">
    <!-- users view media object start -->
    <div class="card-panel">
        <!-- users view card details start -->
        <div class="row">
            <div class="col s12 l6 xl3">
                <input type="text" class="daterange" name="staff_date_range" id="staff_date_range" value=""
                    placeholder="Select Date Range">
            </div>
        </div>
        <div class="row">

            <div class="col s12 m6 l6 xl3">

                <div
                    class="card gradient-45deg-light-blue-cyan gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard text-white"> No of Services</h6>
                                <h5 class="center-align dashboard" id="noOfServices">
                                    {{ $variant->noOfServices }}
                                </h5>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class="col s12 m6 l6 xl3">
                <div
                    class="card gradient-45deg-amber-amber gradient-shadow min-height-100 white-text animate fadeRight">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard text-white"> No of Working Days</h6>
                                <h5 class="center-align dashboard" id="noOfWorkingDays">
                                    {{ $variant->noOfWorkingDays }}
                                </h5>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-red-pink gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard text-white"> No of Leaves</h6>
                                <h5 class="center-align dashboard" id="noOfLeaves">
                                    {{ $variant->noOfLeaves }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col s12 m6 l6 xl3">
                <div class="card gradient-45deg-green-teal gradient-shadow min-height-100 white-text animate fadeLeft">
                    <div class=" card-content">
                        <div class="row">
                            <div class="col s12 m12">

                                <h6 class="mb-0 mt-0 center-align dashboard text-white">Incentive</h6>
                                <h5 class="center-align dashboard" id="incentive">
                                    {{ $variant->incentive }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <div class="card profile-header-card ">
        <div class="card-content">
            <div class="row">
                <div class="col s12 m12">
                    <div class=" media">
                        <div class="media-body">
                            <div class="row d-flex align-items-center">
                                <div class="col s12 m6 d-flex align-items-center">

                                    <h6 class="media-heading m-0 ml-2">
                                        <span class="users-view-name">{{ $variant->staff->user->name }}</span>
                                        @if ($variant->staff->user->email != '')
                                            <span class="grey-text">@</span>
                                            <span
                                                class="users-view-username grey-text">{{ $variant->staff->user->email }}</span>
                                        @endif

                                    </h6>

                                </div>
                                <div class="col s12 m6 text-end">
                                    <div>
                                        <img src="{{ asset('storage/store/users/' . $variant->staff->user->profile) }}"
                                            alt="" srcset="">
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-content">
            <div class="row">

                <div class="col s12 m6">


                    <table class="striped">
                        <tbody>
                            <tr>
                                <td>Registered:</td>
                                <td>{{ $variant->staff->created_at }} </td>
                            </tr>
                            <tr>
                                <td>DOB:</td>

                                <td class="users-view-role">{{ $variant->staff->dob }}</td>
                            </tr>
                            <tr>
                                <td>Gender:</td>
                                <td>
                                    <span class=" users-view-status chip green lighten-5">
                                        @if ($variant->staff->user->gender == 1)
                                            Male
                                        @endif
                                        @if ($variant->staff->user->gender == 2)
                                            Female
                                        @endif
                                        @if ($variant->staff->user->gender == 3)
                                            Other
                                        @endif
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td>Joining Date:</td>
                                <td>{{ $variant->staff->joining_date }}
                                </td>
                            </tr>
                            <tr>
                                <td>Contract End Date:</td>
                                <td>{{ $variant->staff->contract_end_date }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <div class="col s12 m6">
                    <table class="striped">
                        <tbody>
                            <tr>
                                <td>Email:</td>
                                <td>{{ $variant->staff->user->email }}</td>
                            </tr>
                            <tr>
                                <td>Mobile:</td>
                                <td>{{ $variant->staff->user->phone_code }}&nbsp;{{ $variant->staff->user->mobile }}
                                </td>
                            </tr>
                            <tr>
                                <td>Designation:</td>
                                <td>
                                    {{ $variant->staff->designationRelation->name }}
                                </td>
                            </tr>

                            <tr>
                                <td>Address:</td>
                                <td>
                                    {{-- {{$variant->staff->user->}} --}}
                                </td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td class="users-view-verified">
                                    @if ($variant->staff->user->is_active == 1)
                                        <span class="chip lighten-5 green green-text">Active</span>
                                    @else
                                        <span class="chip lighten-5 red red-text">In-active</span>
                                    @endif
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <div class="col s12 mb-3 mt-2">
                    <hr class="mb-2">
                    <h6 class="mb-2">Documents</h6>

                    <div class='row'>
            
                        @if ($variant->staff->documents)
                                           @foreach ($variant->staff->documents as $document)
                                           <div class='col s12 m6 l6 xl2 mb-2'>
                                           <div class="d-flex align-items-center document_box_img"> 
                                            <img src="{{ asset('storage/store/users/documents/' . $document->name) }}"
                                                   width="50" height="50" class="enlarge-image"
                                                   data-src="{{ asset('storage/store/users/documents/' . $document->name) }}"
                                                   alt="Document Thumbnail" />
                                                 <h6 class="ml-4">  <span > {{ $document->details }}</span></h6> 
                                               <br>
                                               </div>
                                               </div>
                                           @endforeach
                                       @endif
                                 
       </div>

                </div>
            </div>
        </div>
    </div>



    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ ucfirst($variant->staff->user->name) }} Work History Table</h4>
                    <div class="row">
                        <div class="col s12 data-table-container">
                            <div class="card-content">
                                <div class="row">

                                </div>
                            </div>
                            <table id="data-table-staff-data" class="display data-tables"
                                data-url="{{ route('staffWorkHistory', ['id' => $variant->staff->user_id]) }}"
                                data-form="dt-filter-form" data-length="10">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Date</th>
                                        <th>In - Times</th>
                                        <th>Out - Times</th>
                                        <th>Total Working Time</th>
                                        <th>Break Time</th>
                                        <th>Over Time</th>
                                    </tr>
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m12 l12">
            <div id="button-trigger" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ ucfirst($variant->staff->user->name) }} Service History Table</h4>
                    <div class="row">
                        <div class="col s12 data-table-container">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12">
                                        <form id="dt-filter-form" name="dt-filter-form">
                                            {{ csrf_field() }}
                                            {!! Form::hidden('staff_id', $$variant->staff->user->id ?? '', ['id' => 'staff_id']) !!}
                                            <div class="row">
                                                <div class="input-field col m4 s4">
                                                    <input type="search" class="" name="billing_code"
                                                        id="billing_code" placeholder="Search Invoice"
                                                        aria-controls="DataTables_Table_0">
                                                </div>
                                                <div class="input-field col m s4">
                                                    <select id="customer_name" name="customer_name">
                                                        <option value="" disabled selected>Select Customer
                                                        </option>
                                                        @foreach ($variant->customers as $customer)
                                                            <option value="{{ $customer->id }}">{{ $customer->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="input-field col m4 s4">
                                                    <select id="room_name" name="room_name">
                                                        <option value="" disabled selected>Select Room</option>
                                                        @foreach ($variant->rooms as $room)
                                                            <option value="{{ $room->id }}">{{ $room->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <table id="data-table-staff-service-data" class="display data-tables"
                                data-url="{{ route('staffServiceHistory', ['id' => $variant->staff->user_id]) }}"
                                data-form="dt-filter-form" data-length="10">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Room</th>
                                        <th>Service Type</th>
                                        <th>Name</th>
                                        <th>Date & Time</th>
                                        <th>Break Time</th>
                                        <th>Over Time</th>
                                    </tr>
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<!-- Modal Structure -->
<div id="image-modal" class="image-modal" style="display:none;">
    <span id="modal-close" style="position:absolute;top:10px;right:10px;font-size:30px;cursor:pointer;">&times;</span>
    <img id="modal-image" src="" style="display:block;margin:auto;max-width:90%;max-height:90%;"
        alt="Enlarged Image" />
</div>


@endsection

{{-- vendor scripts --}}
@section('vendor-script')

@endsection

@push('page-scripts')
<!-- date-time-picker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all images with the 'enlarge-image' class
        document.querySelectorAll('.enlarge-image').forEach(function(image) {
            // Add click event listener to each image
            image.addEventListener('click', function() {
                // Get the modal and modal image elements
                var modal = document.getElementById('image-modal');
                var modalImage = document.getElementById('modal-image');

                // Set the modal image source to the clicked image's data-src
                modalImage.src = this.getAttribute('data-src');

                // Show the modal
                modal.style.display = 'block';
            });
        });

        // Close modal when the close button is clicked
        document.getElementById('modal-close').addEventListener('click', function() {
            var modal = document.getElementById('image-modal');
            modal.style.display = 'none';
        });

        // Close modal when clicking outside the image
        window.addEventListener('click', function(event) {
            var modal = document.getElementById('image-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>

<script>
    // Use Blade to directly construct the URL with staffId 
    $(document).ready(function() {
        var table = $('#data-table-staff-data').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $('#data-table-staff-data').data('url'),
                type: 'GET'
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'id'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'in_time',
                    name: 'in_time'
                },
                {
                    data: 'out_time',
                    name: 'out_time'
                },
                {
                    data: 'total_working_time',
                    name: 'total_working_time'
                },
                {
                    data: 'break_time',
                    name: 'break_time'
                },
                {
                    data: 'over_time',
                    name: 'over_time'
                },
            ],
            order: [
                [1, 'asc']
            ], // Order by date or any column you prefer
            lengthMenu: [10, 25, 50, 75, 100],
            pageLength: 10,
        });
    });
    $(document).ready(function() {
        var table = $('#data-table-staff-service-data').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $('#data-table-staff-service-data').data('url'),
                type: 'GET',
                data: function(d) {
                    // Append additional filter data from the form
                    d.billing_code = $('#billing_code').val();
                    d.customer_name = $('#customer_name')
                        .val(); // Example for filtering by customer name
                    d.room_name = $('#room_name').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'id',
                    orderable: false,
                    searchable: false
                }, // For row indexing
                {
                    data: 'invoice',
                    name: 'invoice'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'room',
                    name: 'room'
                },
                {
                    data: 'service_type',
                    name: 'service_type'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'datetime',
                    name: 'datetime'
                },
                {
                    data: 'break_time',
                    name: 'break_time'
                },
                {
                    data: 'over_time',
                    name: 'over_time'
                }
            ],
            order: [
                [2, 'asc']
            ], // Order by date by default
            lengthMenu: [10, 25, 50, 100],
            pageLength: 10
        });
        $('#billing_code').on('keyup', function() {
            table.draw(); // Reload the DataTable with the new search criteria
        });
        $('#room_name, #customer_name').on('change', function() {
            table.draw(); // Reload the DataTable with the new search criteria
        });
    });
    $(document).ready(function() {
        var staffId = {{ $variant->staff->user->id }}; // Ensure staffId is available in Blade
        var url = '{{ route('getDateRangeStaffDetails', ['id' => $variant->staff->user->id]) }}';
        $('#staff_date_range').daterangepicker({
            // Specify your date range picker options here
        });

        // Event listener for change in date range
        $('#staff_date_range').on('apply.daterangepicker', function(ev, picker) {
            var fromDate = picker.startDate.format('YYYY-MM-DD');
            var toDate = picker.endDate.format('YYYY-MM-DD');
            $.ajax({
                url: url,
                method: 'GET',
                data: {
                    toDate: toDate,
                    fromDate: fromDate
                },
                success: function(response) {
                    if (response.flagError == false) {
                        console.log(response.data);
                        $("#incentive").text(response.data.incentive);
                        $("#noOfLeaves").text(response.data.noOfLeaves);
                        $("#noOfServices").text(response.data.noOfService);
                        $("#noOfWorkingDays").text(response.data.noOfWorkingDays);
                    } else {

                    }
                },
                error: function(err) {
                    console.error('Error fetching data: ' + err);
                }
            });
        });
    });
</script>
@endpush
