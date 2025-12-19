@extends('layouts.master-layouts')

@section('title') CSO Purchase @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') CSO Purchase @endslot
@endcomponent


<div class="container mt-4">

    <h5 class="mb-3"><strong>Current Deals:</strong></h5>
    <input type="hidden" id="purchase_id">

    <div class="row">
        @forelse($rewards as $reward)

            <div class="col-md-4 col-lg-3 mb-4">
                <div class="reward-card">

                    {{-- IMAGE --}}
                    <div class="reward-image">
                        @if($reward->voucher_image)
                            <img src="{{ asset('uploads/image/'.$reward->voucher_image) }}" alt="" width="100px" height="100px">
                        @else
                            <div class="img-placeholder">IMAGE</div>
                        @endif
                    </div>

                    {{-- CONTENT --}}
                    <div class="reward-body">

                        <p class="reward-type">
                            {{ $reward->inventory_type == 0 ? 'Physical' : 'Digital' }}
                        </p>

                        <h6 class="reward-title">{{ $reward->name }}</h6>

                        <p class="reward-desc">
                            {{ $reward->description }}
                        </p>

                        <p class="reward-text">
                            <strong>From:</strong> ${{ $reward->voucher_value }}
                        </p>

                        <p class="reward-text">
                            <strong>Sale End Date Time:</strong><br>
                            {{ $reward->voucher_validity ?? 'yyyy-MM-dd HH:mm:ss' }}
                        </p>

                        <p class="reward-text">Total: {{ $reward->voucher_set }}</p>
                        <p class="reward-text">Left: {{ $reward->inventory_qty ?? 0 }}</p>
                        <p class="reward-text">Club Totals: 12</p>
                        <p class="reward-text">Total Sold: 38</p>
                        <p class="reward-text">(Sold) Online: 1</p>
                        <p class="reward-text">(Sold) Inhouse: 37</p>
                        <p class="reward-text">Pending Collection: 0</p>

                        <div class="text-left mt-3">
                            <button   class="btn btn-secondary btn-sm buy-btn" data-bs-toggle="modal" data-bs-target="#memberModal" data-reward-id="{{ $reward->id }}">
                                BUY
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="col-12 text-center">
                <p>No rewards available.</p>
            </div>
        @endforelse
    </div>

</div>


<!-- Create -->
@can("$permission_prefix-create")
@include('admin.cso-purchase.member-modal')
@include('admin.cso-purchase.checkout-form')
@endcan
<!-- end modal -->
@endsection

@section('script')
<script>
    $(document).on('click', '.buy-btn', function () {
        $('#reward_id').val($(this).data('reward-id'));
        $('#member_id').val('');
    });
    $('#btnCheckout').on('click', function () {
        $('#checkoutStep').hide();
        $('#previewStep').show();
    });
    $('#btnBack').on('click', function () {
        $('#previewStep').hide();
        $('#checkoutStep').show();
    });

    $('#btnConfirm').on('click', function () {

        $.ajax({
            url: '{{ url("/admin/checkout") }}',
            method: 'POST',
            data: $('#checkoutForm').serialize(),
            success: function (res) {             

                $('#purchase_id').val(res.purchase_id);

                $('#receipt_no').text(res.receipt_no);
                $('#receipt_date').text(res.date);

                $('#confirm_reward').text($('#d_reward').text());
                $('#confirm_qty').text($('#qty').val());
                $('#confirm_price').text($('#total').val());
                $('#confirm_amount').text($('#total').val());

                $('#confirm_subtotal').text($('#subtotal').val());
                $('#confirm_total').text($('#total').val());

                $('#previewStep').hide();
                $('#confirmationStep').show();

            }
        });

    });

    $('#btnCompletePurchase').on('click', function () {

        let purchaseId = $('#purchase_id').val();

        Swal.fire({
            title: 'Confirm Purchase',
            text: 'Do you want to complete this purchase?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, complete it',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: '{{ url("admin/purchase/complete") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        purchase_id: purchaseId
                    },
                    success: function () {

                        Swal.fire({
                            icon: 'success',
                            title: 'Completed',
                            text: 'Purchase completed successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#checkoutModal').modal('hide');
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            }
        });
    });


    $('#btnCancelPurchase').on('click', function () {

        let purchaseId = $('#purchase_id').val();

        Swal.fire({
            title: 'Cancel Purchase?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No',
            confirmButtonColor: '#d33',
            reverseButtons: true
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: '{{ url("admin/purchase/cancel") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        purchase_id: purchaseId
                    },
                    success: function () {

                        Swal.fire({
                            icon: 'success',
                            title: 'Cancelled',
                            text: 'Purchase has been cancelled',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#checkoutModal').modal('hide');
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            }
        });
    });




  $('#submitMember').on('click', function () {

    const memberId = $('#member_id').val().trim();
    const rewardId = $('#reward_id').val();

    if (!memberId) {
        alert('Member ID is required');
        return;
    }

    $.ajax({
        url: "{{ url('admin/get-member-details') }}",
        method: 'POST',
        data: {
            member_id: memberId,
            reward_id: rewardId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {

            /* ================= MEMBER ================= */
            $('#d_name').text(res.member.name);
            $('#d_email').val(res.member.email);
            $('#d_mobile').val(res.member.mobile);

            // hidden (must be submitted)
            $('#member_name').val(res.member.name);
            $('#member_email').val(res.member.email);

            /* ================= REWARD ================= */
            $('#reward_image').attr('src', res.reward.image);
            $('#reward_type').text(res.reward.type);
            $('#reward_name').text(res.reward.name);
            $('#reward_offer').text(res.reward.offer);
            $('#d_reward').text(res.reward.name);

            $('#reward_end').text(res.reward.sales_end);
            $('#reward_left').text(res.reward.remaining_qty);

            /* ================= RATES ================= */
            $('#rate_member').text(res.reward.rates.member);
            $('#rate_movie').text(res.reward.rates.movie);
            $('#rate_bitez').text(res.reward.rates.bitez);
            $('#rate_travel').text(res.reward.rates.travel);

            /* ================= PRICING ================= */
            $('#d_subtotal').text(res.pricing.subtotal);
            $('#d_admin').text(res.pricing.admin_fee);
            $('#d_total').text(res.pricing.total);

            // hidden pricing
            $('#subtotal').val(res.pricing.subtotal);
            $('#admin_fee').val(res.pricing.admin_fee);
            $('#total').val(res.pricing.total);

            /* ================= IDS ================= */
            $('#checkout_member_id').val(res.member.id);
            $('#checkout_reward_id').val(res.reward.id);

            /* ================= MODAL FLOW ================= */
            $('#memberModal').modal('hide');
            $('#checkoutModal').modal('show');
        },

        error: function (xhr) {
            alert(xhr.responseJSON?.message || 'Member not found');
        }
    });
});

</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection