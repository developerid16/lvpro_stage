@extends('layouts.master-layouts')

@section('title') Customer Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Customer Management @endslot
@endcomponent

<div class="row">   
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h5 class="sh_sub_title fw-semibold">Rewards Redemption (Last 365Days)</h5>

                <div class="table-responsive">
                    <table class="sh_table table">
                        <thead>
                            <tr>
                                <th>Reward Name</th>
                                <th>Unique Code</th>
                                <th>Receipt No</th>
                                <th>Type</th>
                                <th>Reward Type</th>
                                <th>Voucher Validity</th>
                                <th class="text-center">Suspend</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($rewards as $reward)
                                <tr>

                                    <td>
                                        <a class="text-danger"
                                        href="{{ url('admin/reward/'.$reward->reward->id) }}"
                                        target="_blank">
                                            {{ $reward->reward->name }}
                                        </a>
                                    </td>

                                    <td>{{ $reward->unique_code }}</td>
                                    <td>{{ $reward->receipt_no }}</td>

                                    <td>
                                        @if($reward->reward->type == 0)
                                            Treats & Deals
                                        @elseif($reward->reward->type == 1)
                                            Evoucher
                                        @elseif($reward->reward->type == 2)
                                            Bday
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($reward->reward->reward_type == 0)
                                            Digital Voucher
                                        @elseif($reward->reward->reward_type == 1)
                                            Physical Voucher
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        {{ $reward->reward->voucher_validity
                                            ? \Carbon\Carbon::parse($reward->reward->voucher_validity)
                                                ->format(config('safra.date-only'))
                                            : '-' }}
                                    </td>

                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input suspend-toggle"
                                                type="checkbox"
                                                data-id="{{ $reward->id }}"
                                                {{ $reward->suspend_voucher ? 'checked' : '' }}>
                                        </div>
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

    $(document).on('change', '.suspend-toggle', function () {

        let id = $(this).data('id');
        let status = $(this).is(':checked') ? 1 : 0;
        let toggleElement = $(this);

        $.ajax({
            url: "{{ url('admin/app-user/toggle-suspend') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                suspend_voucher: status
            },
            success: function (response) {
                // optional toast
            },
            error: function () {
                alert('Something went wrong');
                toggleElement.prop('checked', !status); // revert
            }
        });

    });

</script>
@endsection
