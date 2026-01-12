@extends('layouts.master-layouts')

@section('title') Campaign Voucher Assign @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Campaign Voucher Assign @endslot
@endcomponent




<div class="row">

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold sh_sub_title">Reward Information</h5>
                <div class="table-responsive">
                    <table class="table sh_table">
                        <tbody>
                            <tr>
                                <th scope="col">Code</th>
                                <td scope="col">{{$data->code}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Name</th>
                                <td>{{$data->name}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Quantity</th>
                                <td>{{$data->quantity}}</td>
                            </tr>
                            <tr>
                                @php
                                $duration = $data->start_date->format(config('safra.date-format'));

                                if ($data->end_date) {
                                $duration .= ' to ' . $data->end_date->format(config('safra.date-format'));
                                } else {
                                $duration .= " - No Expiry";
                                }
                                @endphp
                                <th scope="row">Duration</th>
                                <td>{{$duration}}</td>
                            </tr>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold sh_sub_title">Assign User or Group</h5>

                <form action="{{url('admin/campaign-voucher-assign/') . '/' . $data->id}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 ">

                            <div class="mt-4 ">
                                <h5 class="font-size-14 mb-4">Please Select User or Group</h5>
                                <br>
                                <div class="d-flex">

                                    <div class="form-check me-3 ">
                                        <input class="form-check-input" type="radio" name="type" id="type1" value="user" required>
                                        <label class="form-check-label" for="type1">
                                            User
                                        </label>
                                    </div>
                                    <div class="form-check me-3 ">
                                        <input class="form-check-input" type="radio" name="type" id="type2" value="group" required>
                                        <label class="form-check-label" for="type2">
                                            Group
                                        </label>

                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type" id="type3" value="csv" required>
                                        <label class="form-check-label" for="type3">
                                            CSV
                                        </label>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-3" id="group-div" style="display: none">
                            <div class="mb-3">
                                <label class="sh_dec " for="answer">Group<span class="required-hash">*</span></label>
                                <select class="sh_dec form-control " id="group" name="group[]" multiple>
                                    @foreach ($group as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 mt-3" id="user-div" style="display: none">
                            <div class="mb-3">
                                <label class="sh_dec " for="answer">Users<span class="required-hash">*</span></label>
                                <select class="sh_dec form-control " id="users" name="users[]" multiple>

                                </select>
                            </div>
                        </div>
                        <div class="col-12 mt-3" id="csv-div" style="display: none">
                            <div class="mb-3">
                                <label class="sh_dec " for="answer">CSV File<span class="required-hash">*</span> <a href="{{url('admin/download-demo-campaign-voucher-assign')}}" class="text-danger" target="_blank">Demo CSV</a> </label>
                                <input type="file" id="file" name="csv" accept=".csv" class="form-control">
                                <span>If you upload CSV file and user E-Mail is not found that will be ignored.</span>

                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="sh_dec" for="status"> Remark </label>
                            <textarea name="reason" id="reason" class="sh_dec form-control " cols="30" rows="10" spellcheck="false"></textarea>
                        </div>
                        @if(count($errors) > 0)
                        <div class="p-1">
                            @foreach($errors->all() as $error)
                            <div class="alert alert-warning alert-danger fade show" role="alert">{{$error}} </div>
                            @endforeach
                        </div>
                        @endif
                        @if(Session::has('message'))
                        <div class="col-12">
                            <div class="alert alert-warning alert-success fade show">{{ Session::get('message') }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="col-12 mt-4">
                            <button class="  btn btn-info waves-effect waves-light" type="reset">Reset</button>
                            <button class="sh_btn btn btn-primary waves-effect waves-light" type="submit">Submit</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>


    </div>

    <div class="col-12">
        <div class="card mt-4">

        <div class="card-body">
        <h5 class="fw-semibold sh_sub_title">Reward Information</h5>
        <table class="sh_table table table-bordered">
            <thead>
                <tr>

                    <th>Log</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>
                        {!!$log->log !!}

                    </td>
                    <td>
                        {{$log->created_at->format(config('safra.date-format' ) . " g:i:s a") }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        </div>
    </div>


</div>



@endsection

@section('script')
<script>
    $(document).ready(function() {

        $('input[type=radio][name=type]').change(function() {
            if (this.value == 'user') {
                // ...
                $('#group-div').hide();
                $('#user-div').show();
                $('#csv-div').hide();
            } else if (this.value == 'group') {
                // ...
                $('#user-div').hide();
                $('#group-div').show();
                $('#csv-div').hide();
            } else if (this.value == 'csv') {
                // ...
                $('#user-div').hide();
                $('#group-div').hide();
                $('#csv-div').show();
            }
        });
        $("#users").select2({
            allowClear: true,
            placeholder: "Select user",
            ajax: {
                url: "{{url('admin/user/search')}}",
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchTerm: params.term // search term
                    };
                },
                processResults: function(response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        });
        $("#group").select2({
            allowClear: true,

        });
    });
</script>
@endsection