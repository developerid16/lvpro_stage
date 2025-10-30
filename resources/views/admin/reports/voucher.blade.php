@extends('layouts.master-layouts')

@section('title') Customer Report @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Customer Report @endslot
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Customer Report</h4>

    </div>
    <form action="">
        <div class="card-body pt-0">
            <div class="col-lg-6">
                <div>
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
            </div>
            <div class="col-lg-6">
                <div>
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button class="btn btn-primary">Search</button>
        </div>
    </form>
</div>

@if (count($data) > 0)

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Data</h4>

    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-show-export="true" data-pagination="true" data-search="false" data-show-columns="false" data-show-toggle="false" data-show-export="true" data-filter-control="false" data-show-columns-toggle-all="false" data-export-types="['csv']">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>Customer Type</th>
                        <th>Aphid/Employment Id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile Number</th>
                        <th>Status</th>
                        <th>Date Of Birth</th>
                        <th>Gender</th>
                        <th>Aph Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)


                    <tr>
                        <td>
                            {{$loop->iteration}}
                        </td>
                        <td>
                            {{$item->user_type}}
                        </td>
                        <td>
                            {{$item->unique_id}}
                        </td>
                        <td>
                            {{$item->name}}
                        </td>
                        <td>
                            {{$item->email}}
                        </td>
                        <td>
                            {{$item->country_code . ' ' . $item->phone_number}}
                        </td>
                        <td>
                            {{$item->status}}
                        </td>
                        <td>
                            {{$item->date_of_birth->format(config('shilla.date-format'))}}
                        </td>
                        <td>
                            {{$item->gender}}
                        </td>
                        <td>
                            {{$item->expiry_date ? $item->expiry_date->format(config('shilla.date-format')) : ''}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
 

@section('script')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" integrity="sha512-ZnR2wlLbSbr8/c9AgLg3jQPAattCUImNsae6NHYnS9KrIwRdcY9DxFotXhNAKIKbAXlRnujIqUWoXXwqyFOeIQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

@endsection