<div class="modal fade" id="VoucherDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Voucher Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Reward Name</th>
                            <th>Reward Type</th>
                            <th>Merchant</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Serial No</th>
                            <th>Unique Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vouchers as $voucher)

                            @php
                                $reward = $voucher->reward;
                                $image = $reward && $reward->voucher_detail_img
                                    ? asset('uploads/image/'.$reward->voucher_detail_img)
                                    : asset('uploads/image/no-image.png');
                            @endphp

                            <tr>
                                <td>
                                    <img src="{{ $image }}" style="object-fit:cover; border-radius:6px;max-heigth: 60px !important; max-width:60px">
                                </td>

                                <td>{{ $reward->name ?? '-' }}</td>

                                <td>
                                    {{ $reward
                                        ? ($reward->reward_type == 1 ? 'Physical' : 'Digital')
                                        : '-' }}
                                </td>

                                <td>{{ optional($reward->merchant)->name ?? '-' }}</td>

                                <td>
                                    {{ $reward && $reward->usual_price
                                        ? number_format($reward->usual_price, 2)
                                        : '-' }}
                                </td>

                                <td>{{ $voucher->qty }}</td>

                                <td>{{ $voucher->status }}</td>

                                <td>{{ $voucher->serial_no }}</td>

                                <td>{{ $voucher->unique_code }}</td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>
