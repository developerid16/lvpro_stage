<div class="modal fade" id="ViewModal" data-bs-backdrop="static">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">View Reward</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body" style="max-height:80vh; overflow:auto;">
<div class="row">

<!-- Push Method -->
<div class="col-md-6 mb-3">
<label>Push Method</label>
<input class="form-control" value="{{ match($data->cso_method){
4=>'All Members',0=>'CSO Issuance',1=>'Push Voucher by Member ID',
2=>'Push Voucher by Parameter',3=>'Push by API SRP',default=>''} }}" readonly>
</div>

<!-- Voucher Name -->
<div class="col-md-6 mb-3">
<label>Voucher Name</label>
<input class="form-control" value="{{ $data->name }}" readonly>
</div>

<!-- Images -->
<div class="col-md-6 mb-3">
<label>Voucher Catalogue Image</label><br>
<img src="{{ imageExists('uploads/image/'.$data->voucher_image) }}?v={{ time() }}" width="80">
</div>

<div class="col-md-6 mb-3">
<label>Voucher Details Image</label><br>
<img src="{{ imageExists('uploads/image/'.$data->voucher_detail_img) }}?v={{ time() }}" width="80">
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
<input class="form-control" value="{{ $data->merchant->name ?? '' }}" readonly>
</div>

<!-- Voucher Type -->
<div class="col-md-6 mb-3">
<label>Voucher Type</label>
<input class="form-control" value="{{ $data->reward_type==0?'Digital Voucher':'Physical Voucher' }}" readonly>
</div>

<!-- Expiry -->
<div class="col-md-6 mb-3">
<label>Voucher Expiry Type</label>
<input class="form-control" value="{{ ucfirst(str_replace('_',' ',$data->expiry_type)) }}" readonly>
</div>

@if($data->expiry_type=='fixed')
<div class="col-md-6 mb-3">
<label>Voucher Validity Date</label>
<input class="form-control" value="{{ $data->voucher_validity }}" readonly>
</div>
@endif

@if($data->expiry_type=='validity')
<div class="col-md-6 mb-3">
<label>Validity Period</label>
<input class="form-control" value="{{ $data->validity_month }}" readonly>
</div>
@endif

<!-- Where To Use -->
<div class="col-md-6 mb-3">
<label>Where To Use</label>
<input class="form-control" value="{{ $data->where_use }}" readonly>
</div>

<!-- DATE -->
<div class="col-12">
<label><b>Date & Time</b></label>
<div class="row">

<div class="col-md-6 mb-3">
<label>Publish Start Date & Time</label>
<input class="form-control" value="{{ $data->publish_start_date }} {{ $data->publish_start_time }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Publish End Date & Time</label>
<input class="form-control" value="{{ $data->publish_end_date }} {{ $data->publish_end_time }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Sales Start Date & Time</label>
<input class="form-control" value="{{ $data->sales_start_date }} {{ $data->sales_start_time }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Sales End Date & Time</label>
<input class="form-control" value="{{ $data->sales_end_date }} {{ $data->sales_end_time }}" readonly>
</div>

</div>
</div>

<!-- Price -->
<div class="col-md-6 mb-3">
<label>Usual Price($)</label>
<input class="form-control" value="{{ $data->usual_price }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Maximum Quantity (Per member)</label>
<input class="form-control" value="{{ $data->max_quantity }}" readonly>
</div>

<!-- TIER -->
<div class="col-12">
<label><b>Tier Rates</b></label>
<div class="row">
@foreach($tiers as $tier)
@php
$price='';
foreach($data->tierRates as $r){
if($r->tier_id==$tier->id){$price=$r->price;}
}
@endphp
<div class="col-md-4 mb-3">
<label>{{ $tier->tier_name }} Price</label>
<input class="form-control" value="{{ $price }}" readonly>
</div>
@endforeach
</div>
</div>

<!-- DIGITAL -->
@if($data->reward_type==0)

<div class="col-md-6 mb-3">
<label>Internal/External</label>
<input class="form-control" value="{{ $data->inventory_type==0?'Internal':'External' }}" readonly>
</div>

@if($data->inventory_type==1)
<div class="col-md-6 mb-3">
<label>File</label>
<input class="form-control" value="{{ $data->csvFile }}" readonly>
</div>
@endif

<div class="col-md-6 mb-3">
<label>Total no. of vouchers/codes</label>
<input class="form-control" value="{{ $data->inventory_qty }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Voucher Value ($)</label>
<input class="form-control" value="{{ $data->voucher_value }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>No. of vouchers/codes per set, per member</label>
<input class="form-control" value="{{ $data->voucher_set }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Total no. of sets available for issuance</label>
<input class="form-control" value="{{ $data->set_qty }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Clearing Methods</label>
<input class="form-control"
value="{{ ['QR Code','Barcode','External Code','External Link','Merchant Code'][$data->clearing_method] ?? '' }}" readonly>
</div>

@endif

<!-- PHYSICAL -->
@if($data->reward_type == 1 && $locations->count())
<div class="col-12 mb-3">
    <label>Locations</label>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Location Name</th>
                <th>Inventory Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($locations as $loc)
                <tr>
                    <td>{{ $loc->name }}</td>
                    <td>{{ $savedLocations[$loc->id] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
<!-- SETTINGS -->
<div class="col-md-6 mb-3">
<label>Hide Quantity</label>
<input class="form-control" value="{{ $data->hide_quantity?'Yes':'No' }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Low Stock Reminder 1</label>
<input class="form-control" value="{{ $data->low_stock_1 }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Low Stock Reminder 2</label>
<input class="form-control" value="{{ $data->low_stock_2 }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Friendly URL Name</label>
<input class="form-control" value="{{ $data->friendly_url }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Category</label>
<input class="form-control" value="{{ $data->category->name ?? '' }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>FABS Categories Information</label>
<input type="text" class="form-control"
            value="{{ optional($fabs->firstWhere('id', $data->fabs_category_id))->name }}"
            readonly></div>


<div class="col-md-6 mb-3">
<label>AX Item Code</label>
<input class="form-control" value="{{ $data->ax_item_code }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Publish Channel</label>
<input class="form-control"
value="{{ ($data->publish_independent?'Internet ':'').($data->publish_inhouse?'Inhouse':'') }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Send Reminder</label>
<input class="form-control" value="{{ $data->send_reminder?'Yes':'No' }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Suspend Deal</label>
<input class="form-control" value="{{ $data->suspend_deal?'Yes':'No' }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Suspend Voucher</label>
<input class="form-control" value="{{ $data->suspend_voucher?'Yes':'No' }}" readonly>
</div>

</div>
</div>
</div>
</div>
</div>