<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1"
    data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="sh_sub_title modal-title">
                    {{ (isset($data->id)) ? 'Edit' : 'Add' }} {{ $title ?? '' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="javascript:void(0)"
                    id="{{ (isset($data->id)) ? 'edit_frm' : 'add_frm' }}"
                    data-id="{{ $data->id ?? '' }}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif

                    {{-- ===== Row 1: Code / Name / Status ===== --}}
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="code">Code <span class="required-hash">*</span></label>
                                <input id="code" type="text" class="sh_dec form-control" name="code" placeholder="Enter Code"
                                    value="{{ $data->code ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="tier_name">Name <span class="required-hash">*</span></label>
                                <input id="tier_name" type="text" class="sh_dec form-control" name="tier_name" placeholder="Enter Name"
                                    value="{{ $data->tier_name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="status">Status <span class="required-hash">*</span></label>

                                <select class="sh_dec form-select" name="status">
                                    <option value="Active" {{ (isset($data->status) && $data->status == 'Active') ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ (isset($data->status) && $data->status == 'Inactive') ? 'selected' : '' }}>Inactive</option>
                                </select>

                                <div class="sh_dec_s error" id="status_error"></div>
                            </div>
                        </div>

                    </div>

                    <hr class="my-3">

                    {{-- ===== Section: Interest Group / Member Type ===== --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h6 class="sh_sub_title text-primary mb-1">
                                Interest Group / Member Type
                                <span class="required-hash">*</span>
                            </h6>
                            <p class="sh_dec text-muted mb-2" style="font-size:12px;">
                                At least one <strong>Interest Group</strong> or one <strong>Member Type</strong> is required.
                                You can add multiple.
                            </p>

                            {{-- Server-side error for IG/MT --}}
                            <div class="alert alert-danger py-2 px-3 mb-3 d-none" id="ig_or_mt_error" role="alert" style="font-size:13px;">
                                <i class="mdi mdi-alert-circle me-1"></i>
                                <span id="ig_or_mt_error_msg">Please add at least one Interest Group or one Member Type.</span>
                            </div>

                            {{-- Tabs --}}
                            <ul class="nav nav-tabs mb-3" id="tierTypeTabs_{{ $data->id ?? 'new' }}" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="ig-tab-{{ $data->id ?? 'new' }}"
                                        data-bs-toggle="tab"
                                        data-bs-target="#ig-section-{{ $data->id ?? 'new' }}"
                                        type="button" role="tab">
                                        <i class="mdi mdi-account-group me-1"></i> Interest Group
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="mt-tab-{{ $data->id ?? 'new' }}"
                                        data-bs-toggle="tab"
                                        data-bs-target="#mt-section-{{ $data->id ?? 'new' }}"
                                        type="button" role="tab">
                                        <i class="mdi mdi-card-account-details me-1"></i> Member Type
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">

                                {{-- ============ INTEREST GROUP TAB ============ --}}
                                <div class="tab-pane fade show active"
                                    id="ig-section-{{ $data->id ?? 'new' }}" role="tabpanel">

                                    <div class="row align-items-end mb-3">
                                        <div class="col-12 col-md-4">
                                            <label class="sh_dec form-label mb-1">Main Group</label>
                                            <select class="sh_dec form-select ig_main_select" id="ig_main_select_{{ $data->id ?? 'new' }}">
                                                <option value="">-- Select Main Group --</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="sh_dec form-label mb-1">Sub Group</label>
                                            <select class="sh_dec form-select ig_sub_select" id="ig_sub_select_{{ $data->id ?? 'new' }}" disabled>
                                                <option value="">-- Select Main Group First --</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <button type="button" class="btn btn-success w-100 add_ig_btn"
                                                id="add_ig_btn_{{ $data->id ?? 'new' }}"
                                                data-uid="{{ $data->id ?? 'new' }}">
                                                <i class="mdi mdi-plus"></i> Add Interest Group
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Selected IGs --}}
                                    <div class="ig_selected_list" id="ig_list_{{ $data->id ?? 'new' }}">
                                        @if(isset($data->interestGroups) && $data->interestGroups->count() > 0)
                                            @foreach($data->interestGroups as $ig)
                                            <div class="ig-item d-flex align-items-center justify-content-between border rounded p-2 mb-2"
                                                style="background:#eef3ff;"
                                                data-main="{{ $ig->interest_group_main_name }}"
                                                data-sub="{{ $ig->interest_group_name }}">
                                                <div>
                                                    <input type="hidden" name="interest_groups[{{ $loop->index }}][main_name]" value="{{ $ig->interest_group_main_name }}">
                                                    <input type="hidden" name="interest_groups[{{ $loop->index }}][sub_name]"  value="{{ $ig->interest_group_name }}">
                                                    <span class="badge bg-primary me-2">IG</span>
                                                    <strong class="sh_dec">{{ $ig->interest_group_main_name }}</strong>
                                                    <span class="text-muted mx-1">/</span>
                                                    <span class="sh_dec">{{ $ig->interest_group_name }}</span>
                                                    @if(isset($ig->is_active) && $ig->is_active == 0)
                                                        <span class="badge bg-warning text-dark ms-2"
                                                            title="No longer in master API listing">Inactive from API</span>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-ig-btn">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <p class="text-muted sh_dec ig_empty_msg" style="font-size:12px;
                                        {{ (isset($data->interestGroups) && $data->interestGroups->count() > 0) ? 'display:none;' : '' }}">
                                        No interest groups added yet.
                                    </p>

                                </div>{{-- end ig tab --}}

                                {{-- ============ MEMBER TYPE TAB ============ --}}
                                <div class="tab-pane fade"
                                    id="mt-section-{{ $data->id ?? 'new' }}" role="tabpanel">

                                    <div class="row align-items-end mb-3">
                                        <div class="col-12 col-md-8">
                                            <label class="sh_dec form-label mb-1">Membership Type Code</label>
                                            <select class="sh_dec form-select mt_select" id="mt_select_{{ $data->id ?? 'new' }}">
                                                <option value="">-- Select Member Type --</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <button type="button" class="btn btn-success w-100 add_mt_btn"
                                                id="add_mt_btn_{{ $data->id ?? 'new' }}"
                                                data-uid="{{ $data->id ?? 'new' }}">
                                                <i class="mdi mdi-plus"></i> Add Member Type
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Selected MTs --}}
                                    <div class="mt_selected_list" id="mt_list_{{ $data->id ?? 'new' }}">
                                        @if(isset($data->memberTypes) && $data->memberTypes->count() > 0)
                                            @foreach($data->memberTypes as $mt)
                                            <div class="mt-item d-flex align-items-center justify-content-between border rounded p-2 mb-2"
                                                style="background:#efffef;"
                                                data-code="{{ $mt->membership_type_code }}">
                                                <div>
                                                    <input type="hidden" name="member_types[]" value="{{ $mt->membership_type_code }}">
                                                    <span class="badge bg-success me-2">MT</span>
                                                    <strong class="sh_dec">{{ $mt->membership_type_code }}</strong>
                                                    @if(isset($mt->is_active) && $mt->is_active == 0)
                                                        <span class="badge bg-warning text-dark ms-2"
                                                            title="No longer in master API listing">Inactive from API</span>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-mt-btn">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <p class="text-muted sh_dec mt_empty_msg" style="font-size:12px;
                                        {{ (isset($data->memberTypes) && $data->memberTypes->count() > 0) ? 'display:none;' : '' }}">
                                        No member types added yet.
                                    </p>

                                </div>{{-- end mt tab --}}

                            </div>{{-- end tab-content --}}
                        </div>
                    </div>

                    {{-- ===== Buttons ===== --}}
                    <div class="row mt-3">
                        <div class="col-6 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light"
                                type="button" onclick="tierModalReset(this)">Reset</button>
                        </div>
                        <div class="col-6 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light"
                                type="submit">Submit</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var BASE_URL = "{{ url('admin/tiers') }}/";
    var UID      = "{{ $data->id ?? 'new' }}";
    var MODAL_ID = "{{ isset($data->id) ? 'EditModal' : 'AddModal' }}";

    var selectedIGs = [];
    var selectedMTs = [];
    var igIndex     = 0;

    // Pre-fill existing data (edit mode)
    @if(isset($data->interestGroups))
        @foreach($data->interestGroups as $ig)
            selectedIGs.push({ main: @json($ig->interest_group_main_name), sub: @json($ig->interest_group_name) });
        @endforeach
        igIndex = {{ $data->interestGroups->count() }};
    @endif
    @if(isset($data->memberTypes))
        @foreach($data->memberTypes as $mt)
            selectedMTs.push(@json($mt->membership_type_code));
        @endforeach
    @endif

    // ── LOAD MAIN GROUPS ─────────────────────────────────────────
    $.get(BASE_URL + 'get-main-groups', function (res) {
        var $sel = $('#ig_main_select_' + UID);
        $.each(res.data, function (i, name) {
            $sel.append('<option value="' + name + '">' + name + '</option>');
        });
    });

    // ── LOAD SUB GROUPS (direct bind — NOT document.on) ──────────
    $('#ig_main_select_' + UID).on('change', function () {
        var mainName = $(this).val();
        var $subSel  = $('#ig_sub_select_' + UID);
        if (!mainName) {
            $subSel.html('<option value="">-- Select Main Group First --</option>').prop('disabled', true);
            return;
        }
        $.get(BASE_URL + 'get-sub-groups', { main_name: mainName }, function (res) {
            $subSel.html('<option value="">-- Select Sub Group --</option>');
            $.each(res.data, function (i, name) {
                $subSel.append('<option value="' + name + '">' + name + '</option>');
            });
            $subSel.prop('disabled', false);
        });
    });

    // ── LOAD MEMBER TYPES ────────────────────────────────────────
    $.get(BASE_URL + 'get-member-types', function (res) {
        var $sel = $('#mt_select_' + UID);
        $.each(res.data, function (i, code) {
            $sel.append('<option value="' + code + '">' + code + '</option>');
        });
    });

    // ── ADD INTEREST GROUP (direct bind with .off first) ─────────
    // .off('click') ensures no duplicate listeners even if script runs again
    $('#add_ig_btn_' + UID).off('click').on('click', function () {
        var mainName = $('#ig_main_select_' + UID).val();
        var subName  = $('#ig_sub_select_' + UID).val();

        if (!mainName || !subName) {
            Swal.fire({ icon: 'warning', title: 'Please select both Main Group and Sub Group', timer: 2000, showConfirmButton: false });
            return;
        }

        var isDup = selectedIGs.some(function (ig) {
            return ig.main === mainName && ig.sub === subName;
        });
        if (isDup) {
            Swal.fire({ icon: 'info', title: 'Already Added', text: 'This Interest Group is already in the list.', timer: 2000, showConfirmButton: false });
            return;
        }

        selectedIGs.push({ main: mainName, sub: subName });
        hideIgMtError();

        var idx  = igIndex++;
        var html = '<div class="ig-item d-flex align-items-center justify-content-between border rounded p-2 mb-2" style="background:#eef3ff;" '
            + 'data-main="' + mainName + '" data-sub="' + subName + '">'
            + '<div>'
            + '<input type="hidden" name="interest_groups[' + idx + '][main_name]" value="' + mainName + '">'
            + '<input type="hidden" name="interest_groups[' + idx + '][sub_name]"  value="' + subName  + '">'
            + '<span class="badge bg-primary me-2">IG</span>'
            + '<strong class="sh_dec">' + mainName + '</strong>'
            + '<span class="text-muted mx-1">/</span>'
            + '<span class="sh_dec">' + subName + '</span>'
            + '</div>'
            + '<button type="button" class="btn btn-sm btn-outline-danger remove-ig-btn"><i class="mdi mdi-close"></i></button>'
            + '</div>';

        $('#ig_list_' + UID).append(html);
        $('#ig_list_' + UID).closest('.tab-pane').find('.ig_empty_msg').hide();
        $('#ig_main_select_' + UID).val('');
        $('#ig_sub_select_' + UID).html('<option value="">-- Select Main Group First --</option>').prop('disabled', true);
    });

    // ── REMOVE INTEREST GROUP (scoped to list container) ─────────
    $('#ig_list_' + UID).off('click', '.remove-ig-btn').on('click', '.remove-ig-btn', function () {
        var $item    = $(this).closest('.ig-item');
        var mainName = $item.data('main');
        var subName  = $item.data('sub');

        selectedIGs = selectedIGs.filter(function (ig) {
            return !(ig.main === mainName && ig.sub === subName);
        });
        $item.remove();
        rebuildIGIndexes();

        if ($('#ig_list_' + UID + ' .ig-item').length === 0) {
            $('#ig_list_' + UID).closest('.tab-pane').find('.ig_empty_msg').show();
        }
    });

    function rebuildIGIndexes() {
        $('#ig_list_' + UID + ' .ig-item').each(function (i) {
            $(this).find('input[name*="[main_name]"]').attr('name', 'interest_groups[' + i + '][main_name]');
            $(this).find('input[name*="[sub_name]"]').attr('name', 'interest_groups[' + i + '][sub_name]');
        });
        igIndex = $('#ig_list_' + UID + ' .ig-item').length;
    }

    // ── ADD MEMBER TYPE (direct bind with .off first) ─────────────
    $('#add_mt_btn_' + UID).off('click').on('click', function () {
        var code = $('#mt_select_' + UID).val();

        if (!code) {
            Swal.fire({ icon: 'warning', title: 'Please select a Member Type', timer: 2000, showConfirmButton: false });
            return;
        }
        if (selectedMTs.includes(code)) {
            Swal.fire({ icon: 'info', title: 'Already Added', text: 'This Member Type is already in the list.', timer: 2000, showConfirmButton: false });
            return;
        }

        selectedMTs.push(code);
        hideIgMtError();

        var html = '<div class="mt-item d-flex align-items-center justify-content-between border rounded p-2 mb-2" style="background:#efffef;" '
            + 'data-code="' + code + '">'
            + '<div>'
            + '<input type="hidden" name="member_types[]" value="' + code + '">'
            + '<span class="badge bg-success me-2">MT</span>'
            + '<strong class="sh_dec">' + code + '</strong>'
            + '</div>'
            + '<button type="button" class="btn btn-sm btn-outline-danger remove-mt-btn"><i class="mdi mdi-close"></i></button>'
            + '</div>';

        $('#mt_list_' + UID).append(html);
        $('#mt_list_' + UID).closest('.tab-pane').find('.mt_empty_msg').hide();
        $('#mt_select_' + UID).val('');
    });

    // ── REMOVE MEMBER TYPE (scoped to list container) ─────────────
    $('#mt_list_' + UID).off('click', '.remove-mt-btn').on('click', '.remove-mt-btn', function () {
        var $item = $(this).closest('.mt-item');
        var code  = $item.data('code');

        selectedMTs = selectedMTs.filter(function (c) { return c !== code; });
        $item.remove();

        if ($('#mt_list_' + UID + ' .mt-item').length === 0) {
            $('#mt_list_' + UID).closest('.tab-pane').find('.mt_empty_msg').show();
        }
    });

    // ── HELPERS ───────────────────────────────────────────────────
    function showIgMtError(msg) {
        $('#ig_or_mt_error_msg').text(msg);
        $('#ig_or_mt_error').removeClass('d-none');
        $('#ig_or_mt_error')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideIgMtError() {
        $('#ig_or_mt_error').addClass('d-none');
    }

    // ── CLEANUP: remove edit modal HTML when closed ───────────────
    // This prevents stale event listeners from old modal instances
    $('#' + MODAL_ID).on('hidden.bs.modal', function () {
        if (MODAL_ID === 'EditModal') {
            $('#edit_modal_placeholder').empty();
        }
    });

    // ── RESET button ──────────────────────────────────────────────
    window.tierModalReset = function (btn) {
        var $form = $(btn).closest('form');
        $form[0].reset();
        if (typeof remove_errors === 'function') remove_errors();
        hideIgMtError();

        selectedIGs = [];
        selectedMTs = [];
        igIndex     = 0;

        $('#ig_list_' + UID).empty();
        $('#mt_list_' + UID).empty();
        $('.ig_empty_msg').show();
        $('.mt_empty_msg').show();

        $('#ig_main_select_' + UID).val('');
        $('#ig_sub_select_' + UID).html('<option value="">-- Select Main Group First --</option>').prop('disabled', true);
        $('#mt_select_' + UID).val('');
    };

})();
</script>