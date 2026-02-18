@extends('layouts.master-layouts')

@section('title') Application User @endsection
@section('content')

    @component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{url('/')}} @endslot
    @slot('title') Application User @endslot
    @endcomponent



    <div class="card">      
        <div class="card-body pt-0 mt-4">        
            <div class="table-responsive">
                <table class="sh_table table table-bordered" id="bstable"
                    data-toggle="table"
                    data-page-size="100"
                    data-ajax="ajaxRequest"
                    data-side-pagination="server"
                    data-pagination="true"
                    data-total-field="count"
                    data-data-field="items"
                    data-filter-control="true">
                    <thead>
                        <tr>
                            <th data-field="sr_no">Sr. No.</th>
                           <th data-field="user_id"
                                data-filter-control="input"
                                data-sortable="true"
                                data-escape="true">
                                User ID
                            </th>
                            <th data-field="action">Action</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>

    <!-- Create -->
   
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