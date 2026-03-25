<div class="modal fade" id="ViewModal" data-bs-backdrop="static">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">View Reward</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body" style="max-height:80vh; overflow:auto;">

<form>

<div class="row">

<!-- Push -->
<div class="col-md-6 mb-3">
<label>Push Method</label>
<select class="form-control" disabled>
<option {{ $data->cso_method == 4 ? 'selected' : '' }}>All Members</option>
<option {{ $data->cso_method == 5 ? 'selected' : '' }}>CSO Issuance</option>
</select>
</div>

<!-- Name -->
<div class="col-md-6 mb-3">
<label>Name</label>
<input type="text" class="form-control" value="{{ $data->name }}" readonly>
</div>

<!-- Image -->
<div class="col-md-6 mb-3">
<label>Voucher Image</label><br>
<img src="{{ imageExists('uploads/image/'.$data->voucher_image) }}" width="80">
</div>

<div class="col-md-6 mb-3">
<label>Detail Image</label><br>
<img src="{{ imageExists('uploads/image/'.$data->voucher_detail_img) }}" width="80">
</div>

<!-- Description -->
<div class="col-md-12 mb-3">
<label>Description</label>
<textarea class="form-control" readonly>{!! strip_tags($data->description) !!}</textarea>
</div>

<!-- Merchant -->
<div class="col-md-6 mb-3">
<label>Merchant</label>
<input type="text" class="form-control" value="{{ $data->merchant->name ?? '' }}" readonly>
</div>

<!-- Type -->
<div class="col-md-6 mb-3">
<label>Voucher Type</label>
<select class="form-control reward_type" disabled>
<option value="0" {{ $data->reward_type==0?'selected':'' }}>Digital</option>
<option value="1" {{ $data->reward_type==1?'selected':'' }}>Physical</option>
</select>
</div>

<!-- Expiry -->
<div class="col-md-6 mb-3">
<label>Expiry Type</label>
<select id="expiry_type" class="form-control" disabled>
<option value="fixed" {{ $data->expiry_type=='fixed'?'selected':'' }}>Fixed</option>
<option value="validity" {{ $data->expiry_type=='validity'?'selected':'' }}>Validity</option>
<option value="no_expiry" {{ $data->expiry_type=='no_expiry'?'selected':'' }}>No Expiry</option>
</select>
</div>

<div class="col-md-6 mb-3" id="fixed_expiry_div">
<label>Expiry Date</label>
<input type="text" class="form-control" value="{{ $data->voucher_validity }}" readonly>
</div>

<div class="col-md-6 mb-3" id="validity_period_div">
<label>Validity Month</label>
<input type="text" class="form-control" value="{{ $data->validity_month }}" readonly>
</div>

<!-- Dates -->
<div class="col-md-6 mb-3">
<label>Publish</label>
<input type="text" class="form-control"
value="{{ $data->publish_start_date }} {{ $data->publish_start_time }} → {{ $data->publish_end_date }} {{ $data->publish_end_time }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Sales</label>
<input type="text" class="form-control"
value="{{ $data->sales_start_date }} {{ $data->sales_start_time }} → {{ $data->sales_end_date }} {{ $data->sales_end_time }}" readonly>
</div>

<!-- Price -->
<div class="col-md-6 mb-3">
<label>Usual Price</label>
<input type="text" class="form-control" value="{{ $data->usual_price }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Max Qty</label>
<input type="text" class="form-control" value="{{ $data->max_quantity }}" readonly>
</div>

<!-- DIGITAL -->
<div id="digital" style="{{ $data->reward_type==0?'':'display:none' }}">

<div class="col-md-6 mb-3">
<label>Inventory Type</label>
<input type="text" class="form-control"
value="{{ $data->inventory_type==0?'Internal':'External' }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Inventory Qty</label>
<input type="text" class="form-control" value="{{ $data->inventory_qty }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Voucher Value</label>
<input type="text" class="form-control" value="{{ $data->voucher_value }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Voucher Set</label>
<input type="text" class="form-control" value="{{ $data->voucher_set }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Set Qty</label>
<input type="text" class="form-control" value="{{ $data->set_qty }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Clearing</label>
<input type="text" class="form-control"
value="{{ ['QR','Barcode','Merchant','Link','Code'][$data->clearing_method] ?? '' }}" readonly>
</div>

</div>

<!-- PHYSICAL -->
<div id="location_section" style="{{ $data->reward_type==1?'':'display:none' }}">

@foreach($savedLocations as $id=>$qty)
<div class="col-md-6 mb-2">
<input type="text" class="form-control"
value="Location {{ $id }} → Qty {{ $qty }}" readonly>
</div>
@endforeach

</div>

<!-- SETTINGS -->
<div class="col-md-6 mb-3">
<label>Hide Qty</label>
<input type="text" class="form-control" value="{{ $data->hide_quantity?'Yes':'No' }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Low Stock 1</label>
<input type="text" class="form-control" value="{{ $data->low_stock_1 }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Low Stock 2</label>
<input type="text" class="form-control" value="{{ $data->low_stock_2 }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Friendly URL</label>
<input type="text" class="form-control" value="{{ $data->friendly_url }}" readonly>
</div>

<div class="col-md-6 mb-3">
<label>AX Code</label>
<input type="text" class="form-control" value="{{ $data->ax_item_code }}" readonly>
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