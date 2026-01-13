@extends('layouts.master-layouts')

@section('title') @lang('translation.Dashboards') @endsection

@section('content')

@component('components.breadcrumb')
@slot('li_1') Dashboard @endslot
@slot('title') Dashboard @endslot
@endcomponent

<!-- end modal -->

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
</script>

@endsection