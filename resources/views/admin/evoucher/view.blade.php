<div class="modal fade" id="ViewModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">View E-Voucher: Digital Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height:80vh; overflow:auto;">
                <form>
                    <div class="row">

                    <!-- Push -->
                    <div class="col-md-6 mb-3">
                    <label>Push Method</label>
                    <input type="text" class="form-control"
                    value="{{ match($data->cso_method){
                        4=>'All Members',
                        0=>'CSO Issuance',
                        1=>'Push Voucher by Member ID',
                        2=>'Push Voucher by Parameter',
                        3=>'Push by API SRP',
                        default=>''
                    } }}" readonly>
                    </div>

                    <!-- Name -->
                    <div class="col-md-6 mb-3">
                    <label>Voucher Name</label>
                    <input type="text" class="form-control" value="{{ $data->name }}" readonly>
                    </div>

                    <!-- Images -->
                    <div class="col-md-6 mb-3">
                    <label>Voucher Catalogue Image</label><br>
                    <img src="{{ imageExists('uploads/image/'.$data->voucher_image) }}" width="80">
                    </div>

                    <div class="col-md-6 mb-3">
                    <label>Voucher Details Image</label><br>
                    <img src="{{ imageExists('uploads/image/'.$data->voucher_detail_img) }}" width="80">
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea class="form-control" readonly>{!! strip_tags($data->description) !!}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                    <label>Voucher T&C</label>
                    <textarea class="form-control" readonly>{!! strip_tags($data->term_of_use) !!}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                    <label>How to use</label>
                    <textarea class="form-control" readonly>{!! strip_tags($data->how_to_use) !!}</textarea>
                    </div>

                    <!-- Merchant -->
                    <div class="col-md-6 mb-3">
                    <label>Merchant</label>
                    <input type="text" class="form-control" value="{{ $data->merchant->name ?? '' }}" readonly>
                    </div>

                    <!-- Voucher Type -->
                    <div class="col-md-6 mb-3">
                    <label>Voucher Type</label>
                    <input type="text" class="form-control" value="e-Voucher" readonly>
                    </div>

                    <!-- Direct Utilization -->
                    <div class="col-md-6 mb-3">
                    <label>Direct Utilization</label>
                    <input type="text" class="form-control" value="{{ $data->direct_utilization ? 'Yes':'No' }}" readonly>
                    </div>

                   <!-- 🔥 DATE & TIME (same as add form) -->
                    <div class="col-12">
                        <label><b>Date & Time</b></label>

                        <div class="row">

                            <!-- Publish Start -->
                            <div class="col-md-6 mb-3">
                                <label>Publish Start Date & Time</label>
                                <input type="text" class="form-control"
                                    value="{{ $data->publish_start_date }} {{ $data->publish_start_time }}" readonly>
                            </div>

                            <!-- Publish End -->
                            <div class="col-md-6 mb-3">
                                <label>Publish End Date & Time</label>
                                <input type="text" class="form-control"
                                    value="{{ $data->publish_end_date }} {{ $data->publish_end_time }}" readonly>
                            </div>

                            <!-- Redemption Start -->
                            <div class="col-md-6 mb-3">
                                <label>Redemption Start Date & Time</label>
                                <input type="text" class="form-control"
                                    value="{{ $data->sales_start_date }} {{ $data->sales_start_time }}" readonly>
                            </div>

                            <!-- Redemption End -->
                            <div class="col-md-6 mb-3">
                                <label>Redemption End Date & Time</label>
                                <input type="text" class="form-control"
                                    value="{{ $data->sales_end_date }} {{ $data->sales_end_time }}" readonly>
                            </div>

                            <!-- Usage Days -->
                            <div class="col-md-12 mb-3">
                                <label>Usage Days</label>
                               <input type="text" class="form-control"
                                value="{{ implode(', ', is_array($data->days) ? $data->days : json_decode($data->days ?? '[]', true)) }}" readonly>
                            </div>

                            <!-- Usage Time -->
                            <div class="col-md-6 mb-3">
                                <label>Usage Start Time</label>
                                <input type="text" class="form-control"
                                    value="{{ $data->start_time }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Usage End Time</label>
                                <input type="text" class="form-control"
                                    value="{{ $data->end_time }}" readonly>
                            </div>

                        </div>
                    </div>

                    <!-- Expiry -->
                    <div class="col-md-6 mb-3">
                    <label>Voucher Expiry Type</label>
                    <input type="text" class="form-control"
                    value="{{ ucfirst(str_replace('_',' ',$data->expiry_type)) }}" readonly>
                    </div>

                    @if($data->expiry_type == 'fixed')
                    <div class="col-md-6 mb-3">
                    <label>Voucher Validity Date</label>
                    <input type="text" class="form-control" value="{{ $data->voucher_validity }}" readonly>
                    </div>
                    @endif

                    @if($data->expiry_type == 'validity')
                    <div class="col-md-6 mb-3">
                    <label>Validity Period</label>
                    <input type="text" class="form-control" value="{{ $data->validity_month }}" readonly>
                    </div>
                    @endif

                    <!-- Inventory -->
                    <div class="col-md-6 mb-3">
                    <label>Internal/External</label>
                    <input type="text" class="form-control"
                    value="{{ $data->inventory_type==0?'Internal':'External' }}" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                    <label>Total no. of vouchers/codes</label>
                    <input type="text" class="form-control" value="{{ $data->inventory_qty }}" readonly>
                    </div>

                    <!-- Voucher -->
                    <div class="col-md-6 mb-3">
                    <label>Voucher Value ($)</label>
                    <input type="text" class="form-control" value="{{ $data->voucher_value }}" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                    <label>No. of vouchers/codes per set, per member</label>
                    <input type="text" class="form-control" value="{{ $data->voucher_set }}" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                    <label>Total no. of sets available for issuance</label>
                    <input type="text" class="form-control" value="{{ $data->set_qty }}" readonly>
                    </div>

                    <!-- Clearing -->
                    <div class="col-md-6 mb-3">
                    <label>Clearing Methods</label>
                    <input type="text" class="form-control"
                    value="{{ ['QR Code','Barcode','Merchant Code','External Link','External Code'][$data->clearing_method] ?? '' }}" readonly>
                    </div>

                  
                    <!-- Settings -->
                    <div class="col-md-6 mb-3">
                    <label>Hide Quantity</label>
                    <input type="text" class="form-control" value="{{ $data->hide_quantity?'Yes':'No' }}" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                    <label>Low Stock Reminder 1</label>
                    <input type="text" class="form-control" value="{{ $data->low_stock_1 }}" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                    <label>Low Stock Reminder 2</label>
                    <input type="text" class="form-control" value="{{ $data->low_stock_2 }}" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                    <label>Friendly URL Name</label>
                    <input type="text" class="form-control" value="{{ $data->friendly_url }}" readonly>
                    </div>

                      <!-- Category -->
                    <div class="col-md-6 mb-3">
                    <label>Category</label>
                   <select class="form-control" disabled>
                    @foreach ($category as $cat)
                    <option value="{{ $cat->id }}"
                    {{ $data->category_id == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                    </option>
                    @endforeach
                    </select>
                    </div>


                    <div class="col-md-6 mb-3">
                    <label>Suspend Deal</label>
                    <input type="text" class="form-control" value="{{ $data->suspend_deal?'Yes':'No' }}" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                    <label>Suspend Voucher</label>
                    <input type="text" class="form-control" value="{{ $data->suspend_voucher?'Yes':'No' }}" readonly>
                    </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>