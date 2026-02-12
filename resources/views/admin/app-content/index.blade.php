@extends('layouts.master-layouts')

@section('title') T&C / FAQ Management @endsection

@section('content')

@component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{ url('/') }} @endslot
    @slot('title') T&C / FAQ Management @endslot
@endcomponent

<div class="card">
    {{-- <div class="card-header bg-white border-bottom">
        <h4 class="card-title mb-0">Upload T&C / FAQ PDF</h4>
    </div> --}}

    <div class="card-body">

        <form id="content_frm" enctype="multipart/form-data">
    @csrf

    <div class="row">

        {{-- ===================== TERMS ===================== --}}
        <div class="col-md-6">
            <div class="card border">
                <div class="card-body">
                    <h5 class="mb-3">Terms & Conditions</h5>

                    <div class="mb-3">
                        <label class="form-label">Upload Terms PDF</label>
                        <input type="file" name="terms_pdf" class="form-control">

                        @if(!empty($terms?->file_path))
                            <small class="text-success mt-2 d-block">
                                Current File:
                                <a href="{{ asset($terms->file_path) }}" target="_blank">
                                    View Terms
                                </a>
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== FAQ ===================== --}}
        <div class="col-md-6">
            <div class="card border">
                <div class="card-body">
                    <h5 class="mb-3">FAQ</h5>

                    <div class="mb-3">
                        <label class="form-label">Upload FAQ PDF</label>
                        <input type="file" name="faq_pdf" class="form-control">

                        @if(!empty($faq?->file_path))
                            <small class="text-success mt-2 d-block">
                                Current File:
                                <a href="{{ asset($faq->file_path) }}" target="_blank">
                                    View FAQ
                                </a>
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary">
            Submit
        </button>
    </div>

</form>

    </div>
</div>

@endsection

@section('script')
<script>

$(document).ready(function () {

    $(document).on("submit", "#content_frm", function(e) {
        e.preventDefault();

        var form_data = new FormData($(this)[0]);

        $.ajax({
            url: "{{ url('admin/app-content') }}",
            headers: {
                'X-CSRF-Token': "{{ csrf_token() }}",
            },
            type: "POST",
            data: form_data,
            processData: false,
            contentType: false,

            success: function(response) {

                if (response.status === 'success') {
                    show_message(response.status, response.message);
                    location.reload();
                } else {
                    show_message(response.status, response.message);
                }

                remove_errors();
            },

            error: function(response) {
                show_errors(response.responseJSON.errors);
            }
        });

    });

});
</script>
@endsection
