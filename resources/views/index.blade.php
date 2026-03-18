@extends('layouts.master-layouts')

@section('title') @lang('translation.Dashboards') @endsection

@section('content')

@component('components.breadcrumb')
@slot('li_1') Dashboard @endslot
@slot('title') Dashboard @endslot
@endcomponent
<style>
    .apexcharts-toolbar{
        display: none !important;
    }
</style>
<div class="d-flex justify-content-end mb-3">
    <select id="rewardTypeFilter" class="form-control w-auto">
        <option value="">All</option>
        <option value="0">Treats & Deals</option>
        <option value="1">E-Voucher</option>
    </select>
</div>

<div class="row">
    <div id="evoucherSection" class="row">

        <!-- Voucher Issuance Trend -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Voucher Issuance Trend</h4>
                    <select id="trendType" class="form-control w-auto">
                        <option value="week" selected>Week</option>
                        <option value="month">Month</option>
                        <option value="year">Year</option>
                    </select>

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

        <!-- Campaign Performance Comparison -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Campaign Performance Comparison</h4></div>
                <div class="card-body">
                    <div id="campaignChart"></div>
                </div>
            </div>
        </div>

        <!---Voucher Redemption Rate Trend-->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Voucher Redemption Rate Trend</h4>

                    <select id="rateTrendType" class="form-control w-auto">
                        <option value="week" selected>Week</option>
                        <option value="month">Month</option>
                    </select>

                </div>

                <div class="card-body">
                    <div id="redemptionRateTrendChart"></div>
                </div>
            </div>
        </div>

        <!-- Voucher Issuance Method Distribution -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Voucher Issuance Method Distribution</h4>
                </div>
        
                <div class="card-body">
                    <div id="issuanceMethodChart"></div>
                </div>
            </div>
        </div>

        

    </div>

    <div id="treatsDealsSection" class="row">

        <!-- Voucher Issuance Method -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Category Performance Summary</h4>
                </div>
        
                <div class="card-body">
                    <div id="categoryPerformanceChart"></div>
                </div>
            </div>
        </div>

        <!-- Monthly Transactions Trend -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Monthly Transactions Trend</h4>
                </div>
        
                <div class="card-body">
                    <div id="monthlyTransactionsChart"></div>
                </div>
            </div>
        </div>  
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4  class="mb-0">Monthly Spending Trend</h4>
                </div>
    
                <div class="card-body">
                    <div id="topDealsChart"></div>
                </div>
            </div>
        </div>

        <!-- Monthly Transactions Trend -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Monthly Unique Member Participation</h4>
                    <select id="participationType" onchange="loadMemberParticipationChart()">
                        <option value="month">Month</option>
                        <option value="week" selected>Week</option>
                        <option value="year">Year</option>
                    </select>
                </div>

                <div class="card-body">
                    <div id="memberChart"></div>
                </div>

            </div>
        </div>

         <!-- Purchase Frequency Distribution -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Purchase Frequency Distribution</h4>
                </div>
        
                <div class="card-body">
                    <div id="purchaseFrequencyChart"></div>
                </div>
            </div>
        </div>
    
         <!-- Demographic Purchase Profiling -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Demographic Purchase Profiling</h4>
                    <select id="demographicType" class="form-control" style="width:200px">
                        <option value="age">Age Distribution</option>
                        <option value="gender">Gender Distribution</option>
                        <option value="region">Region Distribution</option>
                        <option value="marital">Marital Status</option>
                    </select>
                </div>

                <div class="card-body">
                    <div id="demographicChart"></div>
                </div>

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
    var outletCountChart = null;
    var outletValueChart = null;
    var outletPerformanceChart = null;
    var redemptionRateTrendChart = null;
    var categoryChart = null;
    var issuanceMethodChart = null;
    var monthlyTransactionsChart = null;
    var purchaseFrequencyChart = null;
    var demographicChart = null;
    var memberChart = null;
    var campaignChart = null;
    var topDealsChart = null;


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

    function loadRedemptionRateTrend(type='week')
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

    function loadCategoryPerformance(){

        $.get("{{ url('admin/category-performance-data') }}", function(res){

            var labels = [];
            var transactions = [];
            var members = [];
            var sets = [];
            var revenue = [];

            res.forEach(function(row){
                labels.push(row.category_name);
                transactions.push(row.transaction_count);
                members.push(row.unique_members);
                sets.push(row.total_sets);
                revenue.push(row.total_revenue);
            });

            if(!categoryChart){

                categoryChart = new ApexCharts(
                    document.querySelector("#categoryPerformanceChart"),
                    {
                        chart:{
                            type:'bar',
                            height:400
                        },

                        plotOptions:{
                            bar:{
                                horizontal:false,
                                barHeight:'50%'
                            }
                        },

                        series:[
                            {name:'Transactions',data:transactions},
                            {name:'Unique Members',data:members},
                            {name:'Sets Sold',data:sets},
                            {name:'Revenue',data:revenue}
                        ],

                        xaxis:{
                            categories:labels
                        },

                        tooltip:{
                            shared:true,
                            intersect:false
                        },

                        colors:[
                            '#556ee6',
                            '#34c38f',
                            '#f1b44c',
                            '#f46a6a'
                        ]
                    }
                );

                categoryChart.render();

            }else{

                categoryChart.updateOptions({
                    series:[
                        {name:'Transactions',data:transactions},
                        {name:'Unique Members',data:members},
                        {name:'Sets Sold',data:sets},
                        {name:'Revenue',data:revenue}
                    ],
                    xaxis:{categories:labels}
                });

            }

        });
    }

    function loadIssuanceMethodChart()
    {
        $.get("{{ url('admin/voucher-issuance-method-data') }}", function(res){

            if(!issuanceMethodChart){

                issuanceMethodChart = new ApexCharts(
                    document.querySelector("#issuanceMethodChart"),
                    {
                        chart:{
                            type:'bar',
                            height:350
                        },
                        series:[{
                            name:'Total Vouchers Issued',
                            data:res.values
                        }],
                        xaxis:{
                            categories:res.labels
                        },
                        colors:['#556ee6'],
                        tooltip:{
                            shared:true,
                            intersect:false
                        }
                    }
                );

                issuanceMethodChart.render();

            }else{

                issuanceMethodChart.updateOptions({
                    series:[{data:res.values}],
                    xaxis:{categories:res.labels}
                });

            }

        });
    }

    function loadMonthlyTransactionsChart(){

        $.get("{{ url('admin/monthly-transactions-trend-data') }}",function(res){

            if(!monthlyTransactionsChart){

                monthlyTransactionsChart = new ApexCharts(
                    document.querySelector("#monthlyTransactionsChart"),
                    {
                        chart:{
                            type:'line',
                            height:350
                        },

                        stroke:{
                            width:[3,3]
                        },

                        series:[
                            {
                                name:'Transaction Count',
                                data:res.transactions
                            },
                            {
                                name:'Voucher Sets Sold',
                                data:res.sets
                            }
                        ],

                        xaxis:{
                            categories:res.labels
                        },

                        colors:[
                            '#556ee6',
                            '#34c38f'
                        ],

                        tooltip:{
                            shared:true,
                            intersect:false
                        }
                    }
                );

                monthlyTransactionsChart.render();

            }else{

                monthlyTransactionsChart.updateOptions({
                    series:[
                        {name:'Transaction Count',data:res.transactions},
                        {name:'Voucher Sets Sold',data:res.sets}
                    ],
                    xaxis:{categories:res.labels}
                });

            }

        });
    }

    function loadPurchaseFrequencyChart(){

    $.get("{{ url('admin/purchase-frequency-data') }}",function(res){

        if(!purchaseFrequencyChart){

            purchaseFrequencyChart = new ApexCharts(
                document.querySelector("#purchaseFrequencyChart"),
                {
                    chart:{
                        type:'bar',
                        height:350,
                        stacked:true
                    },

                    series:[
                        {
                            name:'Members',
                            data:res.members
                        },
                        {
                            name:'% of Transactions',
                            data:res.percentages
                        }
                    ],

                    xaxis:{
                        categories:res.labels
                    },

                    colors:[
                        '#556ee6',
                        '#34c38f'
                    ],

                    tooltip:{
                        shared:true,
                        intersect:false
                    }
                }
            );

            purchaseFrequencyChart.render();

        }else{

            purchaseFrequencyChart.updateOptions({
                series:[
                    {name:'Members',data:res.members},
                    {name:'% of Transactions',data:res.percentages}
                ],
                xaxis:{categories:res.labels}
            });

        }

    });
}

    function loadDemographicChart(){

        var type = $('#demographicType').val();
        var range = $('#ageRange').val();

        $.get("{{ url('admin/demographic-purchase-data') }}",
        {type:type,range:range},
        function(res){

            var chartType = 'bar';
            var options = {};

            if(type === 'gender'){
                chartType = 'donut';
            }

            if(type === 'region'){
                chartType = 'heatmap';
            }

            if(demographicChart){
                demographicChart.destroy();
            }

            if(chartType === 'heatmap'){

                options = {
                    chart:{
                        type:'heatmap',
                        height:350
                    },
                    series:[{
                        name:'Members',
                        data:res.labels.map(function(label,i){
                            return {x:label,y:res.values[i]};
                        })
                    }],
                    colors:['#556ee6']
                };

            }else if(chartType === 'donut'){

                options = {
                    chart:{
                        type:'donut',
                        height:350
                    },
                    series:res.values,
                    labels:res.labels,
                    colors:['#556ee6','#34c38f','#f1b44c','#f46a6a']
                };

            }else{

                options = {
                    chart:{
                        type:'bar',
                        height:350
                    },
                    series:[{
                        name:'Members',
                        data:res.values
                    }],
                    xaxis:{
                        categories:res.labels
                    },
                    colors:['#556ee6']
                };

            }

            demographicChart = new ApexCharts(
                document.querySelector("#demographicChart"),
                options
            );

            demographicChart.render();

        });
    }

    function loadMemberParticipationChart(){

        var type = $('#participationType').val();

        $.get("{{ url('admin/member-participation-data') }}",
        {type:type},
        function(res){

            // destroy old chart
            if(memberChart && typeof memberChart.destroy === "function"){
                memberChart.destroy();
            }

            var options = {
                chart:{
                    type:'line',
                    height:350
                },
                series:[
                    {
                        name:'Unique Members',
                        data:res.unique_members   // ✅ correct
                    },
                    {
                        name:'Repeat Purchase Rate (%)',
                        data:res.repeat_rate      // ✅ correct
                    }
                ],
                xaxis:{
                    categories:res.labels
                },
                stroke:{
                    curve:'smooth'
                },
                yaxis:[
                    {
                        title:{ text:'Members' }
                    },
                    {
                        opposite:true,
                        title:{ text:'Repeat %' }
                    }
                ],
                colors:['#556ee6','#34c38f'],
                tooltip:{
                    shared:true
                }
            };

            memberChart = new ApexCharts(
                document.querySelector("#memberChart"),
                options
            );

            memberChart.render();

            // ✅ show totals separately (not in chart)
            $('#totalMembers').text(res.total_unique_members);
            $('#repeatRate').text(res.overall_repeat_rate + '%');
        });
    }

    function loadCampaignChart(){

        $.get("{{ url('admin/campaign-performance-data') }}", function(res){

            var fullLabels = res.labels;

            var shortLabels = res.labels.map(function(name){
                return name.length > 8 ? name.substring(0,10) + '…' : name;
            });

            function tooltipFormatter(seriesIndex, dataPointIndex){
                return fullLabels[dataPointIndex];
            }

            
            if(campaignChart && typeof campaignChart.destroy === "function"){
                campaignChart.destroy();
            }

            var el = document.querySelector("#campaignChart");

            if(!el){
                console.error("Chart element not found");
                return;
            }

            var options = {
                chart:{
                    type:'bar',
                    height:350
                },
                series:[
                    {
                        name:'Vouchers Issued',
                        data:res.issued || []
                    },
                    {
                        name:'Vouchers Redeemed',
                        data:res.redeemed || []
                    },
                    {
                        name:'Redemption Rate (%)',
                        type:'line',
                        data:res.rate || []
                    }
                ],
                xaxis:{categories:shortLabels},
                tooltip:{
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        return '<div class="apex-tooltip">'+
                            fullLabels[dataPointIndex] +
                            '<br>Count: '+series[seriesIndex][dataPointIndex]+
                            '</div>';
                    }
                },
                yaxis:[
                    {
                        title:{ text:'Count' }
                    },
                    {
                        opposite:true,
                        title:{ text:'Rate (%)' }
                    }
                ],
                colors:['#556ee6','#34c38f','#f46a6a'],
                stroke:{
                    width:[0,0,3]
                },
                dataLabels:{
                    enabled:true,
                    enabledOnSeries:[2]
                },
               
            };

            campaignChart = new ApexCharts(el, options);
            campaignChart.render();
        });
    }
    
    function loadTopDealsChart(){

        $.get("{{ url('admin/top-deals-data') }}", function(res){

            var labels = [];
            var counts = [];
            var revenue = [];

            res.forEach(function(item){
                labels.push(item.voucher_name);
                counts.push(item.purchase_count);
                revenue.push(item.total_revenue);
            });

            if(!topDealsChart){

                topDealsChart = new ApexCharts(
                    document.querySelector("#topDealsChart"),
                    {
                        chart:{
                            type:'bar',
                            height:350
                        },

                        plotOptions:{
                            bar:{
                                horizontal:false,
                                barHeight:'50%'
                            }
                        },

                        series:[
                            {
                                name:'Purchase Count',
                                data:counts
                            },
                            {
                                name:'Revenue',
                                data:revenue
                            }
                        ],

                        xaxis:{
                            categories:labels
                        },

                        colors:[
                            '#556ee6',
                            '#34c38f'
                        ],

                        tooltip:{
                            shared:true,
                            intersect:false
                        }
                    }
                );

                topDealsChart.render();

            }else{

                topDealsChart.updateOptions({
                    series:[
                        {name:'Purchase Count',data:counts},
                        {name:'Revenue',data:revenue}
                    ],
                    xaxis:{categories:labels}
                });

            }

        });
    }



    $(document).ready(function(){
        loadCharts();
        loadOutletCharts();
        loadRedemptionRateTrend();
        loadCampaignChart();    
        loadIssuanceMethodChart();
        loadCategoryPerformance();
        loadMonthlyTransactionsChart();
        loadPurchaseFrequencyChart();
        loadDemographicChart();    
        loadMemberParticipationChart();    
        loadTopDealsChart();


        $('#rewardTypeFilter').trigger('change');
        $(document).on('change', '#rewardTypeFilter', function () {

            let type = $(this).val();

            if (type === "0") {
                // Treats & Deals
                $('#treatsDealsSection').show();
                $('#evoucherSection').hide();

            } else if (type === "1") {
                // E-Voucher
                $('#treatsDealsSection').hide();
                $('#evoucherSection').show();

            } else {
                // ✅ ALL (empty or anything else)
                $('#treatsDealsSection').show();
                $('#evoucherSection').show();
            }

        });

        $('#trendType').change(function(){
            loadCharts($(this).val());
        });
        
        $('#rateTrendType').change(function(){
            loadRedemptionRateTrend($(this).val());
        });
          
        $('#demographicType').change(function(){
            loadDemographicChart();
        });       
      
        $('#participationType').change(function(){
            loadMemberParticipationChart();
        });       
      
    });

</script>

@endsection