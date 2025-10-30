@extends('layouts.master-layouts')

@section('title') Rewards Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Rewards Management @endslot
@endcomponent




<div class="row">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold sh_sub_title">Customer Information</h5>

                <div class="table-responsive">
                    <table class="table sh_table">
                        <tbody>
                            <tr>
                                <th scope="col">APHID/Employment ID</th>
                                <td scope="col">{{$upr->user->unique_id}}</td>
                            </tr>
                            <tr>
                                <th scope="row">APH Expiry Date</th>
                                <td>{{ $upr->user->expiry_date ?
                                    $upr->user->expiry_date->format(config('shilla.date-format')) : ''}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Name</th>
                                <td>{{$upr->user->name}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Email</th>
                                <td>{{$upr->user->email}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Gender</th>
                                <td>{{$upr->user->gender}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Date Of Birth</th>
                                <td>{{ $upr->user->date_of_birth->format(config('shilla.date-format')) ?? ''}}</td>
                            </tr>

                            <tr>
                                <th scope="row">Status</th>
                                <td><span class="badge badge-soft-info">{{ $upr->user->status }}</span></td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>


    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold sh_sub_title">Reward Information</h5>

                <div class="table-responsive">
                    <table class="table sh_table">
                        <tbody>
                            <tr>
                                <th scope="col">Code</th>
                                <td scope="col">{{$upr->reward->code}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Name</th>
                                <td>{{$upr->reward->name}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Keys</th>
                                <td>{{$upr->key_use}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Quantity</th>
                                <td>{{$upr->reward->quantity == 0? 'Unlimited':$upr->reward->quantity}}</td>
                            </tr>
                            <tr>
                                @php
                                $duration = $upr->reward->start_date->format(config('shilla.date-format'));

                                if ($upr->reward->end_date) {
                                $duration .= ' to ' . $upr->reward->end_date->format(config('shilla.date-format'));
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
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold sh_sub_title">Redemption Information</h5>

                <div class="table-responsive">
                    <table class="table sh_table">
                        <tbody>


                            <tr>
                                <th scope="row">Status</th>
                                <td>{{$upr->status !== 'Purchased'?$upr->status:'Issued'}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Remarks</th>
                                <td>{{$upr->reason}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Issued Date</th>
                                <td>{{$upr->created_at->format(config('shilla.date-format'))}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Redeemed Date</th>
                                <td>{{$upr->redeem_date ? $upr->redeem_date->format(config('shilla.date-format')): ''}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Expiry Date</th>
                                <td>{{$upr->expiry_date->format(config('shilla.date-format'))}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>


    </div>

    @if ($upr->status === "Purchased" &&  $available)



    <div class="col-xl-12 mt-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold sh_sub_title">Redemption Form</h5>

                <form action="{{route('admin.redemption-reward.update',['redemption_reward' => $upr->id])}}" method="post">
                    @csrf
                    @method("put")
                    <div class="mb-3">
                        <label class="sh_dec" for="status"> Status <span class="required-hash">*</span> </label>
                        <select class="sh_dec form-select form-control " name="status" required>
                            <option class="sh_dec" value="">Please select status</option>
                            <option class="sh_dec" value="Redeemed">Redeemed</option>
                            <option class="sh_dec" value="Decline">Decline</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="sh_dec" for="status"> Remark </label>
                        <textarea name="reason" id="reason" class="sh_dec form-control " cols="30" rows="10"></textarea>
                    </div>
                    <div class="hstack gap-2">
                        <button class="sh_btn btn btn-soft-primary " type="submit">Save</button>
                        <button class="sh_btn_sec btn btn-soft-danger " type="reset">Reset</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endif
</div>



@endsection
