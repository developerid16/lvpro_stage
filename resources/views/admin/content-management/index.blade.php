@extends('layouts.master-layouts')

@section('title') Content Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Content Management @endslot
@endcomponent



<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Content Management</h4>

    </div>--}}
    <div class="card-body pt-0">




        <!-- Nav tabs -->
        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">

            <li class="nav-item" role="presentation">
                <a class="nav-link active" data-bs-toggle="tab" href="#messages1" role="tab" aria-selected="true">
                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                    <span class="d-none d-sm-block sh_dec_b">Terms & Conditions </span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab" aria-selected="false" tabindex="-1">
                    <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                    <span class="d-none d-sm-block sh_dec_b">PDPA</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#LegalPrivacyPolicy" role="tab" aria-selected="false" tabindex="-1">
                    <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                    <span class="d-none d-sm-block sh_dec_b">Legal Privacy Policy</span>
                </a>
            </li>
            <!-- <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#milestone_reward" role="tab" aria-selected="false"
                    tabindex="-1">
                    <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                    <span class="d-none d-sm-block sh_dec_b">Milestone Reward</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#shilla_reward" role="tab" aria-selected="false"
                    tabindex="-1">
                    <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                    <span class="d-none d-sm-block sh_dec_b">Shilla Access Duty Free</span>
                </a>
            </li> -->
        </ul>

        <!-- Tab panes -->
        <div class="tab-content p-3 text-muted">

            <div class="min-height-tabs tab-pane active show" id="messages1" role="tabpanel">
                <textarea class="elm1 sh_dec" name="terms">
                {{$data['terms']}}
                </textarea>
                <button class="sh_btn btn btn-primary mt-3 save-btn" data-content="terms"><i class="mdi mdi-file"></i>
                    Save</button>
            </div>
            <div class="min-height-tabs tab-pane" id="settings1" role="tabpanel">
                <textarea class="elm1 sh_dec" name="pdpa">
                {{$data['pdpa']}}
                </textarea>
                <button class="sh_btn btn btn-primary mt-3 save-btn" data-content="pdpa"><i class="mdi mdi-file"></i>
                    Save</button>
            </div>
            <div class="min-height-tabs tab-pane" id="LegalPrivacyPolicy" role="tabpanel">
                <textarea class="elm1 sh_dec" name="LegalPrivacyPolicy">
                {{$data['LegalPrivacyPolicy']}}
                </textarea>
                <button class="sh_btn btn btn-primary mt-3 save-btn" data-content="LegalPrivacyPolicy"><i class="mdi mdi-file"></i>
                    Save</button>
            </div>
            <div class="min-height-tabs tab-pane" id="milestone_reward" role="tabpanel">
                <textarea class="elm1 sh_dec" name="milestone_reward_page">
                {{$data['milestone_reward_page']}}
                </textarea>
                <button class="sh_btn btn btn-primary mt-3 save-btn" data-content="milestone_reward_page"><i class="mdi mdi-file"></i>
                    Save</button>
            </div>
            <div class="min-height-tabs tab-pane" id="shilla_reward" role="tabpanel">
                <textarea class="elm1 sh_dec" name="shilla_intro">
                {{$data['shilla_intro']}}
                </textarea>
                <button class="sh_btn btn btn-primary mt-3 save-btn" data-content="shilla_intro"><i class="mdi mdi-file"></i>
                    Save</button>
            </div>


        </div>
    </div>
</div>


@endsection

@section('script')
<script src='{{URL::asset("build/libs/tinymce/tinymce.min.js")}}'></script>
<script>
    var ModuleBaseUrl = "{{route('admin.content-management.store') }}";



    $(document).ready(function() {
        if ($(".elm1").length > 0) {
            tinymce.init({
                selector: "textarea.elm1",
                height: 500,
                images_upload_url: '{{url("admin/image-upload-editor")}}',
                images_upload_base_path: "{{asset('images')}}/",
                plugins: [
                    "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
                style_formats: [{
                        title: 'Bold text',
                        inline: 'b'
                    },
                    {
                        title: 'Red text',
                        inline: 'span',
                        styles: {
                            color: '#ff0000'
                        }
                    },
                    {
                        title: 'Red header',
                        block: 'h1',
                        styles: {
                            color: '#ff0000'
                        }
                    },
                    {
                        title: 'Example 1',
                        inline: 'span',
                        classes: 'example1'
                    },
                    {
                        title: 'Example 2',
                        inline: 'span',
                        classes: 'example2'
                    },
                    {
                        title: 'Table styles'
                    },
                    {
                        title: 'Table row 1',
                        selector: 'tr',
                        classes: 'tablerow1'
                    }
                ]
            });
        }


        $(document).on("click", ".save-btn", function(e) {
            const btn = $(this);
            btn.attr("disabled", true);
            show_message("success", "Please wait...");
            const content = tinyMCE.activeEditor.getContent();
            var form_data = new FormData();
            form_data.append("value", content);
            form_data.append("name", btn.attr('data-content'));
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