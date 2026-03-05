@extends('layouts.master-layouts')

@section('title')
    CSO Issuance (Free)
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
            CSO Issuance (Free)
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
                            <th data-field="quantity" data-filter-control="input" data-sortable="true">Total</th>
                            <th data-field="redeemed">Redeemed</th>
                            <th data-field="duration">Duration</th>
                            <th data-field="image">Image</th>
                            <th data-field="cso_method">Push Method</th>
                            <th data-field="is_draft">Is Draft</th>
                            <th data-field="created_at">Created On</th>

                            <th class="text-center" data-field="action" data-searchable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>


       <!--Push member voucher-->
    <div class="modal fade" id="AddMemberVoucher" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">   
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="sh_sub_title modal-title">Push Voucher By Member ID</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto;  max-height: 800px;">
                    <form enctype="multipart/form-data" class="z-index-1" method="POST" action="{{ url('admin/cso-issuance-free/push-member-voucher') }}" id="member_voucher">
                        @csrf

                        <input type="hidden" name="reward_id" id="hiddenRewardId">

                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="how_to_use">Push Voucher <span class="required-hash">*</span></label>
                                    <textarea id="push_voucher" type="text" class="sh_dec form-control" name="push_voucher" placeholder="Push Voucher" readonly></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="reward_type">Import Member ID <span class="required-hash">*</span></label>   
                                    <input id="memberId" type="file" class="form-control" name="memberId" accept=".xlsx,.xls">
                                    <div class="mt-1">
                                        <label class="small text-muted">
                                            Download demo file:
                                            <a href="{{ asset('demo-push-voucher.xlsx') }}" download class="text-primary fw-bold">
                                                Click here
                                            </a>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec">Attached Voucher<span class="required-hash">*</span></label>                                  
                                    <input type="text" class="sh_dec form-control readonly" id="rewardNameDisplay" readonly>
                                </div>
                            </div>


                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="sh_dec">Method<span class="required-hash">*</span></label>
                                    <input type="text" class="sh_dec form-control readonly" value="Push to Wallet" readonly>
                                </div>
                            </div>

                        <div id="form_error" class="text-danger d-none"></div>

                        <div class="row">
                            <div class="col-6 mt-3 d-grid">
                                <button type="button" class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" data-bs-dismiss="modal">
                                    Back
                                </button>
                            </div>  
                            <div class="col-6 mt-3 d-grid">
                                <button class="sh_btn btn btn-primary waves-effect waves-light" type="submit">Send Push Voucher</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')

    <script>
        var ModuleBaseUrl = "{{ $module_base_url }}/";
        var DataTableUrl = ModuleBaseUrl + "datatable";
        var digitalMerChants = [];

        function ajaxRequest(params) {
            $.get(DataTableUrl + '?' + $.param(params.data)).then(function(res) {
                $('.fixed-table-body .fixed-table-loading').removeClass('open');
                params.success(res)
            })
        }  
       
        $(document).on('click', '.push_member_btn', function () {

            let rewardId   = $(this).data('id');
            let rewardName = $(this).data('name');
            $('#hiddenRewardId').val(rewardId);
            $('#rewardNameDisplay').val(rewardName);
            $('#AddMemberVoucher').modal('show');

            // Set voucher text + value
            $('#rewardNameDisplay')
                .val(rewardName);

        });

        document.getElementById("memberId").addEventListener("change", function (e) {

            let file = e.target.files[0];
            if (!file) return;

            let reader = new FileReader();

            reader.onload = function (event) {

                try {
                    let data = new Uint8Array(event.target.result);
                    let workbook = XLSX.read(data, { type: 'array' });

                    let firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    let rows = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });


                    let ids = [];

                    rows.forEach(row => {
                        let memberId = row[0];

                        if (
                            memberId &&
                            memberId.toString().trim() !== "" &&
                            memberId.toString().toLowerCase() !== "memberid"
                        ) {
                            ids.push(memberId);
                        }
                    });


                    document.getElementById("push_voucher").value = ids.join(", ");

                } catch (err) {
                    console.error("Excel read error:", err);
                }
            };

            reader.readAsArrayBuffer(file);
        });

        $(document).off("submit", "#member_voucher").on("submit", "#member_voucher", function (e) {
            e.preventDefault();

            let $form = $(this);

            // 🔥 HARD LOCK
            if ($form.data("submitting")) return;
            $form.data("submitting", true);

            let $submitBtn = $form.find("button[type='submit']");
            $submitBtn.prop("disabled", true);

            let formData = new FormData(this);

            $.ajax({
                url: this.action,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",

                success: function (res) {

                    $form.data("submitting", false);
                    $submitBtn.prop("disabled", false);

                    $('#form_error').addClass('d-none');
                    $("#AddMemberVoucher").modal('hide');
                    show_message(res.status, res.message);
                    $form[0].reset();
                    $("#push_voucher").val("");
                },

                error: function (response) {
                    let errors = response.responseJSON?.errors || {};
                    $form.data("submitting", false);
                    $submitBtn.prop("disabled", false);
                    show_errors(errors);
                    $(".error").html("");

                    if (response.status === 400) {
                        $('#form_error')
                            .removeClass('d-none')
                            .addClass('alert-danger')
                            .html(response.responseJSON.message);
                    }
                }
            });
        });

        $('#AddMemberVoucher').on('show.bs.modal', function () {

            let form = $('#member_voucher')[0];
            form.reset();

            $('.validation-error').text('');
            $('#memberId').val('');

            // Clear voucher display manually
            $('#reward_id_hidden').val('');
            $('#selectedRewardOption').val('');

            $('#push_voucher').val('');
            $('#form_error').addClass('d-none').text('');
        });

         $('#AddModal').on('shown.bs.modal', function () {
            $('.validation-error').hide();
        });

    </script>
@endsection
