@extends('layouts.master-layouts')

@section('title') Campaign Voucher Group @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Campaign Voucher Group @endslot
@endcomponent


<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">User Management</h4>

    </div>--}}
    <div class="card-body ">
        <form class="z-index-1" method="POST" action="{{route('admin.campaign-voucher-group.store')}}"
            id="{{ (isset($data->id)) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? ''}}">
            @csrf
            @if(isset($data->id)) @method('PATCH') @endif
            <input type="hidden" name="category_id" value="{{$category->id ?? ''}}">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="mb-3">
                        <label class="sh_dec" for="name">Group Name<span class="required-hash">*</span></label>
                        <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter name"
                            value="{{ $data->name ?? '' }}">
                    </div>
                </div>
                <div class="col-12 col-md-12">
                    <div class="mb-3">
                        <label class="sh_dec " for="answer">Users<span class="required-hash">*</span></label>
                        <select class="sh_dec form-control " id="users" name="users[]" multiple>
                        </select>

                    </div>
                </div>

                <div class="col-3 mt-3  ">
                    <button class="  btn btn-info waves-effect waves-light" type="reset">Reset</button>
                    <button class="sh_btn btn btn-primary waves-effect waves-light" type="submit">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {

        $("#users").select2({
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
    });
</script>
@endsection