@extends('layouts.master-layouts')

@section('title') User Wallet Details @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') User Wallet Details @endslot
@endcomponent

<div class="row">   
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                {{-- <h5 class="sh_sub_title fw-semibold">Rewards Redemption (Last 365Days)</h5> --}}

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
                                       
                                        {{ $reward->reward->name ?? '' }}
                                    </td>

                                    <td>{{ $reward->unique_code }}</td>
                                    <td>{{ $reward->receipt_no }}</td>

                                    <td>
                                        @if(optional($reward->reward)->type === 0)
                                            Treats & Deals
                                        @elseif(optional($reward->reward)->type === 1)
                                            Evoucher
                                        @elseif(optional($reward->reward)->type === 2)
                                            Bday
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if(optional($reward->reward)->reward_type === 0)
                                            Digital Voucher
                                        @elseif(optional($reward->reward)->reward_type === 1)
                                            Physical Voucher
                                        @else
                                            -
                                        @endif
                                    </td>


                                    <td>
                                        {{ optional($reward->reward)->voucher_validity
                                            ? \Carbon\Carbon::parse(optional($reward->reward)->voucher_validity)
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

@endsection
@section('script')
<script src="{{ URL::asset('/build/libs/flatpicker/flatpickr.js') }}"></script>
<link rel="stylesheet" href="{{ URL::asset('/build/libs/flatpicker/flatpickr.min.css') }}">
<script>
  

  
    var ModuleBaseUrl = "{{ $module_base_url }}/";

    $(document).on('change', '.suspend-toggle', function () {

        let id = $(this).data('id');
        let status = $(this).is(':checked') ? 1 : 0;
        let toggleElement = $(this);

        $.ajax({
            url: ModuleBaseUrl + "toggle-suspend",
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
