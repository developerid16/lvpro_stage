@extends('layouts.master-layouts')

@section('title') User Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') User Management @endslot
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        {{--<h4 class="card-title mb-0">User Management</h4>--}}
        @if(Auth::user()->can("$permission_prefix-create"))
        <button class="btn btn-primary ml_auto" data-bs-toggle="modal" data-bs-target="#AddModal"><i class="mdi mdi-plus"></i>
            Add New</button>
        @endif
    </div>
    <div class="card-body pt-0">
    <!-- <div class="wmd-view-topscroll">
        <div class="scroll-div1">
        </div>
    </div> -->
        <div class="table-responsive">
            <table class="sh_table table table-bordered" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr>
                        <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75" data-width-unit="px" data-searchable="false">Sr. No.</th>
                        <th data-field="name" data-filter-control="input" data-sortable="true">Name</th>
                        <th data-field="email" data-filter-control="input" data-sortable="true">Email</th>
                        <th data-field="phone" data-filter-control="input" data-sortable="true">Contact</th>
                        <th data-field="status" data-filter-control="select" data-sortable="false" data-filter-data="var:filterDefaultsGender">Status</th>
                        <th data-field="roles" data-filter-control="input" data-sortable="false">Role</th>
                        <th class="text-center" data-field="action" data-searchable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create -->
@if(Auth::user()->can("$permission_prefix-create"))
@include('admin.user.add-edit-modal')
@endif
<!-- end modal -->
@endsection

@section('script')
<script>
    var filterDefaultsGender = {
        Active: 'Active',
        Lockout: 'Lockout',
        Disabled: 'Disabled'
    };
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
            doubleScrollTuareg();
        })
    }
</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection


<script>
/* for horizontal scroll START here */
// function doubleScrollTuareg(){
//     var running = false;
//     $("body .wmd-view-topscroll").scroll(function(){
//         console.log('test');
//         if(running) {
//             running = false;
//             return;
//         }
//         running = true;
//         $("body .bootstrap-table").scrollLeft($("body .wmd-view-topscroll").scrollLeft());
//     });

//     $("body .bootstrap-table").scroll(function(){
//         console.log('test2');
//         if(running) {
//             running = false;
//             return;
//         }
//         running = true;
//         $("body .wmd-view-topscroll").scrollLeft($("body .bootstrap-table").scrollLeft());
//     });

//     if( $('.scroll-div1').length > 0 ){
//         const dtOwidth = $("#bstable").width();
//         console.log( dtOwidth );
//         $(".scroll-div1").width( dtOwidth );
//         doubleScrollTuareg();
//     }

//     setTimeout(() => {
//         var dtOwidth = $('body').find("#bstable").width()
//         $(".scroll-div1").width( dtOwidth );
//         $("body .wmd-view-topscroll").off("scroll");
//         $("body .bootstrap-table").off("scroll");
//         doubleScrollTuareg();
//     }, 300);
// }
/* for horizontal scroll OVER here */
</script>