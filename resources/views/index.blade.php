@extends('layouts.master-layouts')

@section('title') @lang('translation.Dashboards') @endsection

@section('content')

@component('components.breadcrumb')
@slot('li_1') Dashboard @endslot
@slot('title') Dashboard @endslot
@endcomponent

<div class="row">

    <!-- Voucher Issuance Trend -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Voucher Issuance Trend</h4>
                <div class="col-md-3">
                    <select id="trendType" class="form-control">
                        <option value="week" selected>Week</option>
                        <option value="month">Month</option>
                        <option value="year">Year</option>
                    </select>
                </div>

            </div>
            <div class="card-body">
                <div id="voucherIssuanceChart"></div>
            </div>
        </div>
    </div>

    <!-- Voucher Redeem Trend -->
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

    <!-- Redemption by Outlet -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h4 class="mb-0">Outlet Performance</h4></div>
            <div class="card-body">
                <div id="outletPerformanceChart"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Voucher Redemption Rate Trend</h4>

                <select id="rateTrendType" class="form-control w-auto">
                    <option value="week">Week</option>
                    <option value="month" selected>Month</option>
                </select>

            </div>

            <div class="card-body">
                <div id="redemptionRateTrendChart"></div>
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
{{-- <script src="{{ URL::asset('build/js/pages/dashboard.init.js') }}"></script> --}}

<script>
    function scroll_div(div_class) {
        var height = $(div_class).offset().top - 140;
        $('html, body').animate({
            scrollTop: height
        }, 'slow');
    }
</script>
<script>

   

    var issuanceChart;
    var redeemChart;

    function loadCharts(type='week')
    {
        $.get("{{ url('admin/voucher-trend-data') }}",{type:type},function(res){

            if(issuanceChart){

                issuanceChart.updateOptions({
                    xaxis:{categories:res.labels},
                    series:[{data:res.issuance}]
                });

                redeemChart.updateOptions({
                    xaxis:{categories:res.labels},
                    series:[{data:res.redeem}]
                });

                return;
            }

            issuanceChart = new ApexCharts(
                document.querySelector("#voucherIssuanceChart"),
                {
                    chart:{type:'area',height:350},
                    series:[{name:'Issued',data:res.issuance}],
                    xaxis:{categories:res.labels}
                }
            );

            issuanceChart.render();


            redeemChart = new ApexCharts(
                document.querySelector("#voucherRedeemChart"),
                {
                    chart:{type:'line',height:350},
                    series:[{name:'Redeemed',data:res.redeem}],
                    xaxis:{categories:res.labels}
                }
            );

            redeemChart.render();

        });
    }


    
    var outletCountChart = null;
    var outletValueChart = null;
    var outletPerformanceChart = null;

    function loadOutletCharts()
    {
        $.get("{{ url('admin/outlet-redemption-data') }}", function(res){

            var fullLabels = res.labels;

            var shortLabels = res.labels.map(function(name){
                return name.length > 8 ? name.substring(0,10) + '…' : name;
            });

            function tooltipFormatter(seriesIndex, dataPointIndex){
                return fullLabels[dataPointIndex];
            }

            /* ---------------- Chart 1 ---------------- */

            if(!outletCountChart){

                outletCountChart = new ApexCharts(
                    document.querySelector("#outletCountChart"),
                    {
                        chart:{type:'bar',height:320},
                        series:[{
                            name:'Redemption Count',
                            data:res.count
                        }],
                        xaxis:{categories:shortLabels},
                        tooltip:{
                            custom: function({series, seriesIndex, dataPointIndex, w}) {
                                return '<div class="apex-tooltip">'+
                                    fullLabels[dataPointIndex] +
                                    '<br>Count: '+series[seriesIndex][dataPointIndex]+
                                    '</div>';
                            }
                        }
                    }
                );

                outletCountChart.render();

            }else{

                outletCountChart.updateOptions({
                    series:[{data:res.count}],
                    xaxis:{categories:shortLabels}
                });

            }


            /* ---------------- Chart 2 ---------------- */

            if(!outletValueChart){

                outletValueChart = new ApexCharts(
                    document.querySelector("#outletValueChart"),
                    {
                        chart:{type:'bar',height:320},
                        colors:['#34c38f'],
                        series:[{
                            name:'Redemption Value',
                            data:res.value
                        }],
                        xaxis:{categories:shortLabels},
                        tooltip:{
                            custom: function({series, seriesIndex, dataPointIndex}) {
                                return '<div class="apex-tooltip">'+
                                    fullLabels[dataPointIndex] +
                                    '<br>Value: '+series[seriesIndex][dataPointIndex]+
                                    '</div>';
                            }
                        }
                    }
                );

                outletValueChart.render();

            }else{

                outletValueChart.updateOptions({
                    series:[{data:res.value}],
                    xaxis:{categories:shortLabels}
                });

            }


            /* ---------------- Chart 3 ---------------- */

            if(!outletPerformanceChart){

                outletPerformanceChart = new ApexCharts(
                    document.querySelector("#outletPerformanceChart"),
                    {
                        chart:{type:'bar',height:320},
                        plotOptions:{bar:{columnWidth:'45%'}},
                        colors:['#556ee6','#34c38f'],
                        series:[
                            {name:'Redemption Count',data:res.count},
                            {name:'Redemption Value',data:res.value}
                        ],
                        xaxis:{categories:shortLabels},
                        legend:{position:'top'},
                        tooltip:{
                            custom: function({series, seriesIndex, dataPointIndex}) {
                                return '<div class="apex-tooltip">'+
                                    fullLabels[dataPointIndex] +
                                    '<br>'+series[seriesIndex][dataPointIndex]+
                                    '</div>';
                            }
                        }
                    }
                );

                outletPerformanceChart.render();

            }else{

                outletPerformanceChart.updateOptions({
                    series:[
                        {name:'Redemption Count',data:res.count},
                        {name:'Redemption Value',data:res.value}
                    ],
                    xaxis:{categories:shortLabels}
                });

            }

        });
    }

    var redemptionRateTrendChart = null;

    function loadRedemptionRateTrend(type='month')
    {
        $.get("{{ url('admin/redemption-rate-trend-data') }}",{type:type},function(res){

            if(!redemptionRateTrendChart){

                redemptionRateTrendChart = new ApexCharts(
                    document.querySelector("#redemptionRateTrendChart"),
                    {
                        chart:{type:'line',height:350},
                        stroke:{width:[3,3,3]},
                        colors:['#556ee6','#34c38f','#f46a6a'],
                        series:[
                            {name:'Issued',data:res.issued},
                            {name:'Redeemed',data:res.redeemed},
                            {name:'Redemption Rate %',data:res.rate}
                        ],
                        xaxis:{categories:res.labels},
                        yaxis:[
                            {title:{text:'Vouchers'}},
                            {
                                opposite:true,
                                title:{text:'Rate %'}
                            }
                        ],
                        tooltip:{
                            shared:true
                        }
                    }
                );

                redemptionRateTrendChart.render();

            }else{

                redemptionRateTrendChart.updateOptions({
                    series:[
                        {name:'Issued',data:res.issued},
                        {name:'Redeemed',data:res.redeemed},
                        {name:'Redemption Rate %',data:res.rate}
                    ],
                    xaxis:{categories:res.labels}
                });

            }

        });
    }


    $(document).ready(function(){
        
        loadCharts();
        loadOutletCharts();
        loadRedemptionRateTrend();
        
        $('#trendType').change(function(){
            loadCharts($(this).val());
        });
        
        $('#rateTrendType').change(function(){
            loadRedemptionRateTrend($(this).val());
        });
    });

   

</script>

@endsection