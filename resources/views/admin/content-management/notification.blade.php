@extends('layouts.master-layouts')

@section('title') Notification Setting @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('title') Notification Setting @endslot
@endcomponent


<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Notification Setting</h4>

    </div>--}}
    <form action="" id="form">
        <div class="card-body">
        <div class="row">
            <h6 class="sh_sub_title mb-3">1st Notification Configuration</h6>

            </div>


            <div class="row">
                <h6 class="sh_sub_title">Email Notification</h6>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Email (APH Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="sh_dec form-control" placeholder=""
                            name="email_aph_expiry_noti_day" value="{{$data['email_aph_expiry_noti_day']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Email (Reward Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder=""
                            name="email_reward_expiry_noti_day" value="{{$data['email_reward_expiry_noti_day']}}"
                            required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Email (Keys Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder=""
                            name="email_keys_expiry_noti_day" value="{{$data['email_keys_expiry_noti_day']}}" required>
                    </div>
                </div>


            </div>
            <div class="row">
                <h6 class="sh_sub_title">SMS Notification</h6>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">SMS (APH Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="aph_expiry_noti_day"
                            value="{{$data['aph_expiry_noti_day']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">SMS (Reward Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="reward_expiry_noti_day"
                            value="{{$data['reward_expiry_noti_day']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">SMS (Keys Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="keys_expiry_noti_day"
                            value="{{$data['keys_expiry_noti_day']}}" required>
                    </div>
                </div>


            </div>
            <div class="row">
                <h6 class="sh_sub_title">Push Notification</h6>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Push (APH Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="push_aph_expiry_noti_day"
                            value="{{$data['push_aph_expiry_noti_day']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Push (Reward Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="push_reward_expiry_noti_day"
                            value="{{$data['push_reward_expiry_noti_day']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Push (Keys Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="push_keys_expiry_noti_day"
                            value="{{$data['push_keys_expiry_noti_day']}}" required>
                    </div>
                </div>


            </div>
            <hr class="dashed ">

            <div class="row">
            <h6 class="sh_sub_title mb-3">2nd Notification Configuration</h6>

            </div>

            <div class="row">
                <h6 class="sh_sub_title">Email Notification</h6>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Email (APH Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="sh_dec form-control" placeholder=""
                            name="email_aph_expiry_noti_day_two" value="{{$data['email_aph_expiry_noti_day_two']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Email (Reward Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder=""
                            name="email_reward_expiry_noti_day_two" value="{{$data['email_reward_expiry_noti_day_two']}}"
                            required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Email (Keys Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder=""
                            name="email_keys_expiry_noti_day_two" value="{{$data['email_keys_expiry_noti_day_two']}}" required>
                    </div>
                </div>


            </div>
            <div class="row">
                <h6 class="sh_sub_title">SMS Notification</h6>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">SMS (APH Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="aph_expiry_noti_day_two"
                            value="{{$data['aph_expiry_noti_day_two']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">SMS (Reward Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="reward_expiry_noti_day_two"
                            value="{{$data['reward_expiry_noti_day_two']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">SMS (Keys Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="keys_expiry_noti_day_two"
                            value="{{$data['keys_expiry_noti_day_two']}}" required>
                    </div>
                </div>


            </div>

            <div class="row">
                <h6 class="sh_sub_title">Push Notification</h6>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Push (APH Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="push_aph_expiry_noti_day_two"
                            value="{{$data['push_aph_expiry_noti_day_two']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Push (Reward Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="push_reward_expiry_noti_day_two"
                            value="{{$data['push_reward_expiry_noti_day_two']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Push (Keys Expiry)</label>
                    <div class="input-group">
                        <div class="input-group-text sh_dec">Days</div>
                        <input type="number" min="0" class="form-control sh_dec" placeholder="" name="push_keys_expiry_noti_day_two"
                            value="{{$data['push_keys_expiry_noti_day_two']}}" required>
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
<script>
    var ModuleBaseUrl = "{{route('admin.notification-setting.store') }}";
     

  
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