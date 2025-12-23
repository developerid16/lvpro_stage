@extends('layouts.master-layouts')

@section('title') CSO Purchase @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') CSO Purchase @endslot
@endcomponent


<form method="GET" action="{{ url()->current() }}" class="mb-4">
    <div class="row g-2 align-items-end">

        <div class="col-md-3">
            <label class="form-label fw-bold">Reward Type</label>
            <select name="reward_type" class="form-select">
                <option value="">All</option>
                <option value="0" {{ (string)$selected_type === '0' ? 'selected' : '' }}>
                    Digital
                </option>
                <option value="1" {{ (string)$selected_type === '1' ? 'selected' : '' }}>
                    Physical
                </option>
            </select>
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">
                Search
            </button>
        </div>

        <div class="col-md-1">
            <a href="{{ url()->current() }}" class="btn btn-secondary w-100">
                Reset
            </a>
        </div>

    </div>
</form>

<h5 class="mb-3"><strong>Current Deals:</strong></h5>
<input type="hidden" id="purchase_id">

<div class="row g-4">
    @forelse($rewards as $reward)

        <div class="col-lg-2 col-md-2">
            <div class="card reward-card h-100 shadow-sm">

                {{-- IMAGE --}}
                <div class="reward-img-wrapper d-flex justify-content-center">
                    @if($reward->voucher_image)
                        <img src="{{ asset('uploads/image/'.$reward->voucher_image) }}"
                            class="card-img-top reward-img"
                            alt="{{ $reward->name }}"  style="width: 150px; height: 150px;">
                    @else
                        <div class="reward-img-placeholder">
                            No Image
                        </div>
                    @endif
                </div>

                {{-- BODY --}}
                <div class="card-body d-flex flex-column">

                    <span class="badge bg-info mb-2 align-self-start p-2">
                        {{ $reward->reward_type == 0 ? 'Digital' : 'Physical' }}
                    </span>

                    <h6 class="card-title fw-bold mb-1">
                        {{ $reward->name }}
                    </h6>

                    <p class="text-muted small mb-2">
                        {{ Str::limit($reward->description, 70) }}
                    </p>

                    <p class="fw-semibold mb-1">
                        From: <span class="text-success">${{ $reward->voucher_value }}</span>
                    </p>

                    <p class="small mb-2">
                        <strong>Sale Ends:</strong><br>
                        {{ $reward->voucher_validity ?? '-' }}
                    </p>

                    <hr class="my-2">

                    <ul class="list-unstyled small mb-3">
                        <li>Total: {{ $reward->voucher_set }}</li>
                        <li>Left: {{ $reward->inventory_qty ?? 0 }}</li>
                        <li>Club Total: 12</li>
                        <li>Total Sold: 38</li>
                        <li>Online: 1 | Inhouse: 37</li>
                        <li>Pending Collection: 0</li>
                    </ul>

                    <div class="mt-auto">
                        <button
                            class="btn btn-primary w-100 buy-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#memberModal"
                            data-reward-id="{{ $reward->id }}">
                            BUY
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @empty
        <div class="col-12 text-center">
            <p class="text-muted">No rewards available.</p>
        </div>
    @endforelse
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
  

    $('#btnConfirm').on('click', function () {

        $('#checkoutModalTitle').text('Purchase Confirmation');

        $.ajax({
            url: '{{ url("/admin/checkout") }}',
            method: 'POST',
            data: $('#checkoutForm').serialize(),

            success: function (res) {

                $('#purchase_id').val(res.purchase_id);

                $('#receipt_no').text(res.receipt_no);
                $('#receipt_date').text(res.date);
                $('.type').text(res.type);
                $('.name').text(res.name);

                $('#confirm_reward').text($('#d_reward').val());
                $('#confirm_qty').text($('#qty').val());
                $('#confirm_price').text($('#total').val());
                $('#confirm_amount').text($('#total').val());

                $('#confirm_subtotal').text($('#subtotal').val());
                $('#confirm_total').text($('#total').val());
                $('#payment_mode').text(res.payment_mode);

                // ✅ ONLY TWO STEPS NOW
                $('#checkoutStep').hide();
                $('#confirmationStep').show();
            },

            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Checkout Failed',
                    text: xhr.responseJSON?.message || 'Something went wrong'
                });
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
                $('#d_name').val(res.member.name);
                $('#d_email').val(res.member.email);
                $('#d_mobile').val(res.member.mobile);

                // hidden (must be submitted)
                $('#member_name').val(res.member.name);
                $('#member_email').val(res.member.email);

                /* ================= REWARD ================= */
                $('#reward_image').attr('src', res.reward.image);
                $('#reward_type').text(res.reward.type);
                /* ================= COLLECTION (BASED ON REWARD TYPE) ================= */
                let $collection = $('#collection');
                $collection.empty(); // remove old options

                if (parseInt(res.reward.reward_type) === 0) {
                    // Digital
                    $collection.append(
                        `<option value="digital" selected>Digital</option>`
                    );
                } else if (parseInt(res.reward.reward_type) === 1) {
                    // Physical
                    $collection.append(
                        `<option value="physical" selected>Physical</option>`
                    );
                }

                $('#reward_name').text(res.reward.name);
                $('#reward_offer').text(res.reward.offer);
                $('#d_reward').val(res.reward.name);
                $('#checkoutModalTitle').text(res.reward.name);
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
                $('#btnBackToMember').attr('data-reward-id', res.reward.id);

                /* ================= MODAL FLOW ================= */
                $('#memberModal').modal('hide');
                $('#checkoutModal').modal('show');
            },

            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Member not found');
            }
        });
    });

    $('#checkoutModal').on('hidden.bs.modal', function () {

        $('#checkoutModalTitle').text('');

        // show checkout again
        $('#checkoutStep').show();
        $('#confirmationStep').hide();

        // reset purchase id
        $('#purchase_id').val('');

        // reset form
        $('#checkoutForm')[0].reset();
    });


    $('#checkoutModal').on('show.bs.modal', function () {

        // Reset steps
        $('#checkoutStep').show();
        $('#confirmationStep').hide();

        // Reset purchase id
        $('#purchase_id').val('');

        // Optional: reset form
        $('#checkoutForm')[0].reset();
    });


</script>
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection