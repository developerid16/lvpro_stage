{{-- resources/views/admin/reward/index.blade.php --}}
@extends('layouts.master-layouts')

@section('title')
    Voucher List
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{ url('/') }} @endslot
    @slot('title') Voucher List @endslot
@endcomponent

<div class="card">
  
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered"
                   id="bstable"
                   data-toggle="table"
                   data-side-pagination="server"
                   data-pagination="true"
                   data-search="false"
                   data-page-size="100"
                   data-ajax="ajaxRequest"
                   data-total-field="count"
                   data-data-field="items">

                <thead>
                <tr>
                    <th data-field="sr_no" data-width="70">#</th>
                    <th data-field="name">Name</th>
                    <th data-field="type">Type</th>
                    <th data-field="reward_type">Reward Type</th>
                    <th data-field="created_at">Created On</th>
                    <th data-field="updated_at">Updated On</th>
                    <th data-field="action" data-searchable="false" class="text-center">Action</th>
                </tr>
                </thead>

            </table>
        </div>
    </div>
</div>

{{-- VIEW MODAL --}}
<div class="modal fade" id="ViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Voucher Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th>Name</th><td id="v_name"></td></tr>
                    <tr><th>Type</th><td id="v_type"></td></tr>
                    <tr><th>Reward Type</th><td id="v_reward_type"></td></tr>
                    <tr><th>Description</th><td id="v_description"></td></tr>
                    <tr><th>Created At</th><td id="v_created"></td></tr>
                    <tr><th>Updated At</th><td id="v_updated"></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ADD / EDIT --}}
@can("$permission_prefix-create")
@endcan
@endsection


@section('script')
<script>
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var RewardBaseUrl = "{{ $reward_base_url }}/";
    var DataTableUrl = RewardBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data))
            .then(res => params.success(res));
    }

    // VIEW
    $(document).on('click','.view',function () {
        let id = $(this).data('id');

        $.get(RewardBaseUrl + id, function (res) {
            let d = res.data;

            $('#v_name').text(d.name);
            $('#v_type').text(
                d.type == 0 ? 'Treats & Deals' :
                d.type == 1 ? 'E-Voucher' : 'Birthday Voucher'
            );
            $('#v_reward_type').text(d.reward_type == 0 ? 'Digital' : 'Physical');
            $('#v_description').text(d.description ?? '-');
            $('#v_created').text(d.created_at);
            $('#v_updated').text(d.updated_at);

            $('#ViewModal').modal('show');
        });
    });

    // SUSPEND
    $(document).on('change','.suspend-switch',function () {
        $.post(RewardBaseUrl + 'suspend', {
            _token: '{{ csrf_token() }}',
            id: $(this).data('id'),
            status: $(this).is(':checked') ? 1 : 0
        });
    });
</script>
@endsection
