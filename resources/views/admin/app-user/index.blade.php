@extends('layouts.master-layouts')

@section('title') Application User @endsection
@section('content')

    @component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{url('/')}} @endslot
    @slot('title') Application User @endslot
    @endcomponent



    <div class="card">
        {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            <h4 class="card-title mb-0">User Management</h4>

        </div>--}}
        <div class="card-body pt-0 mt-4">
            <!-- <div class="wmd-view-topscroll">
            <div class="scroll-div1">
            </div>
        </div> -->
            <div class="table-responsive">
                <table class="sh_table table table-bordered" id="bstable"
    data-toggle="table"
    data-page-size="100"
    data-ajax="ajaxRequest"
    data-side-pagination="server"
    data-pagination="true"
    data-total-field="count"
    data-data-field="items">

    <thead>
        <tr>
            <th data-field="sr_no">Sr. No.</th>
            <th data-field="user_id" data-sortable="true">User ID</th>
            <th data-field="action">Action</th>
        </tr>
    </thead>
</table>

            </div>
        </div>
    </div>

    <!-- Create -->
    @if(Auth::user()->can("$permission_prefix-create"))
        {{-- @include('admin.user.add-edit-modal') --}}
    @endif
    <!-- end modal -->
@endsection

@section('script')
    <script>
        var ModuleBaseUrl = "{{ $module_base_url }}/";
        var DataTableUrl = ModuleBaseUrl + "datatable";
        function ajaxRequest(params) {
            $.get(DataTableUrl + '?' + $.param(params.data)).then(function (res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res)

                doubleScrollTuareg();
                setTimeout(() => {
                    var dtOwidth = $('body').find("#bstable").width()
                    $(".scroll-div1").width(dtOwidth);
                    $("body .wmd-view-topscroll").off("scroll");
                    $("body .table-responsive").off("scroll");
                    doubleScrollTuareg();
                }, 300);

            })
        }


    </script>
    <script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection

<script>
   
</script>