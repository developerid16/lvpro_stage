@extends('layouts.master-layouts')

@section('title') Update Customer @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Customer Management @endslot
@slot('li_1_link') {{url('admin/app-user')}} @endslot
@slot('title') Update Customer @endslot
@endcomponent
<style>
    #status {
        width: 100% !important;
        position: relative !important;
        margin: 0px !important;
        left: 0px;
        top: 0px
    }

    input:read-only {
        background-color: #bab8b8;
    }
</style>
<div class="card">

    <form action="{{route('admin.app-user.update',[$data->id])}}" method="post">
        <div class="card-body">
            <div class="row">

                @method('PUT')

                <div class="col-12">

                    @csrf

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">Name<span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name"
                                    placeholder="Enter name" value="{{ $data->name ?? '' }}" required>
                            </div>
                            @error('name')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">Email</label>
                                <input id="email" type="email" class="sh_dec form-control" name="email"
                                    placeholder="Enter email" value="{{ $data->email ?? '' }}" required>
                            </div>
                            @error('email')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">Phone Code</label>
                                <input id="country_code" type="tel" class="sh_dec form-control" name="country_code"
                                    placeholder="Enter country code" value="{{ $data->country_code ?? '' }}" required>

                            </div>
                            @error('country_code')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-12 col-md-7">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">Phone Number</label>
                                <input id="phone_number" type="number" class="sh_dec form-control" name="phone_number"
                                    placeholder="Enter phone number" value="{{ $data->phone_number ?? '' }}" required>

                            </div>
                            @error('phone_number')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">Date Of Birth</label>
                                <input id="date_of_birth" type="text" class="sh_dec form-control" name="date_of_birth"
                                    placeholder="Enter date of birth"
                                    value="{{$data->date_of_birth ?  $data->date_of_birth->format('Y-m-d') : '' }}" required>
                            </div>
                            @error('date_of_birth')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>


                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="status">Gender <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select " name="gender" id="gender" required>
                                    <option class="sh_dec" value="Male" {{ (isset($data->gender) && $data->gender ==
                                        'Male') ?
                                        'selected' : '' }} >Male</option>
                                    <option class="sh_dec" value="Female" {{ (isset($data->gender) && $data->gender ==
                                        'Female')
                                        ?
                                        'selected' : '' }} >Female</option>
                                    <option class="sh_dec" value="Other" {{ (isset($data->gender) && $data->gender ==
                                        'Other') ? 'selected' : '' }} >Other</option>
                                </select>
                                <div class="error sh_dec_s" id="gender_error"></div>
                            </div>
                            @error('gender')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">Aphid === {{ $data->user_type }}<span
                                        class="required-hash">*</span></label>
                                <input id="unique_id" type="text" class="sh_dec form-control" name="unique_id"
                                    placeholder="" value="{{ $data->unique_id ?? '' }}" required
                                    @readonly($data->user_type === 'Aircrew')>
                            </div>
                            @error('unique_id')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        @if ($data->user_type === 'Airport Pass Holder')
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">Expiry Date</label>
                                <input id="expiry_date" type="date" class="sh_dec form-control" name="expiry_date"
                                    value="{{$data->expiry_date ?  $data->expiry_date->format('Y-m-d') : '' }}">
                            </div>
                            @error('date')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        @else
                        <div class="mb-3">
                            <label class="sh_dec" for="title">Aircrew Unique<span class="required-hash">*</span></label>
                            <input id="aircrew_unique" type="text" class="sh_dec form-control" name="aircrew_unique"
                                placeholder="Enter aircrew unique number" value="{{ $data->aircrew_unique ?? '' }}"
                                required>
                            @error('aircrew_unique')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        @endif

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                {{-- 'Active','Inactive','Blacklist','Expired','Awaiting Activation' --}}
                                <label class="sh_dec" for="status">Status <span class="required-hash">*</span></label>
                                <select class="sh_dec form-select " name="status" id="status-select" required
                                    onchange="onStatusChange(event)">
                                    <option class="sh_dec" value="Active" @selected(old('status',$data->status) ===
                                        'Active')
                                        >Active
                                    </option>
                                    <option class="sh_dec" value="Inactive" @selected(old('status',$data->status) ===
                                        'Inactive')
                                        >Inactive</option>
                                    <option class="sh_dec" value="Blacklist" @selected(old('status',$data->status) ===
                                        'Blacklist')
                                        >Blacklist</option>
                                    <option class="sh_dec" value="Expired" @selected(old('status',$data->status) ===
                                        'Expired')
                                        >Expired</option>
                                    <option class="sh_dec" value="Awaiting Activation" @selected(old('status',$data->
                                        status)
                                        ===
                                        'Awaiting Activation') >Awaiting Activation</option>
                                </select>
                                @error('status')
                                <p class="text-danger">{{$message}}</p>
                                @enderror
                            </div>
                        </div>


                        <div class="col-12 col-md-12" style="display:{{old('status',$data->status) ===
                                        'Blacklist' || old('status',$data->status) ===
                                        'Inactive' ? 'block' :'none'}}" id="blacklist-reason">
                            <div class="mb-3">
                                <label class="sh_dec" for="title"> <span id="reason-text">
                                        {{old('status',$data->status)}} </span> Reason<span
                                        class="required-hash">*</span></label>
                                <input id="blacklist_reason" type="text" class="sh_dec form-control"
                                    name="blacklist_reason" placeholder="Enter  reason"
                                    value="{{ $data->blacklist_reason  }}" maxlength="500">
                            </div>
                            <p class="m-0">Only Applicable if customer status is blacklist or Inactive.</p>
                            @error('blacklist_reason')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>

                        <div class="col-4">
                            <label class="sh_dec" for="company_id">Company </label>
                            <select class="sh_dec form-select select2" name="company_id" id="company_id">
                                <option class="sh_dec" value="">Remove Selection
                                </option>
                                @foreach ($company as $item)
                                <option class="sh_dec" value="{{$item->id}}" @selected($data->company_id == $item->id)
                                    >{{$item->name . ' - ' . $item->code}}
                                </option>
                                @endforeach

                            </select>
                            @error('company_id')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-4">
                            <label class="sh_dec" for="c_code">Company Name</label>
                            <input id="c_name" type="text" class="sh_dec form-control" name="c_name"
                                placeholder="Enter company name" value="{{ $data->c_name ?? '' }}">
                            @error('c_name')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-4">
                            <label class="sh_dec" for="c_code">Company Code</label>
                            <input id="c_code" type="text" class="sh_dec form-control" name="c_code"
                                placeholder="Enter company code" value="{{ $data->c_code ?? '' }}">
                            @error('c_code')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6 mt-3">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">New Password</label>
                                <input id="password" type="text" class="sh_dec form-control" name="password">
                            </div>
                            @error('password')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6 mt-3">
                            <div class="mb-3">
                                <label class="sh_dec" for="title"> <span id="reason-text">
                                        Password Reason </label>
                                <input id="password_reason" type="text" class="sh_dec form-control"
                                    name="password_reason" placeholder="Enter  reason"
                                    value="{{ $data->password_reason ?? '' }}" maxlength="500">
                            </div>
                            <p class="m-0">Only Applicable if you are changing the password.</p>
                            @error('password_reason')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save </button>
            <button type="reset" class="btn btn-info">Reset </button>
        </div>
    </form>
</div>

@endsection

<script>
    function onStatusChange(e){
        var optionSelected = $('#status-select');
        console.log('optionSelected',optionSelected);
        console.log('optionSelected',optionSelected.val());
     var valueSelected  = optionSelected.val();
     $('#reason-text').text(valueSelected)
     if(valueSelected === 'Blacklist' || valueSelected === 'Inactive'){
        $('#blacklist-reason').show()
        return
    }
    $('#blacklist-reason').hide()
    }
</script>