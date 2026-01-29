@extends('layouts.master-layouts')

@section('title') Tier Configuration @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('title') Tier Configuration @endslot
@endcomponent

<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3"
        style="height: 57px;">
        <h4 class="card-title mb-0">Tier Management</h4>

    </div>--}}
    <div class="card-body">

        <div class="row">
            <div class="col-md-3">


                <div class="sidbar_link nav flex-column nav-pills " id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    @foreach ($tier as $t)
                    <a class="nav-link mb-2 {{$loop->first ? 'active' :  ''}}" id="v-pills-{{$t->id}}-tab"
                        data-bs-toggle="pill" href="#v-pills-{{$t->id}}" role="tab" aria-controls="v-pills-{{$t->id}}"
                        aria-selected="{{$loop->first ? 'true' :  'false'}}" tabindex="-1">
                        {{$t->name}}
                    </a>

                    @endforeach
                </div>
            </div>
            <div class="col-md-9 border-start">

                <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                    @foreach ($tier as $t)
                    <div class="tab-pane fade {{$loop->first ? 'active show' :  ''}} border-left"
                        id="v-pills-{{$t->id}}" role="tabpanel" aria-labelledby="v-pills-{{$t->id}}-tab">

                        <form action="" method="post" class="save-btn">
                            @csrf
                            <input type="hidden" name="id" value="{{$t->id}}">
                            @if (!$loop->first)
                            <div class="row border-bottom mb-3 pb-3">


                                <label for="spend_amount">Spend Amount</label>
                                <div class="input-group ">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">$</span>
                                    </div>
                                    <input id="spend_amount" type="text" class="form-control" name="spend_amount"
                                        placeholder="Enter spend amount number" value="{{ $t->spend_amount  }}">
                                </div>
                                <span class="text-muted">User need to spend $ to reach this {{$t->name}}</span>
                            </div>
                            @endif
                            <div class="row mt-2 border-bottom">
                                <div class="col-12">
                                    <h5 class="sh_sub_title text-primary">Multiplier</h5>
                                    <p class="sh_dec card-title-desc 1">Multiplier key amount when ever user spend $1</p>
                                </div>
                                <div class="col-lg-6 d-none">

                                    <label class="sh_dec min-titleh" for="isc_multiplier">ISC / MDC multiplier</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend sh_dec">
                                            <span class="input-group-text">$1 =</span>
                                        </div>
                                        <input type="text" name="isc_multiplier"
                                            placeholder="Enter ISC / MDC multiplier" class="form-control sh_dec"
                                            aria-label="Amount (to the nearest dollar)"
                                            value="{{ $t->isc_multiplier }}">
                                        <div class="input-group-append">
                                            <span class="sh_dec input-group-text">Keys</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">

                                    <label class="sh_dec min-titleh" for="isc_multiplier">In-store multiplier</label>
                                    <div class="input-group mb-3">
                                        <div class="sh_dec input-group-prepend">
                                            <span class="sh_dec input-group-text">$1 =</span>
                                        </div>
                                        <input type="text" name="instore_multiplier"
                                            placeholder="Enter In-store multiplier" class="sh_dec form-control"
                                            aria-label="Amount (to the nearest dollar)"
                                            value="{{ $t->instore_multiplier }}">
                                        <div class="input-group-append">
                                            <span class="sh_dec input-group-text">Keys</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row mt-2 border-bottom">
                                <div class="col-12">
                                    <h5 class="sh_sub_title text-primary">Milestones Management
                                        {{-- <button class="sh_btn btn btn-primary float-end" type="button" data-bs-toggle="modal"
                                            data-bs-target="#AddModal">Add New</button> --}}
                                    </h5>
                                    <p class="card-title-desc 1 sh_dec">Milestones based on tier user can earn keys or reward.
                                        <span class="text-warning ">If user reach the milestone already changes not
                                            effect .</span>
                                    </p>
                                </div>
                                @foreach ($milestones as $milestone)

                                <div class="col-lg-6">

                                    <input type="hidden" name="milestone_id[]" value="{{$milestone->id}}">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="sh_dec min-titleh" for="name">Milestones 
                                                name ({{number_format($milestone->min)}} - {{number_format($milestone->max)}})</label>
                                            <div class="input-group mb-3">

                                                <input type="text" name="milestone_name[]"
                                                    placeholder="Milestones  Name" class="sh_dec form-control"
                                                    aria-label="Amount (to the nearest dollar)"
                                                    value="{{ $milestone->name }}" required>

                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="sh_dec min-titleh" for="amount">Milestones {{$loop->iteration}}
                                                amount</label>
                                            <div class="input-group mb-3">
                                                <div class="sh_dec input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" min="0" name="milestone_amount[]"
                                                    placeholder="Milestones  Amount" class="form-control sh_dec"
                                                    aria-label="Amount (to the nearest dollar)"
                                                    value="{{ $milestone->amount }}" required min="{{$milestone->min}}"
                                                    max="{{$milestone->max}}">

                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <div class="col-lg-6">
                                    <div class="row">
                                        <div class="col-xl-6 col-sm-6">

                                            <label class="sh_dec min-titleh" for="isc_multiplier">Milestone Earings</label>
                                            <div class="mt-2 d-flex">
                                                <div class="sh_dec form-check mb-3 me-3">
                                                    <input class="form-check-input radio-earning" type="radio"
                                                        name="milestone_type_{{$t->id.'-'.$loop->index}}"
                                                        id="formKeys{{$t->id.'-'.$loop->index}}"
                                                        @checked($milestone->type == 'key') value="key"
                                                    data-colid="col{{$t->id.'-'.$loop->index}}">
                                                    <label class="form-check-label"
                                                        for="formKeys{{$t->id.'-'.$loop->index}}">
                                                        Keys
                                                    </label>
                                                </div>
                                                {{-- <div class="form-check">
                                                    <input class="form-check-input radio-earning" type="radio"
                                                        name="milestone_type_{{$t->id.'-'.$loop->index}}"
                                                        id="formRewards{{$t->id.'-'.$loop->index}}"
                                                        data-colid="col{{$t->id.'-'.$loop->index}}" value="reward"
                                                        @checked($milestone->type == 'reward')>
                                                    <label class="form-check-label"
                                                        for="formRewards{{$t->id.'-'.$loop->index}}">
                                                        Rewards
                                                    </label>
                                                </div> --}}
                                            </div>
                                        </div>
                                        <div id="keys-col{{$t->id.'-'.$loop->index}}" @class(['col-xl-6
                                            col-sm-6', 'd-none'=> $milestone->type != 'key'])>
                                            <label class="sh_dec min-titleh" for="isc_multiplier">Keys earn when user reach milestone</label>
                                            <div class="input-group mb-3">

                                                <input type="text" name="milestone_no_of_keys[]"
                                                    placeholder="Enter Keys user earn" class="form-control sh_dec"
                                                    aria-label="Amount (to the nearest dollar)"
                                                    value="{{ $milestone->no_of_keys }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text sh_dec">Keys</span>
                                                </div>
                                            </div>

                                        </div>
                                        <div @class(['col-xl-6 col-sm-6', 'd-none'=> $milestone->type != 'reward'])
                                            id="reward-col{{$t->id.'-'.$loop->index}}">
                                            <div class="form-group">
                                                <label class="sh_dec min-titleh">Reward</label>
                                                <select class="sh_dec form-select select2" name="milestone_reward_id[]">
                                                    @foreach ($rewards as $reward)

                                                    <option class="sh_dec" value="{{$reward->id}}" @selected($reward->id ==
                                                        $milestone->reward_id)>{{$reward->name .' - ' .
                                                        $reward->code}}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                {!! $milestones->links() !!}

                            </div>


                            <button class="sh_btn btn btn-primary mt-3 save-btn">
                                <i class="mdi mdi-file"></i>
                                Save
                            </button>
                        </form>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

    </div>
</div>


<div class="modal fade" id="AddModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-hidden="true">
    @php
    $minm = $last_milestone->max + 1;
    $maxm = $last_milestone->max + 500;
    @endphp
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Milestone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" id="add_frm" action="{{route('admin.tiers.milestone.save')}}">
                    @csrf



                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="code">Milestones Name <span class="required-hash">*</span></label>
                                <input id="name" type="text" class="form-control" name="name" placeholder="Enter name"
                                    required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="amount">Milestones Amount <span class="required-hash">*</span></label>
                                <input id="amount" type="number" min="{{$minm}}" max="{{$maxm}}" class="form-control"
                                    name="amount" placeholder="Enter amount" required>
                            </div>
                        </div>
                        <input type="hidden" name="type" value="key">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="name">Keys earn <span class="required-hash">*</span></label>
                                <input id="no_of_keys" type="number" min="0" class="form-control" name="no_of_keys"
                                    placeholder="Enter keys" required>
                            </div>
                        </div>



                        <div class="col-6 mt-3 d-grid">
                            <button class="btn btn-outline-danger waves-effect waves-light" type="reset"
                                onclick="remove_errors()">Reset</button>
                        </div>
                        <div class="col-6 mt-3 d-grid">
                            <button class="btn btn-primary waves-effect waves-light" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    var ModuleBaseUrl = "{{route('admin.tiers.update') }}";
     
        $(document).ready(function () {
        
            $(document).on('change','.radio-earning',function (e) {
                const checkbox = $(this);
                console.log("data",checkbox.attr("data-colid"));
                const  colid = checkbox.attr("data-colid")
                $('#keys-' +colid).toggleClass('d-none');
                $('#reward-'+colid).toggleClass('d-none');
            });
   
        $(document).on("submit",".save-btn",function (e) {
            e.preventDefault();
            const btn = $(this);
            btn.attr("disabled", true);
            e.preventDefault();
            var form_data = new FormData($(this)[0]);
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
            if(response.status === 'success'){
            show_message(response.status,response.message);
             
              } else {
            show_message(response.status,response.message);
            }
            remove_errors();
            },
            error: function(response) {
            show_errors(response.responseJSON.errors);
            }
            });
        });

    });
</script>
@endsection