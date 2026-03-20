@extends('layouts.master-layouts')

@section('title') @lang('translation.Dashboards') @endsection

@section('content')

@component('components.breadcrumb')
@slot('li_1') Dashboard @endslot
@slot('title') Dashboard @endslot
@endcomponent

<style>
    .apexcharts-toolbar {
        display: none !important;
    }
</style>

<div class="d-flex justify-content-end mb-3">
    <select id="rewardTypeFilter" class="form-control w-auto">
        <option value="">All</option>
        <option value="0">Treats &amp; Deals</option>
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
                <div class="card-header"><h4 class="mb-0">Redemption by Outlet</h4></div>
                <div class="card-body">
                    <div id="outletPerformanceChart"></div>
                </div>
            </div>
        </div>

        <!-- Campaign Performance Comparison -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Campaign Performance Comparison</h4>
                    <select id="rewardArr" name="rewardArr[]" class="form-control w-25 select2" multiple>
                        @foreach ($rewards as $key => $reward)
                            <option value="{{ $reward->campaign }}" @if($key < 7) selected @endif>{{ $reward->campaign }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body">
                    <div id="campaignChart"></div>
                </div>
            </div>
        </div>

        <!-- Voucher Redemption Rate Trend -->
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

    </div><!-- /#evoucherSection -->

    <div id="treatsDealsSection" class="row">

        <!-- Category Performance Summary -->
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

        <!-- Monthly Spending Trend -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Monthly Spending Trend</h4>
                </div>
                <div class="card-body">
                    <div id="topDealsChart"></div>
                </div>
            </div>
        </div>

        <!-- Monthly Unique Member Participation -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Monthly Unique Member Participation</h4>
                    <select id="participationType" class="form-control w-auto">
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

    </div><!-- /#treatsDealsSection -->

</div><!-- /.row -->

@endsection

@section('script')
<script src="{{ URL::asset('/build/libs/apexcharts/apexcharts.min.js') }}"></script>

<script>
    // ─── Chart instance variables ───────────────────────────────────────────────
    var issuanceChart             = null;
    var redeemChart               = null;
    var outletCountChart          = null;
    var outletValueChart          = null;
    var outletPerformanceChart    = null;
    var redemptionRateTrendChart  = null;
    var categoryChart             = null;
    var issuanceMethodChart       = null;
    var monthlyTransactionsChart  = null;
    var purchaseFrequencyChart    = null;
    var demographicChart          = null;
    var memberChart               = null;
    var campaignChart             = null;
    var topDealsChart             = null;

    // ─── Helper: safely destroy a chart ─────────────────────────────────────────
    function destroyChart(chartVar) {
        if (chartVar && typeof chartVar.destroy === 'function') {
            try { chartVar.destroy(); } catch(e) {}
        }
        return null;
    }

    // ─── Helper: sanitize number array (replace NaN/null/undefined with 0) ──────
    function sanitize(arr) {
        if (!Array.isArray(arr)) return [];
        return arr.map(function(v) {
            var n = parseFloat(v);
            return isNaN(n) ? 0 : n;
        });
    }

    // ─── Helper: shorten label ───────────────────────────────────────────────────
    function shortLabel(name, max) {
        max = max || 10;
        return (name && name.length > max) ? name.substring(0, max) + '…' : (name || '');
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 1. Voucher Issuance + Redeem Trend
    // ════════════════════════════════════════════════════════════════════════════
    function loadCharts(type) {
        type = type || 'week';

        $.get("{{ url('admin/voucher-trend-data') }}", { type: type }, function(res) {
            if (!res) return;

            var labels   = res.labels   || [];
            var issuance = sanitize(res.issuance);
            var redeem   = sanitize(res.redeem);

            if (issuanceChart) {
                issuanceChart.updateOptions({ xaxis: { categories: labels }, series: [{ data: issuance }] });
                redeemChart.updateOptions(  { xaxis: { categories: labels }, series: [{ data: redeem   }] });
                return;
            }

            var issuanceEl = document.querySelector('#voucherIssuanceChart');
            var redeemEl   = document.querySelector('#voucherRedeemChart');
            if (!issuanceEl || !redeemEl) return;

            issuanceChart = new ApexCharts(issuanceEl, {
                chart:  { type: 'area', height: 350 },
                series: [{ name: 'Issued', data: issuance }],
                xaxis:  { categories: labels },
                colors: ['#556ee6']
            });
            issuanceChart.render();

            redeemChart = new ApexCharts(redeemEl, {
                chart:  { type: 'line', height: 350 },
                series: [{ name: 'Redeemed', data: redeem }],
                xaxis:  { categories: labels },
                colors: ['#34c38f']
            });
            redeemChart.render();

        }).fail(function() { console.warn('loadCharts: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 2. Outlet Performance
    // ════════════════════════════════════════════════════════════════════════════
    function loadOutletCharts() {
        $.get("{{ url('admin/outlet-redemption-data') }}", function(res) {
            if (!res) return;

            var fullLabels  = res.labels || [];
            var shortLabels = fullLabels.map(function(n) { return shortLabel(n, 10); });
            var count       = sanitize(res.count);
            var value       = sanitize(res.value);

            var el = document.querySelector('#outletPerformanceChart');
            if (!el) return;

            if (!outletPerformanceChart) {
                outletPerformanceChart = new ApexCharts(el, {
                    chart:        { type: 'bar', height: 320 },
                    plotOptions:  { bar: { columnWidth: '45%' } },
                    colors:       ['#556ee6', '#34c38f'],
                    series: [
                        { name: 'Redemption Count', data: count },
                        { name: 'Redemption Value', data: value }
                    ],
                    xaxis: { categories: shortLabels },
                    legend: { position: 'top' },
                    tooltip: {
                        custom: function(opts) {
                            var i = opts.dataPointIndex;
                            return '<div style="padding:8px">' +
                                '<b>' + (fullLabels[i] || '') + '</b><br/>' +
                                'Count: '  + (count[i]  || 0) + '<br/>' +
                                'Value: '  + (value[i]  || 0) +
                                '</div>';
                        }
                    }
                });
                outletPerformanceChart.render();
            } else {
                outletPerformanceChart.updateOptions({
                    series: [
                        { name: 'Redemption Count', data: count },
                        { name: 'Redemption Value', data: value }
                    ],
                    xaxis: { categories: shortLabels }
                });
            }

        }).fail(function() { console.warn('loadOutletCharts: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 3. Redemption Rate Trend
    // ════════════════════════════════════════════════════════════════════════════
    function loadRedemptionRateTrend(type) {
        type = type || 'week';

        $.get("{{ url('admin/redemption-rate-trend-data') }}", { type: type }, function(res) {
            if (!res) return;

            var labels   = res.labels   || [];
            var issued   = sanitize(res.issued);
            var redeemed = sanitize(res.redeemed);
            var rate     = sanitize(res.rate);

            var el = document.querySelector('#redemptionRateTrendChart');
            if (!el) return;

            if (!redemptionRateTrendChart) {
                redemptionRateTrendChart = new ApexCharts(el, {
                    chart:  { type: 'line', height: 350 },
                    stroke: { width: [3, 3, 3], curve: 'smooth' },
                    colors: ['#556ee6', '#34c38f', '#f46a6a'],
                    series: [
                        { name: 'Issued',             data: issued   },
                        { name: 'Redeemed',           data: redeemed },
                        { name: 'Redemption Rate %',  data: rate     }
                    ],
                    xaxis: { categories: labels },
                    yaxis: [
                        { title: { text: 'Vouchers' } },
                        { show: false },
                        {
                            opposite: true,
                            title:    { text: 'Rate %' },
                            min: 0, max: 100,
                            labels: { formatter: function(v) { return parseFloat(v).toFixed(2) + '%'; } }
                        }
                    ],
                    tooltip: { shared: true, intersect: false }
                });
                redemptionRateTrendChart.render();
            } else {
                redemptionRateTrendChart.updateOptions({
                    series: [
                        { name: 'Issued',            data: issued   },
                        { name: 'Redeemed',          data: redeemed },
                        { name: 'Redemption Rate %', data: rate     }
                    ],
                    xaxis: { categories: labels }
                });
            }

        }).fail(function() { console.warn('loadRedemptionRateTrend: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 4. Campaign Performance
    // ════════════════════════════════════════════════════════════════════════════
    function loadCampaignChart() {
        var rewardVals = $('#rewardArr').val();
        if (!rewardVals || rewardVals.length === 0) return;

        $.post(
            "{{ url('admin/campaign-performance-data') }}",
            { rewardVals: rewardVals, _token: "{{ csrf_token() }}" },
            function(res) {
                if (!res || !res.labels || res.labels.length === 0) return;

                var el = document.querySelector('#campaignChart');
                if (!el) return;

                campaignChart = destroyChart(campaignChart);

                var fullLabels  = res.labels;
                var shortLabels = fullLabels.map(function(n) { return shortLabel(n, 8); });
                var issued      = sanitize(res.issued);
                var redeemed    = sanitize(res.redeemed);
                var rate        = sanitize(res.rate);
                var barCount    = fullLabels.length;

                campaignChart = new ApexCharts(el, {
                    chart: {
                        type:    'bar',
                        height:  380,
                        width:   '100%',
                        stacked: false,
                        toolbar: { show: false },
                        zoom:    { enabled: false }
                    },
                    series: [
                        { name: 'Issued',   type: 'column', data: issued   },
                        { name: 'Redeemed', type: 'column', data: redeemed },
                        { name: 'Rate (%)', type: 'line',   data: rate     }
                    ],
                    xaxis: {
                        categories: shortLabels,
                        labels: {
                            rotate:       -60,
                            rotateAlways: true,
                            trim:         false,
                            style:        { fontSize: '11px' }
                        },
                        tickPlacement: 'on'
                    },
                    yaxis: [
                        {
                            seriesName: 'Issued',
                            title:  { text: 'Vouchers' },
                            min:    0,
                            labels: { formatter: function(v) { return Math.floor(v); } }
                        },
                        {
                            seriesName: 'Redeemed',
                            show: false
                        },
                        {
                            seriesName: 'Rate (%)',
                            opposite: true,
                            title:    { text: 'Rate (%)' },
                            min: 0,   max: 100,
                            labels: { formatter: function(v) { return parseFloat(v).toFixed(2) + '%'; } }
                        }
                    ],
                    plotOptions: {
                        bar: { columnWidth: barCount > 20 ? '70%' : '40%' }
                    },
                    stroke:  { width: [0, 0, 2] },
                    colors:  ['#556ee6', '#34c38f', '#f46a6a'],
                    markers: { size: [0, 0, 4] },
                    tooltip: {
                        shared:    true,
                        intersect: false,
                        custom: function(opts) {
                            var i    = opts.dataPointIndex;
                            var r    = parseFloat(rate[i] || 0).toFixed(2);
                            return '<div style="padding:10px;font-size:13px;line-height:1.8;">' +
                                '<b>' + (fullLabels[i] || '') + '</b><br/>' +
                                '<span style="color:#556ee6;">■</span> Issued: <b>'   + (issued[i]   || 0) + '</b><br/>' +
                                '<span style="color:#34c38f;">■</span> Redeemed: <b>' + (redeemed[i] || 0) + '</b><br/>' +
                                '<span style="color:#f46a6a;">■</span> Rate: <b>'     + r + '%</b>' +
                                '</div>';
                        }
                    },
                    legend:      { position: 'top', horizontalAlign: 'center' },
                    dataLabels:  { enabled: false },
                    grid:        { padding: { left: 10, right: 20 } },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            chart:  { height: 300 },
                            xaxis:  { labels: { rotate: -90 } },
                            legend: { position: 'bottom' }
                        }
                    }]
                });

                campaignChart.render();
            }
        ).fail(function() { console.warn('loadCampaignChart: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 5. Category Performance
    // ════════════════════════════════════════════════════════════════════════════
    function loadCategoryPerformance() {
        $.get("{{ url('admin/category-performance-data') }}", function(res) {
            if (!res || !Array.isArray(res)) return;

            var el = document.querySelector('#categoryPerformanceChart');
            if (!el) return;

            var labels = [], transactions = [], members = [], sets = [], revenue = [];
            res.forEach(function(row) {
                labels.push(row.category_name       || '');
                transactions.push(+row.transaction_count || 0);
                members.push(     +row.unique_members    || 0);
                sets.push(        +row.total_sets        || 0);
                revenue.push(     +row.total_revenue     || 0);
            });

            if (!categoryChart) {
                categoryChart = new ApexCharts(el, {
                    chart:       { type: 'bar', height: 400 },
                    plotOptions: { bar: { horizontal: false, barHeight: '50%' } },
                    series: [
                        { name: 'Transactions',    data: transactions },
                        { name: 'Unique Members',  data: members      },
                        { name: 'Sets Sold',       data: sets         },
                        { name: 'Revenue',         data: revenue      }
                    ],
                    xaxis:   { categories: labels },
                    colors:  ['#556ee6', '#34c38f', '#f1b44c', '#f46a6a'],
                    tooltip: { shared: true, intersect: false }
                });
                categoryChart.render();
            } else {
                categoryChart.updateOptions({
                    series: [
                        { name: 'Transactions',   data: transactions },
                        { name: 'Unique Members', data: members      },
                        { name: 'Sets Sold',      data: sets         },
                        { name: 'Revenue',        data: revenue      }
                    ],
                    xaxis: { categories: labels }
                });
            }

        }).fail(function() { console.warn('loadCategoryPerformance: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 6. Issuance Method
    // ════════════════════════════════════════════════════════════════════════════
    function loadIssuanceMethodChart() {
        $.get("{{ url('admin/voucher-issuance-method-data') }}", function(res) {
            if (!res) return;

            var el = document.querySelector('#issuanceMethodChart');
            if (!el) return;

            var values = sanitize(res.values);
            var labels = res.labels || [];

            if (!issuanceMethodChart) {
                issuanceMethodChart = new ApexCharts(el, {
                    chart:   { type: 'bar', height: 350 },
                    series:  [{ name: 'Total Vouchers Issued', data: values }],
                    xaxis:   { categories: labels },
                    colors:  ['#556ee6'],
                    tooltip: { shared: true, intersect: false }
                });
                issuanceMethodChart.render();
            } else {
                issuanceMethodChart.updateOptions({
                    series: [{ data: values }],
                    xaxis:  { categories: labels }
                });
            }

        }).fail(function() { console.warn('loadIssuanceMethodChart: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 7. Monthly Transactions Trend
    // ════════════════════════════════════════════════════════════════════════════
    function loadMonthlyTransactionsChart() {
        $.get("{{ url('admin/monthly-transactions-trend-data') }}", function(res) {
            if (!res) return;

            var el = document.querySelector('#monthlyTransactionsChart');
            if (!el) return;

            var labels       = res.labels || [];
            var transactions = sanitize(res.transactions);
            var sets         = sanitize(res.sets);

            if (!monthlyTransactionsChart) {
                monthlyTransactionsChart = new ApexCharts(el, {
                    chart:   { type: 'line', height: 350 },
                    stroke:  { width: [3, 3], curve: 'smooth' },
                    series: [
                        { name: 'Transaction Count',  data: transactions },
                        { name: 'Voucher Sets Sold',  data: sets         }
                    ],
                    xaxis:   { categories: labels },
                    colors:  ['#556ee6', '#34c38f'],
                    tooltip: { shared: true, intersect: false }
                });
                monthlyTransactionsChart.render();
            } else {
                monthlyTransactionsChart.updateOptions({
                    series: [
                        { name: 'Transaction Count', data: transactions },
                        { name: 'Voucher Sets Sold', data: sets         }
                    ],
                    xaxis: { categories: labels }
                });
            }

        }).fail(function() { console.warn('loadMonthlyTransactionsChart: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 8. Top Deals / Monthly Spending Trend
    // ════════════════════════════════════════════════════════════════════════════
    function loadTopDealsChart() {
        $.get("{{ url('admin/top-deals-data') }}", function(res) {
            if (!res || !Array.isArray(res)) return;

            var el = document.querySelector('#topDealsChart');
            if (!el) return;

            var fullLabels = [], counts = [], revenue = [];
            res.forEach(function(item) {
                fullLabels.push(item.voucher_name  || '');
                counts.push(   +item.purchase_count || 0);
                revenue.push(  +item.total_revenue  || 0);
            });
            var shortLabels = fullLabels.map(function(n) { return shortLabel(n, 10); });

            if (!topDealsChart) {
                topDealsChart = new ApexCharts(el, {
                    chart:       { type: 'bar', height: 350 },
                    plotOptions: { bar: { horizontal: false, columnWidth: '50%' } },
                    series: [
                        { name: 'Purchase Count', data: counts  },
                        { name: 'Revenue',        data: revenue }
                    ],
                    xaxis:   { categories: shortLabels, labels: { rotate: -30 } },
                    colors:  ['#556ee6', '#34c38f'],
                    tooltip: {
                        custom: function(opts) {
                            var i = opts.dataPointIndex;
                            return '<div style="padding:8px">' +
                                '<b>' + (fullLabels[i] || '') + '</b><br/>' +
                                'Count: '   + (counts[i]  || 0) + '<br/>' +
                                'Revenue: ' + (revenue[i] || 0) +
                                '</div>';
                        }
                    }
                });
                topDealsChart.render();
            } else {
                topDealsChart.updateOptions({
                    series: [
                        { name: 'Purchase Count', data: counts  },
                        { name: 'Revenue',        data: revenue }
                    ],
                    xaxis: { categories: shortLabels }
                });
            }

        }).fail(function() { console.warn('loadTopDealsChart: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 9. Purchase Frequency Distribution
    // ════════════════════════════════════════════════════════════════════════════
    function loadPurchaseFrequencyChart() {
        $.get("{{ url('admin/purchase-frequency-data') }}", function(res) {
            if (!res) return;

            var el = document.querySelector('#purchaseFrequencyChart');
            if (!el) return;

            var labels      = res.labels || [];
            var members     = sanitize(res.members);
            var percentages = sanitize(res.percentages);

            if (!purchaseFrequencyChart) {
                purchaseFrequencyChart = new ApexCharts(el, {
                    chart:   { type: 'bar', height: 350, stacked: true },
                    series: [
                        { name: 'Members',              data: members     },
                        { name: '% of Transactions',    data: percentages }
                    ],
                    xaxis:   { categories: labels },
                    colors:  ['#556ee6', '#34c38f'],
                    tooltip: { shared: true, intersect: false }
                });
                purchaseFrequencyChart.render();
            } else {
                purchaseFrequencyChart.updateOptions({
                    series: [
                        { name: 'Members',           data: members     },
                        { name: '% of Transactions', data: percentages }
                    ],
                    xaxis: { categories: labels }
                });
            }

        }).fail(function() { console.warn('loadPurchaseFrequencyChart: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 10. Demographic Purchase Profiling
    // ════════════════════════════════════════════════════════════════════════════
    function loadDemographicChart() {
        var type  = $('#demographicType').val();
        var range = $('#ageRange').val();

        $.get("{{ url('admin/demographic-purchase-data') }}", { type: type, range: range }, function(res) {
            if (!res) return;

            var el = document.querySelector('#demographicChart');
            if (!el) return;

            demographicChart = destroyChart(demographicChart);

            var labels = res.labels || [];
            var values = sanitize(res.values);
            var options;

            if (type === 'region') {
                options = {
                    chart:  { type: 'heatmap', height: 350 },
                    series: [{
                        name: 'Members',
                        data: labels.map(function(label, i) { return { x: label, y: values[i] || 0 }; })
                    }],
                    colors: ['#556ee6']
                };
            } else if (type === 'gender') {
                options = {
                    chart:  { type: 'donut', height: 350 },
                    series: values,
                    labels: labels,
                    colors: ['#556ee6', '#34c38f', '#f1b44c', '#f46a6a']
                };
            } else {
                options = {
                    chart:   { type: 'bar', height: 350 },
                    series:  [{ name: 'Members', data: values }],
                    xaxis:   { categories: labels },
                    colors:  ['#556ee6']
                };
            }

            demographicChart = new ApexCharts(el, options);
            demographicChart.render();

        }).fail(function() { console.warn('loadDemographicChart: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 11. Member Participation
    // ════════════════════════════════════════════════════════════════════════════
    function loadMemberParticipationChart() {
        var type = $('#participationType').val();

        $.get("{{ url('admin/member-participation-data') }}", { type: type }, function(res) {
            if (!res) return;

            var el = document.querySelector('#memberChart');
            if (!el) return;

            memberChart = destroyChart(memberChart);

            var labels         = res.labels        || [];
            var unique_members = sanitize(res.unique_members);
            var repeat_rate    = sanitize(res.repeat_rate);

            memberChart = new ApexCharts(el, {
                chart:  { type: 'line', height: 350 },
                stroke: { curve: 'smooth', width: [3, 3] },
                series: [
                    { name: 'Unique Members',          data: unique_members },
                    { name: 'Repeat Purchase Rate (%)', data: repeat_rate   }
                ],
                xaxis: { categories: labels },
                yaxis: [
                    { title: { text: 'Members' } },
                    { opposite: true, title: { text: 'Repeat %' } }
                ],
                colors:  ['#556ee6', '#34c38f'],
                tooltip: { shared: true, intersect: false }
            });

            memberChart.render();

            // Show summary totals
            if (typeof res.total_unique_members !== 'undefined') {
                $('#totalMembers').text(res.total_unique_members);
            }
            if (typeof res.overall_repeat_rate !== 'undefined') {
                $('#repeatRate').text(res.overall_repeat_rate + '%');
            }

        }).fail(function() { console.warn('loadMemberParticipationChart: request failed'); });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // DOM READY
    // ════════════════════════════════════════════════════════════════════════════
    $(document).ready(function() {

        // ── Init Select2 FIRST (fixes addEventListener null error) ──────────────
        if ($.fn.select2) {
            $('#rewardArr').select2({
                placeholder: 'Select campaigns',
                allowClear:  true
            });
        }

        // ── Load all charts ─────────────────────────────────────────────────────
        loadCharts();
        loadOutletCharts();
        loadCampaignChart();
        loadRedemptionRateTrend();
        loadIssuanceMethodChart();
        loadCategoryPerformance();
        loadMonthlyTransactionsChart();
        loadTopDealsChart();
        loadMemberParticipationChart();
        loadPurchaseFrequencyChart();
        loadDemographicChart();

        // ── Trigger filter to set initial visibility ────────────────────────────
        $('#rewardTypeFilter').trigger('change');

        // ── Event listeners ─────────────────────────────────────────────────────
        $(document).on('change', '#rewardTypeFilter', function() {
            var type = $(this).val();
            if (type === '0') {
                $('#treatsDealsSection').show();
                $('#evoucherSection').hide();
            } else if (type === '1') {
                $('#treatsDealsSection').hide();
                $('#evoucherSection').show();
            } else {
                $('#treatsDealsSection').show();
                $('#evoucherSection').show();
            }
        });

        $('#trendType').on('change', function() {
            loadCharts($(this).val());
        });

        $('#rateTrendType').on('change', function() {
            loadRedemptionRateTrend($(this).val());
        });

        $('#demographicType').on('change', function() {
            loadDemographicChart();
        });

        $('#participationType').on('change', function() {
            loadMemberParticipationChart();
        });

        // ── Campaign: max 7 + live reload ───────────────────────────────────────
        $('#rewardArr').on('change', function() {
            // ✅ Remove blank/empty values from selection
            $('#rewardArr option:selected').each(function() {
                if ($(this).val() === '' || $(this).val() === null) {
                    $(this).prop('selected', false);
                }
            });
            var selected = $('#rewardArr option:selected');
            console.log('Selected campaigns:', selected.map(function() { return $(this).text(); }).get());
            if (selected.length > 7) {
                alert('Maximum 7 selections allowed');
                selected.last().prop('selected', false);
                if ($.fn.select2) { $('#rewardArr').trigger('change.select2'); }
                return;
            }
            loadCampaignChart();
        });

    }); // end document.ready

    // ── Utility exposed globally (used in some blade templates) ─────────────────
    function scroll_div(div_class) {
        var height = $(div_class).offset().top - 140;
        $('html, body').animate({ scrollTop: height }, 'slow');
    }
</script>

@endsection