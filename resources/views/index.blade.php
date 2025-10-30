@extends('layouts.master-layouts')

@section('title') @lang('translation.Dashboards') @endsection

@section('content')

@component('components.breadcrumb')
@slot('li_1') Dashboard @endslot
@slot('title') Dashboard @endslot
@endcomponent

<div>
    <ul class="dashboard_cloud">
        <li onclick="scroll_div('#week_chart_s')"><a href="javascript:void(0)">Week Chart</a></li>
        <li onclick="scroll_div('#gender_breakdown_s')"><a href="javascript:void(0)">Gender Breakdown</a></li>
        <li onclick="scroll_div('#total_sales_s')"><a href="javascript:void(0)">Total Sales Years Last 12 Month</a></li>
        <li onclick="scroll_div('#total_signup_s')"><a href="javascript:void(0)">Total Signup Years Last 12 Month</a>
        </li>
        <li onclick="scroll_div('#redmptions_s')"><a href="javascript:void(0)">Top 5 Redemptions (last 3 months)</a>
        </li>
        <li onclick="scroll_div('#sale_refunds_s')"><a href="javascript:void(0)">Top 5 Customers with Most Sales Refunds</a></li>
        <li onclick="scroll_div('#most_keys_s')"><a href="javascript:void(0)">Top 10 Customers with Most Keys</a></li>
        <li onclick="scroll_div('#rewards_s')"><a href="javascript:void(0)">Rewards</a></li>
        <li onclick="scroll_div('#keys_state_s')"><a href="javascript:void(0)">Keys State (By last 2 Cycle)</a></li>
        <li onclick="scroll_div('#keys_state_s')"><a href="javascript:void(0)">Redemptions status based on Reward type & status</a></li>
        <li onclick="scroll_div('#age_group_s')"><a href="javascript:void(0)">Age Group</a></li>
        <li onclick="scroll_div('#top-sku')"><a href="javascript:void(0)">Top 5 SKU</a></li>
        <li onclick="scroll_div('#top-brand')"><a href="javascript:void(0)">Top 5 Brand SKU</a></li>
        <li onclick="scroll_div('#top-sales')"><a href="javascript:void(0)">Top 5 Locations </a></li>
    </ul>
</div>
<div class="row">
    <div class="col-xl-4">
        <div class="card overflow-hidden">
            <div class="bg-primary bg-soft">
                <div class="row">
                    <div class="col-7">
                        <div class="text-primary p-3">
                            <h5 class="text-primary sh_sub_title nowrap">Welcome Back !</h5>
                            <p class="sh_dec"> Your Best Dashboard</p>
                        </div>
                    </div>
                    <div class="col-5 align-self-end">
                        <img src="{{ URL::asset('/build/images/profile-img.png') }}" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="avatar-md profile-user-wid mb-4">
                            <img src="{{ isset(Auth::user()->avatar) ? asset(Auth::user()->avatar) : asset('/build/images/users/avatar-1.jpg') }}" alt="" class="img-thumbnail rounded-circle">
                        </div>
                        <h5 class="font-size-15 text-truncate sh_dec_b">{{ Str::ucfirst(Auth::user()->name) }}</h5>

                    </div>

                    <div class="col-sm-8">
                        <div class="pt-4">

                            <div class="row">
                                <div class="col-4">
                                    <h5 class="font-size-15 sh_dec_b">{{ number_format($master['active_user'])}}</h5>
                                    <p class="text-muted mb-0 sh_dec">Active Customer</p>
                                </div>
                                <div class="col-4">
                                    <h5 class="font-size-15 sh_dec_b">{{number_format($master['active_reward'])}}</h5>
                                    <p class="text-muted mb-0 sh_dec">Active Reward</p>
                                </div>
                                <div class="col-4">
                                    <h5 class="font-size-15 sh_dec_b">{{number_format($master['total_transaction'])}}
                                    </h5>
                                    <p class="text-muted mb-0 sh_dec">Total Transaction</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" id="gender_breakdown_s">
            <div class="card-body">

                <h4 class="card-title sh_sub_title">Gender Breakdown</h4>


                <!-- Nav tabs -->
                <ul class="nav nav-pills nav-justified tab_toggle" role="tablist">
                    <li class="nav-item waves-effect waves-light" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#home-1" role="tab" aria-selected="true">
                            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                            <span class="d-none d-sm-block sh_dec">Total Active (Last 3 Months)</span>
                        </a>
                    </li>
                    <li class="nav-item waves-effect waves-light" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#profile-1" role="tab" aria-selected="false">
                            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                            <span class="d-none d-sm-block sh_dec"> Total Overall </span>
                        </a>
                    </li>

                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-3 text-muted">
                    <div class="tab-pane active show" id="home-1" role="tabpanel">
                        <div id="active-gender" data-colors='["--bs-primary", "--bs-warning", "--bs-success"]' class="apex-charts" dir="ltr">
                        </div>
                    </div>
                    <div class="tab-pane" id="profile-1" role="tabpanel">
                        <div id="overall-gender" data-colors='["--bs-primary", "--bs-warning", "--bs-success"]' class="apex-charts" dir="ltr">
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
    <div class="col-xl-8">
        <div class="row">
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium sh_dec min-htitle">Total Customer</p>
                                <h4 class="mb-0 sh_sub_title">{{ number_format($master['total_user'])}}</h4>
                            </div>

                            <div class="flex-shrink-0 align-self-center ">
                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <i class="bx bx-archive-in font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium sh_dec min-htitle">Total Voucher Issued</p>
                                <h4 class="mb-0 sh_sub_title">{{ number_format($master['total_buy_reward'])}}</h4>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                    <span class="avatar-title">
                                        <i class="bx bx-copy-alt font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium sh_dec min-htitle">Total Voucher Redeemed</p>
                                <h4 class="mb-0 sh_sub_title">{{ number_format($master['total_redeemed_reward'])}}</h4>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <i class="bx bx-purchase-tag-alt font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <div class="row">
            <div class="col-12">
                <div class="card" id="week_chart_user">
                    <div class="card-body">
                        <div class="d-sm-flex flex-wrap">
                            <h4 class="card-title mb-4 sh_sub_title">Week Chart Customer</h4>

                        </div>

                        <div id="stacked-column-chart-user" data-colors='["--bs-primary", "--bs-warning", "--bs-success"]' class="apex-charts" dir="ltr"></div>
                    </div>
                </div>
            </div>

        </div>


    </div>
</div>
<div class="row">
    <div class="col-6">
        <div class="card" id="week_chart_s">
            <div class="card-body">
                <div class="d-sm-flex flex-wrap">
                    <h4 class="card-title mb-4 sh_sub_title">Week Chart By Total Sales</h4>

                </div>

                <div id="stacked-column-chart-sale-week" data-colors='["--bs-primary", "--bs-warning", "--bs-success"]' class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card" id="week_chart_s">
            <div class="card-body">
                <div class="d-sm-flex flex-wrap">
                    <h4 class="card-title mb-4 sh_sub_title">Week Chart Redemption / Complete Redemption</h4>

                </div>

                <div id="stacked-column-chart" data-colors='["--bs-primary", "--bs-warning", "--bs-success"]' class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->
<div class="row">
    <div class="col-md-6 col-sm-12" id="total_sales_s">
        <div class="card">
            <div class="card-body">
                <div class="d-sm-flex flex-wrap">
                    <h4 class="card-title mb-4 sh_sub_title">Total Sales Years Last 12 Month</h4>
                    <div class="ms-auto">

                    </div>
                </div>

                <div id="stacked-column-chart-sales" data-colors='["--bs-primary", "--bs-warning", "--bs-success"]' class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12" id="total_signup_s">
        <div class="card">
            <div class="card-body">
                <div class="d-sm-flex flex-wrap">
                    <h4 class="card-title mb-4 sh_sub_title">Total Signup Years Last 12 Month</h4>
                    <div class="ms-auto">

                    </div>
                </div>

                <div id="stacked-column-chart-signup" data-colors='["--bs-warning", "--bs-success"]' class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>

<!-- end row -->

<div class="row">
    <div class="col-lg-6" id="redmptions_s">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 sh_sub_title">Top 5 Redemptions (last 3 months)</h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>

                                <th class="align-middle">Rank</th>
                                <th class="align-middle">Rewards</th>
                                <th class="align-middle">Redemption</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['top5Redemption'] as $item)


                            <tr>

                                <td>{{$loop->iteration}}</td>

                                <td>{{$item->reward->name}}</td>
                                <td>
                                    {{number_format($item->count)}}
                                </td>

                            </tr>
                            @endforeach



                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6" id="sale_refunds_s">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 sh_sub_title">Top 5 Customers with Most Sales Refunds: </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>

                                <th class="align-middle">Rank</th>
                                <th class="align-middle">Customer</th>

                                <th class="align-middle">Total Refunds</th>
                                <th class="align-middle">Total Amount</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['top5Refund'] as $item)


                            <tr>

                                <td>{{$loop->iteration}}</td>

                                <td>{{$item->user->name ?? 'No Data'}}</td>
                                <td>
                                    {{$item->count}}
                                </td>
                                <td>

                                    ${{number_format(abs($item->tsl), 2, '.', ',')}}

                                </td>

                            </tr>
                            @endforeach




                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6" id="most_keys_s">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 sh_sub_title">Top 10 Customers with Most Keys {{$master['allCycle'][0]['cycle']}}</h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">Rank</th>
                                <th class="align-middle">Customer</th>
                                 <th class="align-middle">Total Keys</th>

                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $p = 1;
                            @endphp
                            @foreach ($top10keyearn as $user)

                            <tr>

                                <td>{{$loop->iteration}}</td>

                                <td>{{$user->user->name}}</td>

                                 <td> {{number_format($user->tk)}} </td>



                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6" id="rewards_s">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center"> Rewards
                    <a class="btn btn-primary btn-sm sh_btn" href="{{url('admin/reward')}}">View More</a>
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">Rank</th>
                                <th class="align-middle">Rewards</th>

                                <th class="align-middle">Balance</th>
                                <th class="align-middle">Total</th>
                                <th class="align-middle">Issuance</th>
                                <th class="align-middle">Redeemed</th>


                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['lowstock'] as $item)

                            <tr>

                                <td>{{$loop->iteration}}</td>

                                <td>
                                    @if ($item->status !== 'Active')
                                    <del>
                                        @endif
                                        {{$item->name}}
                                        @if ($item->reward_type == 0) <span class="badge bg-primary">Cash</span> @else
                                        <span class="badge bg-secondary">Product</span> @endif
                                        @if ($item->status !== 'Active')
                                    </del>
                                    @endif
                                </td>

                                <td>
                                    {{$item['balance'] > 0 ? $item['balance'] : '0'}}
                                    @if ($item['balance'] < 50) <span class="badge bg-danger">Low Stock</span>
                                        @endif

                                </td>
                                <td>
                                    {{$item['quantity']}}
                                </td>
                                <td>
                                    {{$item['redeemed']}}
                                </td>
                                <td>
                                    {{$item['total_redeemed']}}
                                </td>



                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6" id="keys_state_s">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Keys State
                    (By last 2 Cycle)
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">Cycle</th>
                                <th class="align-middle">Total Keys Earned</th>
                                <th class="align-middle">Total Keys Redeem</th>

                                <th class="align-middle">Total Balance</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['allCycle'] as $item)

                            <tr>

                                <td>{{$item['cycle']}}</td>



                                <td>

                                    {{number_format($item['creditKey'])}}

                                </td>
                                <td>

                                    {{number_format($item['debitKey'])}}

                                </td>
                                <td>

                                    {{number_format($item['remain'])}}

                                </td>



                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Redemptions
                    status based on Reward type & status
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">Name</th>
                                <th class="align-middle">Balance</th>
                                <th class="align-middle">Total</th>
                                <th class="align-middle">Issuance</th>
                                <th class="align-middle">Redeemed</th>


                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['redemptionsStatus'] as $item)

                            <tr>

                                <td>{{$item['name']}}</td>



                                <td>

                                    {{number_format($item['Balance'])}}

                                </td>
                                <td>

                                    {{number_format($item['Total'])}}

                                </td>
                                <td>

                                    {{number_format($item['Issued'])}}

                                </td>
                                <td>

                                    {{number_format($item['Redeemed'])}}

                                </td>



                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6" id="top-sku">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Top 5 SKUs by Sales
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">SKU Name </th>
                                <th class="align-middle">Sales</th>



                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['salesSku'] as $item)

                            <tr>

                                <td>{{$item['brand']['product_name'] ?? 'No Data'}}</td>



                                <td>

                                    {{number_format($item['count'])}}

                                </td>




                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Top 5 SKUs by Transaction
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">SKU Name</th>
                                <th class="align-middle">Transaction</th>



                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['transactionSku'] as $item)

                            <tr>

                                <td>{{$item['brand']['product_name'] ?? 'No Data'}}</td>


                                <td>

                                    ${{number_format($item['total_amount'], 2, '.', ',')}}

                                </td>




                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6" id="top-brand">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Top 5 Brand SKUs by Sales
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">Brand Name</th>
                                <th class="align-middle">Sales</th>



                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['salesBrand'] as $item)

                            <tr>

                                <td>{{$item['brand']['brand_name'] ?? 'No Data'}}</td>



                                <td>

                                    {{number_format($item['count'])}}

                                </td>




                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Top 5 Brand SKUs by Transaction
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">Brand Name</th>
                                <th class="align-middle">Transaction</th>



                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['transactionBrand'] as $item)

                            <tr>

                                <td>{{$item['brand']['brand_name'] ?? 'No Data'}}</td>


                                <td>

                                    ${{number_format($item['total_amount'], 2, '.', ',')}}

                                </td>




                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6" id="top-sales">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Top 5 Location by Sales
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">Location Name</th>
                                <th class="align-middle">Sales</th>



                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['salesLocation'] as $item)

                            <tr>

                                <td>{{$item['location'] ?? 'No Data' }}</td>



                                <td>

                                    {{number_format($item['count'])}}

                                </td>




                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Top 5 Location by Transaction
                </h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0 sh_table">
                        <thead class="table-light">
                            <tr>


                                <th class="align-middle">Location Name</th>
                                <th class="align-middle">Transaction</th>



                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($master['transactionLocation'] as $item)

                            <tr>

                                <td>{{$item['location'] ?? 'No Data' }}</td>

                                <td>

                                    ${{number_format($item['total_amount'], 2, '.', ',')}}
                                </td>




                            </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
                <!-- end table-responsive -->
            </div>
        </div>
    </div>


</div>
<div class="col-12" id="age_group_s">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title mb-4 d-flex justify-content-between align-items-center sh_sub_title"> Age Group
            </h4>
            <div id="synced-charts">
                <div id="chart-line2"></div>
                <div id="chart-small"></div>
            </div>
            <!-- end table-responsive -->
        </div>
    </div>
</div>

<!-- end row -->
<!-- end row -->

<!-- end row -->


<!-- end row -->

<!-- Transaction Modal -->
<div class="modal fade transaction-detailModal" role="dialog" aria-labelledby="transaction-detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transaction-detailModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Product id: <span class="text-primary">#SK2540</span></p>
                <p class="mb-4">Billing Name: <span class="text-primary">Neal Matthews</span></p>

                <div class="table-responsive">
                    <table class="table align-middle table-nowrap sh_table">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Product Name</th>
                                <th scope="col">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <div>
                                        <img src="{{ URL::asset('/build/images/product/img-7.png') }}" alt="" class="avatar-sm">
                                    </div>
                                </th>
                                <td>
                                    <div>
                                        <h5 class="text-truncate font-size-14">Wireless Headphone (Black)</h5>
                                        <p class="text-muted mb-0">$ 225 x 1</p>
                                    </div>
                                </td>
                                <td>$ 255</td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <div>
                                        <img src="{{ URL::asset('/build/images/product/img-4.png') }}" alt="" class="avatar-sm">
                                    </div>
                                </th>
                                <td>
                                    <div>
                                        <h5 class="text-truncate font-size-14">Phone patterned cases</h5>
                                        <p class="text-muted mb-0">$ 145 x 1</p>
                                    </div>
                                </td>
                                <td>$ 145</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h6 class="m-0 text-right">Sub Total:</h6>
                                </td>
                                <td>
                                    $ 400
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h6 class="m-0 text-right">Shipping:</h6>
                                </td>
                                <td>
                                    Free
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h6 class="m-0 text-right">Total:</h6>
                                </td>
                                <td>
                                    $ 400
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->

<!-- subscribeModal -->
<div class="modal fade" id="subscribeModal" aria-labelledby="subscribeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar-md mx-auto mb-4">
                        <div class="avatar-title bg-light rounded-circle text-primary h1">
                            <i class="mdi mdi-email-open"></i>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-xl-10">
                            <h4 class="text-primary">Subscribe !</h4>
                            <p class="text-muted font-size-14 mb-4">Subscribe our newletter and get notification to stay
                                update.</p>

                            <div class="input-group bg-light rounded">
                                <input type="email" class="form-control bg-transparent border-0" placeholder="Enter Email address" aria-label="Recipient's username" aria-describedby="button-addon2">

                                <button class="btn btn-primary" type="button" id="button-addon2">
                                    <i class="bx bxs-paper-plane"></i>
                                </button>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->

@endsection
@section('script')
<!-- apexcharts -->
<script>
    var chartData = @json($chart);
    console.log("chartData", chartData)
</script>
<script src="{{ URL::asset('/build/libs/apexcharts/apexcharts.min.js') }}"></script>

<!-- dashboard init -->
<script src="{{ URL::asset('build/js/pages/dashboard.init.js') }}"></script>

<script>
    function scroll_div(div_class) {
        var height = $(div_class).offset().top - 140;
        $('html, body').animate({
            scrollTop: height
        }, 'slow');
    }
</script>

@endsection