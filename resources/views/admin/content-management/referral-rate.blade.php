@extends('layouts.master-layouts')

@section('title') Referral Rate @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('title') Referral Rate @endslot
@endcomponent


<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Referral Rate</h4>

    </div>--}}
    <form action="" id="form">
        <div class="card-body">

            <h4 class="card-title mb-4">Under Development </h4>

            <div class="row d-none">
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-solid fa-key"></i></div>
                        <input type="number" min="1" class="sh_dec form-control" placeholder="" name="referral_to_keys" value="{{$data['referral_to_keys']}}" required>
                    </div>
                    <span class="sh_dec_s">No. of keys to be issued for Signup (Referee)</span>

                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-solid fa-key"></i></div>
                        <input type="number" min="1" class="sh_dec form-control" placeholder="" name="referral_by_keys" value="{{$data['referral_by_keys']}}" required>
                    </div>
                    <span class="sh_dec_s">No.of keys to be issued for Referral</span>

                </div>

            </div>



        </div>
        <div class="card-footer">
            <button class="sh_btn btn btn-primary mt-3 save-btn" type="submit"><i class="mdi mdi-file"></i>
                Save</button>
        </div>
    </form>
</div>


@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{route('admin.referral-rate.store') }}";



    $(document).ready(function() {



        $(document).on("submit", "#form", function(e) {
            e.preventDefault()
            const btn = $('.save-btn');
            btn.attr("disabled", true);
            show_message("success", "Please wait...");
            var form_data = new FormData($('#form')[0]);

            $.ajax({
                url: ModuleBaseUrl,
                headers: {
                    'X-CSRF-Token': "{{ csrf_token() }}",
                },
                type: "POST",
                data: form_data,
                processData: false,
                contentType: false,
                success: function(response) {
                    show_message(response.status, response.message);

                    btn.attr("disabled", false);
                },
                error: function(response) {
                    btn.attr("disabled", false);
                    console.log("response", response);
                }
            });
        });


    });
</script>
@endsection