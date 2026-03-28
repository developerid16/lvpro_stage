@extends('layouts.master-layouts')

@section('title')
    Treats & Deals Management Listing
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Admin
        @endslot
        @slot('li_1_link')
            {{ url('/') }}
        @endslot
        @slot('title')
            Treats & Deals Management Listing
        @endslot
    @endcomponent


    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
          
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="sh_table table table-bordered" id="bstable" data-toggle="table"
                    data-page-list="[100, 500, 1000, 2000, All]" data-search-time-out="1200" data-page-size="100"
                    data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="true" data-search="false"
                    data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false"
                    data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                    <thead>
                        <tr>
                            <th data-field="sr_no" data-filter-control="input" data-sortable="false" data-width="75"
                                data-width-unit="px" data-searchable="false">Sr. No.</th>
                          
                            <th data-field="name" data-filter-control="input" data-sortable="true" data-escape="true">Name</th>
                            <th data-field="reward_type" data-filter-control="input" data-sortable="true" data-escape="true">Reward Type</th>
                            <th data-field="amount" data-sortable="true">Amount</th>
                            <th data-field="quantity" data-sortable="true">Total</th>
                          
                            <th data-field="image">Image</th>
                            <th data-field="created_at">Created On</th>
                            <th data-field="is_featured">Is Featured</th>
                            <th data-field="hide_catalogue">Hide From <br> Catalogue
                            </th> 
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('script')

    <script>
        var participatingLocations = {};
        var ModuleBaseUrl = "{{ $module_base_url }}/";
        var DataTableUrl = ModuleBaseUrl + "datatable";

        var digitalMerChants = [];
        let selectedType = 'plus'; // default
        

        function ajaxRequest(params) {
            $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res)
            })
        }  

        $(document).on('change', '.featured-toggle-switch', function () {

            let rewardId = $(this).data('id');
            let isChecked = $(this).is(':checked') ? 1 : 0;
            let $switch = $(this);

            $.ajax({
                url: ModuleBaseUrl + 'toggle-featured',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: rewardId,
                    is_featured: isChecked
                },
                success: function (res) {
                    show_message(true, 'Featured status updated');
                },
                error: function () {

                    // revert toggle if failed
                    $switch.prop('checked', !isChecked);

                    show_message(false, 'Server error');
                }
            });

        });


        $(document).on('change', '.hide-catalogue-switch', function () {
            let $this = $(this);
            let isChecked = $(this).is(':checked') ? 1 : 0;
            let $switch = $(this);
            $.ajax({
                url: ModuleBaseUrl + 'hide-catalogue',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: $this.data('id'),
                    status: isChecked
                },
                success: function (response) {
                    if (response.status) {
                        // $switch.prop('checked', !isChecked);
                        show_message(response.status, response.message);
                        // location.reload();
                    }
                },
                error: function () {
                    $switch.prop('checked', !isChecked);
                    show_message(false, 'Server error');
                }
            });

        });
        
       
    </script>     
@endsection
