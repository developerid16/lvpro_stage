@extends('layouts.master-layouts')

@section('title') Content Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin
 @endslot
@slot('title') Content Management @endslot
@endcomponent
 


<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Contact Us</h4>

    </div>--}}
    <form action="" id="form">
        <div class="card-body">


            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-solid fa-envelope"></i></div>
                        <input type="email" class="form-control sh_dec" placeholder="Email" name="email"
                            value="{{$data['email']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-solid fa-phone"></i></div>
                        <input type="tel" class="form-control sh_dec" placeholder="Phone" name="phone"
                            value="{{$data['phone']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-solid fa-clock"></i></div>
                        <input type="text" class="form-control sh_dec" placeholder="Timing" name="operation_hours"
                            value="{{$data['operation_hours']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-solid fa-location-pin"></i></div>
                        <input type="text" class="form-control sh_dec" placeholder="Location" name="location"
                            value="{{$data['location']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-brands fa-facebook"></i></div>
                        <input type="url" class="form-control sh_dec" placeholder="Facebook Link" name="facebook"
                            value="{{$data['facebook']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-brands fa-square-instagram"></i></div>
                        <input type="url" class="form-control sh_dec" placeholder="Instagram Link" name="instagram"
                            value="{{$data['instagram']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text"><i class="fa-brands fa-whatsapp"></i></div>
                        <input type="tel" class="form-control sh_dec" placeholder="WhatsApp Number" name="WhatsApp"
                            value="{{$data['WhatsApp']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <div class="input-group-text">xiao hong shu</div>
                        <input type="url" class="form-control sh_dec" placeholder="xiao hong shu Link" name="xiaohongshu"
                            value="{{$data['xiaohongshu']}}" required>
                    </div>
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
<script src='{{URL::asset("build/libs/tinymce/tinymce.min.js")}}'></script>
<script>
    var ModuleBaseUrl = "{{route('admin.app-content-management.store') }}";
     

  
        $(document).ready(function () {
    
   

        $(document).on("submit","#form",function (e) {
            e.preventDefault()
            const btn = $('.save-btn');
            btn.attr("disabled", true);
            show_message("success","Please wait...");
              var form_data = new FormData($('#form')[0]);
             
            $.ajax({
                url: ModuleBaseUrl,
                headers : {
                    'X-CSRF-Token' : "{{ csrf_token() }}",
                },
                type:"POST",
                data: form_data,
                processData: false,
                contentType: false,
                success:function(response){
                    show_message(response.status,response.message);
                      
            btn.attr("disabled", false);
                },
                error: function(response) {
            btn.attr("disabled", false);
                    console.log("response",response);
                 }
            });
        });

        
    });
</script>
@endsection