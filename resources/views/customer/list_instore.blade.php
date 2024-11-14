@extends('layouts.app')

{{-- page title --}}
@section('seo_title', Str::plural($page->title) ?? '')
{{-- @section('search-title') {{ $page->title ?? '' }} @endsection --}}

{{-- vendor styles --}}
@section('vendor-style')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')

@section('breadcrumb')
    <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ Str::plural($page->title) ?? '' }}</span></h5>
    <ol class="breadcrumbs mb-0">
        <li class="breadcrumb-item"><a href="{{ url(ROUTE_PREFIX . '/home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ $page->link }}">{{ Str::plural($page->title) ?? '' }}</a></li>
        <li class="breadcrumb-item active">in-store Credit Used List</li>
    </ol>
@endsection



<div class="section">

    <!--Basic Form-->
    <div class="row">
        <!-- Form Advance -->
        <div class="col s12 m12 l12">
            <div id="Form-advance" class="card card card-default scrollspy">
                <div class="card-content">
                    <h4 class="card-title">{{ $page->title ?? '' }} In-store Credit History</h4>
                    <div class="m10">
                      @if($customerInstoreCreditLists->count() > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="w-5">#</th>
                                    <th>Invoice</th>
                                    <th>Credit Used</th>
                                    <th>Balance Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalDeducted = 0;
                                $over_paid=0; 
                                @endphp
                              
                                @foreach ($customerInstoreCreditLists as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>                                            
                                            @if($item->is_cron==0 && $item->refundCash)
                                            <a href="{{ url(ROUTE_PREFIX . '/cancel-bill/' . $item->refundCash->id) }}">{{ $item->bill->billing_code ?? '' }}</a>
                                            @elseif($item->bill && !$item->bill->trashed())
                                            <a href="{{ url(ROUTE_PREFIX . '/billings/' . $item->bill->id) }}">{{ $item->bill->billing_code ?? '' }}</a>
                                            @else 
                                            <span>Deducted through Instore Balance</span> 
                                            @endif
                                        </td>
                                        <td>{{ number_format($item->deducted_over_paid ?? 0, 2) }}</td>
                                        <td>{{ number_format($item->over_paid ?? 0, 2) }}</td>
                                    </tr>
                                    @php

                                        $over_paid = $item->over_paid ?? 0;
                                        $totalDeducted += $item->deducted_over_paid ?? 0;
                                    @endphp
                                @endforeach
                              
                            </tbody>
                        </table>
                        @elseif($instoreCreditPaid->count()>0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="w-5">#</th>
                                    <th>Credit </th>
                                    <th>Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($instoreCreditPaid  as $instore)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$instore->over_paid ?? '0'}}</td>
                                    <td>Instore Added by Admin</td>
                                </tr>
                                @endforeach
                            </tbody>
                          </table>
                      @else
                      <table class="table table-bordered">
                        <tr>
                          <td>No Data Found</td>
                        </tr>
                      </table>
                      @endif

                    </div>
                    <div>
                      @if($customerInstoreCreditLists->count() > 0)
                      <table style="border-color: aliceblue;">
                        <tbody>
                          <tr>
                                  <td colspan="2" style="text-align: right">Total Deductions</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>Total In-store Credit</td>
                                    <td>{{ number_format($over_paid, 2) }}</td>

                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>Used In-store Credit</td>
                                    <td>{{ number_format($totalDeducted, 2) }}</td>

                                </tr>
                                <tr>
                                    <td ></td>
                                    <td></td>
                                    <td>In-store Credit Balance</td>
                                    <td>{{ number_format($over_paid - $totalDeducted, 2) }}</td>

                                </tr>
                        </tbody>
                      </table>
                      @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- vendor scripts --}}
@section('vendor-script')
@endsection

@push('page-scripts')
<script></script>

@endpush
