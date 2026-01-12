@extends('layouts.master-layouts')

@section('title') Customer Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Customer Management @endslot
@endcomponent

<div class="row">
    {{-- <div class="col-xl-4">
        <form action="" method="POST">
            @csrf
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-semibold">Customer Management</h5>

                    <div class="row">


                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label for="name">Name<span class="required-hash">*</span></label>
                                <input id="name" type="text" class="form-control" name="name" placeholder="Enter name"
                                    value="{{ $user->name  }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label for="Email">Email<span class="required-hash">*</span></label>
                                <input id="Email" type="email" class="form-control" name="email"
                                    placeholder="Enter email" value="{{ $user->email  }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label>Mobile Number<span class="required-hash">*</span></label>
                                <input type="tel" class="form-control" name="phone_number"
                                    placeholder="Enter phone number" value="{{ $user->phone_number  }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label for="CustomerType">Customer Type<span class="required-hash">*</span></label>
                                <select name="user_type" id="user_type" class="form-select">
                                    <option value="Aircrew" @selected($user->user_type == 'Aircrew')>Aircrew</option>
                                    <option value="Airport Pass Holder" @selected($user->user_type == 'Airport Pass
                                        Holder')>Airport Pass Holder</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label>APHID/Employment ID<span class="required-hash">*</span></label>
                                <input type="text" class="form-control" name="unique_id"
                                    placeholder="Enter APHID/Employment ID" value="{{ $user->unique_id  }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label>APH expiry date<span class="required-hash">*</span></label>
                                <input type="date" class="form-control" name="expiry_date"
                                    placeholder="Enter phone number" value="{{ $user->expiry_date->format('Y-m-d')  }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label for="CustomerType">Gender<span class="required-hash">*</span></label>
                                <select name="gender" id="gender" class="form-select">
                                    <option value="Male" @selected($user->gender == 'Male')>Male</option>
                                    <option value="Female" @selected($user->gender == 'Female')>Female</option>
                                    <option value="Other" @selected($user->gender == 'Other')>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label>Status<span class="required-hash">*</span></label>
                                <select name="status" id="status" class="form-select">
                                    <option value="Active" @selected($user->status === 'Active')>Active</option>
                                    <option value="Inactive" @selected($user->status === 'Inactive')>Inactive</option>
                                    <option value="Blacklist" @selected($user->status === 'Blacklist')>Blacklist
                                    </option>
                                    <option value="Expired" @selected($user->status === 'Expired')>Expired</option>
                                    <option value="Awaiting Activation" @selected($user->status === 'Awaiting
                                        Activation')>Awaiting Activation</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label for="blacklist_reason">Blacklist Reason<span
                                        class="required-hash">*</span></label>
                                <input id="blacklist_reason" type="text" class="form-control" name="blacklist_reason"
                                    placeholder="Enter blacklist reason" value="{{ $user->blacklist_reason  }}">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" type="submit">Update Information</button>
                </div>
            </div>
        </form>

    </div> --}}
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h5 class="sh_sub_title fw-semibold">Rewards Redemption (Last 365Days)</h5>

                <div class="table-responsive">
                    <table class="sh_table table">
                        <thead>
                            <tr>
                                <th scope="col">Reward</th>
                                <th scope="row">Keys used</th>
                                <th scope="row">Status</th>
                                <th scope="row">Redeemed/Expiry Date</th>
                                <th scope="row">Action</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rewards as $reward)


                            <tr>

                                <td scope="col"> <a class="text-danger"
                                        href="{{url('admin/redemption-reward/'.$reward->id)}}" target="_blank"
                                        rel="noopener noreferrer">{{$reward->reward->name}}</a></td>


                                <td>{{number_format($reward->key_use)}}</td>

                                <td>{{$reward->status}}</td>

                                <td>{{
                                    $reward->status == 'Redeemed' ?
                                    $reward->redeem_date->format(config('safra.date-format')) :
                                    $reward->expiry_date->format(config('safra.date-format')) }}</td>
                                <td>
                                    @if ($reward->status != 'Redeemed')



                                    @if ($reward->status === "Admin Deleted")
                                    <a data-bs-toggle="modal" data-bs-target="#deleteReward" class='edit'
                                        data-id='{{$reward->id}}' onclick="updateIdHidden('delete','{{$reward->id}}')"
                                        title="Restore Reward "><i
                                            class='mdi mdi-restore text-success action-icon font-size-18'></i>
                                    </a>
                                    @else
                                    <a data-bs-toggle="modal" data-bs-target="#deleteReward" class='edit'
                                        data-id='{{$reward->id}}' onclick="updateIdHidden('delete','{{$reward->id}}')"
                                        title="Delete Reward "><i
                                            class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                                    </a>
                                    @endif
                                    @endif


                                    @if ($reward->status != 'Redeemed')

                                    <a data-bs-toggle="modal" data-bs-target="#changeDate" class='edit'
                                        data-id='{{$reward->id}}' onclick="updateIdHidden('expiry','{{$reward->id}}')"
                                        title="Change Expiry Date "><i
                                            class='mdi mdi-update text-primary action-icon font-size-18'></i></a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

            </div>
        </div>


    </div>

</div>
{{-- <div class="row">
    <div class="col-xl-5">
        <div class="card">
            <form action="{{url('admin/keys-credit-debit')}}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{$user->id}}">
                <div class="card-body">
                    <h5 class="fw-semibold sh_sub_title">Keys Entry (Available Key
                        {{number_format($user->available_key)}})</h5>
                    <div class="col-12 col-md-12">
                        <div class="mb-3">
                            <label class="sh_dec">Keys<span class="required-hash">*</span></label>
                            <input type="number" class="sh_dec form-control" name="keys"
                                placeholder="Enter keys" required min="1" max="50000" step="1">
                        </div>
                        @error('keys')

                        <span class="text-danger">{{$message}}</span>

                        @enderror
                    </div>
                    <div class="col-12 col-md-12">
                        <div class="mb-3">
                            <label class="sh_dec">Type<span class="required-hash">*</span></label>
                            <select name="type" id="type" class="sh_dec form-select" required>
                                <option class="sh_dec" value="">Please select type </option>
                                <option class="sh_dec" value="debit"> Credit </option>
                                <option class="sh_dec" value="credit">Debit </option>
                            </select>
                        </div>
                        @error('type')

                        <span class="sh_btn text-danger">{{$message}}</span>

                        @enderror
                    </div>
                    <div class="col-12 col-md-12">
                        <div class="mb-3">
                            <label class="sh_dec">Reason of adjustment (For Admin)<span
                                    class="required-hash">*</span></label>
                            <input type="text" class="sh_dec form-control" name="reason"
                                placeholder="Enter reason of adjustment" required maxlength="50">
                            <span>Max 50 Character limit </span>
                        </div>
                        @error('keys')

                        <span class="text-danger">{{$message}}</span>

                        @enderror
                    </div>
                    <div class="col-12 col-md-12">
                        <div class="mb-3">
                            <label class="sh_dec">Reason of adjustment (For App User)<span
                                    class="required-hash">*</span></label>
                            <input type="text" class="sh_dec form-control" name="app_reason"
                                placeholder="Enter reason of adjustment" required maxlength="50">
                            <span>Max 50 Character limit </span>
                        </div>
                        @error('keys')

                        <span class="text-danger">{{$message}}</span>

                        @enderror
                    </div>

                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <h5 class="sh_sub_title fw-semibold">Keys Adjustment (Last 365Days)</h5>
                <div class="table-responsive">
                    <table class="sh_table table">
                        <thead>
                            <tr>
                                <th scope="col">Type</th>
                                <th scope="row">Keys</th>
                                <th scope="row" width="14%">Date</th>
                                <th scope="row">Note</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($keysData as $key)


                            <tr>

                                <td>
                                    {{$key['type']}}
                                </td>

                                <td>{{number_format($key['keys'])}}</td>

                                <td>{{ $key['date']->format(config('safra.date-format')) }}</td>
                                <td>{{$key['meta_data']}}</td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> --}}


<div class="modal fade" id="changeDate" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sh_sub_title">Change Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="{{ url('admin/reward-redemption-change-date') }}">
                    @csrf
                    <input type="hidden" name="id" id="update-id">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec">Reason of adjustment<span class="required-hash">*</span></label>
                                <input type="text" class="sh_dec form-control" name="reason"
                                    placeholder="Enter reason of adjustment" required maxlength="50">
                                <span>Max 50 Character limit </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div>
                                <label class="sh_dec" for="date">Expiry Date & Time<span
                                        class="required-hash">*</span></label>
                                <input id="date" type="text" required class="sh_dec form-control datetimepicker"
                                    name="date">
                            </div>

                        </div>


                    </div>
                    <div class="row">
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light"
                                type="submit">Update</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteReward" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sh_sub_title">Delete/Restore Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="{{ url('admin/reward-redemption-delete') }}">
                    @csrf

                    <input type="hidden" name="id" id="delete-id">
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec">Reason of adjustment<span class="required-hash">*</span></label>
                                <input type="text" class="sh_dec form-control" name="reason"
                                    placeholder="Enter reason of adjustment" required maxlength="50">
                                <span>Max 50 Character limit </span>
                            </div>
                        </div>




                    </div>
                    <div class="row">
                        <div class="col-12 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light"
                                type="submit">Submit</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="{{ URL::asset('/build/libs/flatpicker/flatpickr.js') }}"></script>
<link rel="stylesheet" href="{{ URL::asset('/build/libs/flatpicker/flatpickr.min.css') }}">
<script>
    $(function() {
        $(".datetimepicker").flatpickr({
        enableTime: true,
        minDate: new Date().fp_incr(1),
        allowInput: true,
        dateFormat: "Y-m-d H:i",
        });
    })
    function updateIdHidden(type,id){
        if(type == 'delete'){
            $('#delete-id').val(id)
            }else{
            $('#update-id').val(id)

        }
    }
</script>
@endsection
