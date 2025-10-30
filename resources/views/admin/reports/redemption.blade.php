@extends('layouts.master-layouts')

@section('title') Redemption Report @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Redemption Report @endslot
@endcomponent



<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Redemption Report</h4>

    </div>--}}
    <form action="">
        <div class="card-body">
            <div class="row">

                <div class="col-lg-4">
                    <div class="sh_dec">
                        <label class="sh_dec form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control start_date" value="{{$sd}}" max="{{date('Y-m-d')}}">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sh_dec">
                        <label class="sh_dec form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control end_date" value="{{$ed}}" max="{{date('Y-m-d')}}">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div>
                        <label class="sh_dec form-label">Status</label>
                        <select name="status" class="sh_dec form-control">
                            <option value="">All</option>
                            <option class="sh_dec" value="Purchased" @selected($status==='Purchased' )>Issuance</option>
                            <option class="sh_dec" value="Redeemed" @selected($status==='Redeemed' )>Redeemed</option>
                            <option class="sh_dec" value="Expired" @selected($status==='Expired' )>Expired</option>
                            <option class="sh_dec" value="Decline" @selected($status==='Decline' )>Decline</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button class="sh_btn btn btn-primary">Search</button>
        </div>
    </form>
</div>

@if (count($data) > 0)

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Data</h4>

    </div>
    <div class="card-body pt-0 sh_table_btn">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-show-export="true" data-pagination="true" data-search="false" data-show-columns="false" data-show-toggle="false" data-show-export="true" data-filter-control="false" data-show-columns-toggle-all="false" data-export-types="['csv']" data-export-data-type="all">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>APHID</th>
                        <th>Name</th>
                        <th>Reward</th>
                        <th>Key Use</th>
                        <th>Issuance Date</th>
                        <th>Voucher Validity </th>
                        <th>Redeemed Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)


                    <tr>
                        <td>
                            {{$loop->iteration}}
                        </td>
                        <td>
                            {{$item->user->unique_id}}
                        </td>
                        <td data-escape="true">
                            {{$item->user->name}}
                        </td>
                        <td data-escape="true">
                            {{$item->reward->code . ' '.$item->reward->name}}
                        </td>
                        <td>
                            {{number_format($item->key_use)}}
                        </td>
                        <td>
                            {{$item->created_at->format(config('shilla.date-format'))}}
                        </td>
                        <td>
                           
                            {{$item->expiry_date->format(config('shilla.date-format'))}}
                            
                        </td>
                        <td>
                        @if($item->status === 'Redeemed')

                        {{$item->updated_at->format(config('shilla.date-format'))}}
                        @else
                        -
                        @endif

                        </td>
                        <td>
                            {{$item->status === 'Purchased' ? 'Issuance' : $item->status}}
                        </td>


                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if (count($data) === 0 &&isset($sd))
<p class="text-center ">No data found</p>
@endif


<!-- Create -->

<!-- end modal -->
@endsection

@section('script')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" integrity="sha512-ZnR2wlLbSbr8/c9AgLg3jQPAattCUImNsae6NHYnS9KrIwRdcY9DxFotXhNAKIKbAXlRnujIqUWoXXwqyFOeIQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

@endsection