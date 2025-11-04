@extends('layouts.master-layouts')

@section('title') Automated Reward @endsection
@section('content')

    @component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('title') Automated Reward  @endslot
    @endcomponent



    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Notification Setting</h4>

    </div>--}}
    <form action="" id="form">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <h6 class="sh_sub_title mb-3">Welcome Reward</h6>
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label sh_dec">Limit per month</label>
                        <div class="input-group">
                            <input type="number" min="0" class="sh_dec form-control" placeholder=""
                                name="welcome_reward_limit" value="{{$data['welcome_reward_limit']}}" required>
                        </div>
                        <span>* 0 for unlimited</span>

                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label sh_dec">Assign Vouchers</label>
                        <select class="form-select" name="welcome_reward_voucher">
                            @foreach ($rewards as $item)
                                <option value="{{$item->id}}" @selected($data['welcome_reward_voucher'] == $item->id)>
                                    {{$item->name}}
                                </option>

                            @endforeach

                        </select>
                        <span>*Limit might be lesser than limit set above</span>

                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label sh_dec">Assign User Group</label>



                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="welcome_reward_group[]" id="welcome_male"
                                value="Male" @checked( in_array('Male',$data['welcome_reward_group']))>
                            <label class="form-check-label" for="welcome_male">
                                Male
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="welcome_reward_group[]" id="welcome_female"
                                value="Female" @checked( in_array('Female',$data['welcome_reward_group']))>
                            <label class="form-check-label" for="welcome_female">
                                Female
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="welcome_reward_group[]" id="welcome_all"
                                value="all" @checked( in_array('all',$data['welcome_reward_group']))>
                            <label class="form-check-label" for="welcome_all">
                                All
                            </label>
                        </div>

                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label sh_dec">Notification</label>
                        <div class="form-check">
                            <input class="form-check-input" name="welcome_reward_email_noti" type="checkbox" value="email"
                                id="welcome_email" @checked($data['welcome_reward_email_noti'] === 'email')>
                            <label class="form-check-label" for="welcome_email">
                                EMail
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" name="welcome_reward_push_noti" type="checkbox" value="push"
                                id="welcome_push" @checked($data['welcome_reward_push_noti'] === 'push')>
                            <label class="form-check-label" for="welcome_push">
                                APP
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" name="welcome_reward_sms_noti" type="checkbox" value="sms"
                                id="welcome_sms" @checked($data['welcome_reward_sms_noti'] === 'sms')>
                            <label class="form-check-label" for="welcome_sms">
                                SMS
                            </label>
                        </div>

                    </div>

                </div>


            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <h6 class="sh_sub_title mb-3">Birthday Reward</h6>
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label sh_dec">Limit per month</label>
                        <div class="input-group">
                            <input type="number" min="0" class="sh_dec form-control" placeholder=""
                                name="birthday_reward_limit" value="{{$data['birthday_reward_limit']}}" required>
                        </div>
                        <span>* 0 for unlimited</span>

                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label sh_dec">Assign Vouchers</label>
                        <select class="form-select" name="birthday_reward_voucher">
                            @foreach ($rewards as $item)
                                <option value="{{$item->id}}" @selected($data['birthday_reward_voucher'] == $item->id)>
                                    {{$item->name}}
                                </option>

                            @endforeach

                        </select>
                        <span>*Limit might be lesser than limit set above</span>

                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label sh_dec">Assign User Group</label>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="birthday_reward_group[]" id="birthday_male"
                                value="Male" @checked( in_array('Male',$data['birthday_reward_group']))>
                            <label class="form-check-label" for="birthday_male">
                                Male
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="birthday_reward_group[]" id="birthday_female"
                                value="Female" @checked( in_array('Female',$data['birthday_reward_group']))>
                            <label class="form-check-label" for="birthday_female">
                                Female
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="birthday_reward_group[]" id="birthday_all"
                                value="all" @checked( in_array('all',$data['birthday_reward_group']))>
                            <label class="form-check-label" for="birthday_all">
                                All
                            </label>
                        </div>

                  

                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label sh_dec">Notification</label>
                        <div class="form-check">
                            <input class="form-check-input" name="birthday_reward_email_noti" type="checkbox" value="email"
                                id="birthday_email" @checked($data['birthday_reward_email_noti'] === 'email')>
                            <label class="form-check-label" for="birthday_email">
                                EMail
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" name="birthday_reward_push_noti" type="checkbox" value="push"
                                id="birthday_push" @checked($data['birthday_reward_push_noti'] === 'push')>
                            <label class="form-check-label" for="birthday_push">
                                APP
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" name="birthday_reward_sms_noti" type="checkbox" value="sms"
                                id="birthday_sms" @checked($data['birthday_reward_sms_noti'] === 'sms')>
                            <label class="form-check-label" for="birthday_sms">
                                SMS
                            </label>
                        </div>

                    </div>

                </div>


            </div>
        </div>

       
        <div class="card-footer mb-3">
            <button class="sh_btn btn btn-primary save-btn" type="submit"><i class="mdi mdi-file"></i>
                Save</button>
        </div>
    </form>
    </div>

 
@endsection

@section('script')
    <script>
        var ModuleBaseUrl = "{{url('admin/automated-reward-update') }}";



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

                        btn.attr("disabled", false);
                    },
                    error: function (response) {
                        btn.attr("disabled", false);
                        console.log("response", response);
                    }
                });
            });


        });
    </script>
@endsection