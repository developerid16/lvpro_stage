@extends('layouts.master-layouts')

@section('title') @lang('translation.Dashboards') @endsection

@section('content')

@component('components.breadcrumb')
@slot('li_1') Dashboard @endslot
@slot('title') Dashboard @endslot
@endcomponent

<div class="row">

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Voucher Issuance Trend</h4>

                <form method="GET" action="{{ url('/') }}">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="week" {{ $type == 'week' ? 'selected' : '' }}>Week</option>
                        <option value="month" {{ $type == 'month' ? 'selected' : '' }}>Month</option>
                        <option value="year" {{ $type == 'year' ? 'selected' : '' }}>Year</option>
                    </select>
                </form>
            </div>
            <div class="card-body">
                <div id="voucherIssuanceChart"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Voucher Redeem Trend</h4>
            </div>
            <div class="card-body">
                <div id="voucherRedeemChart"></div>
            </div>
        </div>
    </div>

</div>
@endsection
@section('script')
<!-- apexcharts -->
<script>
</script>
<script src="{{ URL::asset('/build/libs/apexcharts/apexcharts.min.js') }}"></script>

<!-- dashboard init -->
{{-- <script src="{{ URL::asset('build/js/pages/dashboard.init.js') }}"></script> --}}

<script>
    function scroll_div(div_class) {
        var height = $(div_class).offset().top - 140;
        $('html, body').animate({
            scrollTop: height
        }, 'slow');
    }
</script><script src="{{ URL::asset('/build/libs/apexcharts/apexcharts.min.js') }}"></script>

<script>

var issuanceLabels = @json($issuanceLabels);
var issuanceValues = @json($issuanceValues);

var redeemLabels = @json($redeemLabels);
var redeemValues = @json($redeemValues);

/* Issuance Chart */

var issuanceOptions = {
    chart: {
        type: 'area',
        height: 350,
        toolbar: { show: false },
        zoom: { enabled: false }
    },
    colors: ['#556ee6'],
    dataLabels: { enabled: false },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    markers: {
        size: 5,
        hover: { size: 7 }
    },
    series: [{
        name: "Vouchers Issued",
        data: issuanceValues
    }],
    xaxis: {
        categories: issuanceLabels,
        title: { text: "Period" }
    },
    yaxis: {
        title: { text: "Total Issued" }
    },
    fill: {
        type: "gradient",
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100]
        }
    },
    tooltip: {
        y: {
            formatter: function(val) {
                return val + " vouchers";
            }
        }
    }
};

var issuanceChart = new ApexCharts(
    document.querySelector("#voucherIssuanceChart"),
    issuanceOptions
);

issuanceChart.render();


/* Redeem Chart */

var redeemOptions = {
    chart: {
        type: 'area',
        height: 350,
        toolbar: { show: false },
        zoom: { enabled: false }
    },
    colors: ['#34c38f'],
    dataLabels: { enabled: false },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    markers: {
        size: 5,
        hover: { size: 7 }
    },
    series: [{
        name: "Vouchers Redeemed",
        data: redeemValues
    }],
    xaxis: {
        categories: redeemLabels,
        title: { text: "Period" }
    },
    yaxis: {
        title: { text: "Total Redeemed" }
    },
    fill: {
        type: "gradient",
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100]
        }
    },
    tooltip: {
        y: {
            formatter: function(val) {
                return val + " vouchers";
            }
        }
    }
};

var redeemChart = new ApexCharts(
    document.querySelector("#voucherRedeemChart"),
    redeemOptions
);

redeemChart.render();

</script>
@endsection