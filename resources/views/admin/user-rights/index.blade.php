@extends('layouts.master-layouts')
<style>
.dragging-row {
    background: #eef3ff !important;
    opacity: 0.9;
}


</style>
@section('title') User Rights Request @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') User Rights Request @endslot
@endcomponent



<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
      
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="sh_table table table-bordered" data-id-field="id" id="bstable" data-toggle="table" data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-ajax="ajaxRequest" data-side-pagination="server" data-pagination="false" data-search="false" data-total-field="count" data-data-field="items" data-show-columns="false" data-show-toggle="false" data-show-export="false" data-filter-control="true" data-show-columns-toggle-all="false">
                <thead>
                    <tr> 
                        <th data-field="sr_no" data-sortable="false" data-width="75" data-width-unit="px"data-searchable="false">  Sr. No.</th>
                        <th data-field="name" data-filter-control="input"  data-sortable="true">User Name </th>
                        <th data-field="email" data-sortable="false"> Email</th>
                        <th data-field="description" data-sortable="false"> Description</th>
                        <th data-field="status" data-sortable="false"> Status</th>
                        <th data-field="created_at" data-sortable="false"> Date</th>
                        <th class="text-center" data-field="action" data-searchable="false"> Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="ApproveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="approveForm">
                @csrf
                <input type="hidden" name="request_id" id="request_id">

                <div class="modal-header">
                    <h5 class="modal-title">Approve User Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                         <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Name<span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter Name" value="">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Email<span class="required-hash"></span></label>
                                <input id="email" type="email" class="sh_dec form-control" name="email" placeholder="Enter Email" value="" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Phone No<span class="required-hash"></span></label>
                                <input id="phone" type="tel" class="sh_dec form-control" name="phone" placeholder="Enter Phone number" value="">
                            </div>
                        </div>
                        {{-- STATUS --}}
                        <div class="col-12 col-md-6">
                            <label class="sh_dec">Select Status <span class="required-hash">*</span></label>
                            <select class="form-select" name="status">
                                <option value="">Select</option>
                                <option value="Active">Active</option>
                                <option value="Disabled">Disabled</option>
                                <option value="Lockout">Lockout</option>
                            </select>
                        </div>

                        {{-- ROLE --}}
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec new-role-drop" for="role">Select Role<span class="required-hash">*</span></label>
                                <select class="form-select select-multiple sh_dec" multiple name="role[]">

                                    @foreach($role as $value)
                                    <option class="sh_dec" value="{{$value->name}}" {{ (isset($data->roles) && in_array($value->name,
                                        $data->roles->pluck('name')->toArray())) ? 'selected' : '' }}>{{ $value->name
                                        }}</option>
                                    @endforeach
                                </select>
                                <div class="error" id="role_error"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Approve</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- end modal -->
@endsection

@section('script')
<script>
    const csrf = $('meta[name="csrf-token"]').attr('content');
    var ModuleBaseUrl = "{{ $module_base_url }}/";
    var DataTableUrl = ModuleBaseUrl + "datatable";

    function ajaxRequest(params) {
        $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
            $('.fixed-table-body .fixed-table-loading').removeClass('open');
            params.success(res)
        })
    }

    $(document).on('click', '.approve_btn', function () {

        let form = document.getElementById('approveForm');

        // Full reset
        form.reset();

        // Clear errors
        $('#ApproveModal .error').html('');
        $('#ApproveModal .validation-error').html('');
        $('#ApproveModal .is-invalid').removeClass('is-invalid');

        // Set values after reset
        $('#request_id').val($(this).data('id'));
        $('#ApproveModal input[name="name"]').val($(this).data('name'));
        $('#ApproveModal input[name="email"]').val($(this).data('email'));

        $('#ApproveModal').modal('show');
    });


    

    $('#approveForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: "{{ url('admin/user-rights/approve') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function () {
                $('#ApproveModal').modal('hide');
                $('#bstable').bootstrapTable('refresh');
            },
            error: function (response) {
                show_errors(response.responseJSON.errors);
            }
        });
    });
  

    $(document).on("click", ".reject_btn", function () {

        let id = $(this).data("id");

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to reject this reward update request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reject it!',
            cancelButtonText: 'No, cancel',
            reverseButtons: true
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ url('admin/user-rights') }}/" + id + "/reject",
                    type: "POST",
                    data: {
                        _token: csrf
                    },
                    success: function (response) {
                        Swal.fire( 'Rejected!',response.message, 'success');
                        $('#bstable').bootstrapTable('refresh');
                    },
                    error: function (xhr) {
                        Swal.fire('Error',xhr.responseJSON?.message || 'Something went wrong.','error' );
                    }
                });
            }
        });
    });

</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection