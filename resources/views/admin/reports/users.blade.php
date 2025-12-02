@extends('layouts.master-layouts')

@section('title') Customer Report @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Customer Report @endslot
@endcomponent



<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Customer Report</h4>

    </div>--}}
    <form action="">
        <div class="card-body">
            <div class="row">

                <div class="col-lg-4">
                    <div class="sh_dec">
                        <label class="sh_dec form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control start_date" value="{{$sd ?? ''}}" max="{{date('Y-m-d')}}">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sh_dec">
                        <label class="sh_dec form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control end_date" value="{{$ed ?? ''}}" max="{{date('Y-m-d')}}">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div>
                        <label class="sh_dec form-label">Status</label>
                        <select name="status" class="sh_dec form-control">
                            <option class="sh_dec" value="All" @selected($selected==='All' )>All</option>
                            <option class="sh_dec" value="Active" @selected($selected==='Active' )>Active</option>
                            <option class="sh_dec" value="Inactive" @selected($selected==='Inactive' )>Inactive</option>
                            <option class="sh_dec" value="Blacklist" @selected($selected==='Blacklist' )>Blacklist
                            </option>
                            <option class="sh_dec" value="Expired" @selected($selected==='Expired' )>Expired</option>
                            <option class="sh_dec" value="Awaiting Activation" @selected($selected==='Awaiting Activation' )>Awaiting
                                Activation</option>
                            {{-- 'Active','Inactive','Blacklist','Expired','Awaiting Activation' --}}
                        </select>
                    </div>
                </div> 
            </div>

        </div> 
        <div class="card-footer ">
            <button class="sh_btn btn btn-primary" sty>Search</button>

           


        </div>
    </form>
</div>

@if (count($data) > 0)

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Data</h4>
        <div>
            <a class="btn-sm btn btn-info " href="{{url('admin/download-report-file')}}"  target="_blank" >Download Full Data </a>
             
            <a class="btn btn-sm btn-primary"  style="{{!request()->query() ? 'pointer-events: none;' : '' }}"   href="{{url('admin/report/customer-download')  . '?' . http_build_query(request()->query())  }}">Download Filtered Data</a>
           

        </div>

    </div>
    <div class="card-body pt-0">
        {{ $data->links() }}

        <div class=" table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>Customer Type</th>
                        <th>Aphid</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile Number</th>
                        <th>Status</th>
                        <th>Date Of Birth</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Aph Expiry Date</th>
                        <th>Sign Up Date</th>
                        <th>Last Login</th>

                        <th>Referral's Name</th>
                        <th>Referral's APH</th>
                        <th>Referral date</th>
                        <th>Referral status</th>


                        <th>Keys Spending(Total)</th>
                        <th>Keys Spending(last 3 Month)</th>
                        <th>Keys Spending(last 6 Month)</th>
                        <th>Last transaction date</th>
                        <th>No. of Keys earned (Past Cycle  {{$pd}})</th>
                        <th>No. of Keys earned (Current Cycle {{$cd}})</th>
                        <th>No. of Keys available (Current Cycle) {{$cd}}</th>

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
                            {{isset($item->date_of_birth) ? $item->date_of_birth->format(config('shilla.date-format')) : '' }}
                        </td>
                        <td>
                            {{$item->gender}}
                        </td>
                        <td>
                            {{$item->date_of_birth ? $item->date_of_birth->age : 'NA'}}
                        </td>
                        <td>
                            {{$item->expiry_date ? $item->expiry_date->format(config('shilla.date-format')) : ''}}
                        </td>
                        <td>
                            {{$item->created_at->format(config('shilla.date-format'))}}
                        </td>
                        <td>
                            {{$item->last_login ? $item->last_login->format(config('shilla.date-format')) : ''}}
                        </td>


                        <td>{{ ($item->referral && $item->referral->byuser) ? $item->referral->byuser->name : 'ND' }}</td>
                        <td>{{ ($item->referral && $item->referral->byuser) ? $item->referral->byuser->unique_id : 'ND' }}</td>
                        <td>{{ $item->referral ? $item->referral->created_at->format(config('shilla.date-format')) : 'ND' }}</td>
                        <td>{{ $item->referral ? $item->referral->status : 'ND' }}</td>




                        <td>
                            {{$item->total}}
                        </td>
                        <td>
                            {{$item->last3month}}
                        </td>
                        <td>
                            {{$item->last6month}}
                        </td>
                        <td>
                            {{$item->lastTransaction}}
                        </td>
                        <td>
                            {{$item->pastCycle}}
                        </td>

                        <td>
                            {{$item->currantCycle}}
                        </td>
                        <td>
                            {{$item->remainKeys}}
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $data->links() }}

    </div>
</div>
@endif


@if (count($data) === 0 &&isset($selected))
<p class="text-center ">No data found</p>
@endif

<!-- Create -->

<!-- end modal -->
@endsection

@section('script')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" integrity="sha512-ZnR2wlLbSbr8/c9AgLg3jQPAattCUImNsae6NHYnS9KrIwRdcY9DxFotXhNAKIKbAXlRnujIqUWoXXwqyFOeIQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script>



</script>
@endsection