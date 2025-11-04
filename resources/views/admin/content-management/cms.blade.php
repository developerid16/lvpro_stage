@extends('layouts.master-layouts')

@section('title') QR Setting @endsection
@section('content')

    @component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('title') CMS Configuration Setting @endslot
    @endcomponent


    <div class="row">
        <div class="col-md-9">
            <div class="card">
                {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
                    <h4 class="card-title mb-0">Notification Setting</h4>

                </div>--}}
                <form action="" id="form" enctype="multipart/form-data">
                    <div class="card-body">

                        @csrf
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label class="form-label sh_dec">Website Color</label>
                                <div class="input-group">

                                    <input type="color" required name="CMScolor" value="{{$data['CMScolor']}}" id="color">
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label sh_dec">Logo </label>
                                <div class="input-group">
                                    <input type="file" accept=".png" class="sh_dec form-control" name="logo"
                                        onchange="imageChange(event)">
                                </div>
                                <img src="{{asset('build/images/logo-dark.png')}}" alt="" srcset="" height="100"
                                    width="100">
                            </div>  
                            <div class="col-md-3 mb-3">
                                <label class="form-label sh_dec">Favicon Image </label>
                                <div class="input-group">
                                    <input type="file" accept=".png" class="sh_dec form-control" name="favicon"
                                        onchange="imageChange(event)">
                                </div>
                                <img src="{{asset('build/images/favicon.png')}}" alt="" srcset="" height="100"
                                    width="100">
                            </div>  



                        </div>






                    </div>
                    <div class="card-footer">
                        <button class="sh_btn btn btn-primary mt-3 save-btn" type="submit"><i class="mdi mdi-file"></i>
                            Save</button>
                        <button class="btn btn-warning mt-3  " type="button" onclick="previewQRCode()">
                            Click to Preview</button>
                    </div>
                </form>
            </div>
        </div>

    </div>





@endsection

@section('script')
    <script type="text/javascript" src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
    <script>
        var ModuleBaseUrl = "{{route('admin.cms-setting.store') }}";
        var image = "{{asset('images/qr.png')}}"


        $(document).ready(function () {

            $(document).on("submit", "#form", function (e) {
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
                    success: function (response) {
                        show_message(response.status, response.message);
                        window.location.reload();

                        btn.attr("disabled", false);
                    },
                    error: function (response) {
                        btn.attr("disabled", false);
                        console.log("response", response);
                    }
                });
            });




        })
    </script>
@endsection