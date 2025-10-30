@extends('layouts.master-layouts')

@section('title') Learn More Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Learn More Page @endslot
@endcomponent

<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Learn More Management</h4>

    </div>--}}

    <form action="{{url('admin/learn-more-page')}}" id="form" enctype="multipart/form-data" method="post">
        @csrf
        <div class="card-body">

            {{-- Evergreen Info , Milestone Reward--}}
            <div class="row border-bottom">
                {{-- <div class="col-md-6">
                    <div class="mb-3">
                        <label class="sh_dec form-label">Evergreen Info</label>
                        <div>
                            <textarea class="sh_dec form-control" rows="3" spellcheck="false" name="evergreen_info">{{old('evergreen_info',$content_data['evergreen_info'])}}</textarea>
                        </div>
                        @error('evergreen_info')
                        <span class="sh_dec_s text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div> --}}
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="sh_dec form-label">Learn More Page</label>
                        <div>
                            <textarea required="" class="sh_dec form-control" rows="3" spellcheck="false" name="milestone_reward" style="min-height:200px">{{old('milestone_reward',$content_data['milestone_reward'])}}</textarea>
                        </div>
                        @error('milestone_reward')
                        <span class="sh_dec_s text-danger">{{$message}}</span>
                        @enderror
                    </div>

                </div>

            </div>

            {{-- @foreach ($tier_data as $tier)


            <div class="row border-bottom my-3">
                <div class="col-12">
                    <h4 class="sh_sub_title card-title">{{$tier['name']}} Tier</h4>
                </div>
                <div class="col-md-3">
                    <div class="">
                        <label class="sh_dec form-label">Image</label>
                        <input class="sh_dec form-control" type="file" name="image_{{$tier->id}}" accept="image/*">
                    </div>
                    @error("image_$tier->id")
                    <span class="text-danger sh_dec_s">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <a href='{{asset("images/$tier->image")}}' data-lightbox='set-10'>
                        <img src='{{asset("images/$tier->image")}}' alt="" srcset="" height="100" width="100">
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="mb-3">
                        <label class="form-label sh_dec">Information</label>
                        <div>
                            <textarea required="" class="sh_dec form-control" rows="3" spellcheck="false" name="detail_{{$tier->id}}">{{old("detail_$tier->id",$tier->detail)}}</textarea>
                        </div>
                        @error("detail_$tier->id")
                        <span class="text-danger sh_dec_s">{{$message}}</span>
                        @enderror
                    </div>
                </div>
            </div>
            @endforeach --}}
        </div>
        <div class="card-footer">
            <button class="btn btn-primary mt-3 save-btn sh_btn" type="submit"><i class="mdi mdi-file"></i> Save</button>
        </div>
    </form>
</div>

@endsection