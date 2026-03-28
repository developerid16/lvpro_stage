<div class="modal fade" id="ViewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title">View Birthday Voucher</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
        <div class="row">

        <!-- Month -->
        <div class="col-md-6 mb-3">
        <label>Select Month</label>
        <input class="form-control" value="{{ $data->month }}" readonly>
        </div>

        <!-- Voucher Name -->
        <div class="col-md-6 mb-3">
        <label>Voucher Name</label>
        <input class="form-control" value="{{ $data->name }}" readonly>
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
        <input class="form-control" value="{{ $data->merchant->name ?? '' }}" readonly>
        </div>

        <!-- Voucher Type -->
        <div class="col-md-6 mb-3">
        <label>Voucher Type</label>
        <input class="form-control" value="Birthday Voucher" readonly>
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

        <!-- Inventory -->
        <div class="col-md-6 mb-3">
        <label>Inventory Type</label>
        <input class="form-control" value="System Generated Code" readonly>
        </div>

        <div class="col-md-6 mb-3">
        <label>Inventory Quantity</label>
        <input class="form-control" value="{{ $data->inventory_qty }}" readonly>
        </div>

        <!-- Voucher -->
        <div class="col-md-6 mb-3">
        <label>Voucher Value ($)</label>
        <input class="form-control" value="{{ $data->voucher_value }}" readonly>
        </div>

        <div class="col-md-6 mb-3">
        <label>Voucher Set (Per Transaction)</label>
        <input class="form-control" value="{{ $data->voucher_set }}" readonly>
        </div>

        <div class="col-md-6 mb-3">
        <label>Voucher Set Quantity</label>
        <input class="form-control" value="{{ $data->set_qty }}" readonly>
        </div>

        <div class="col-md-6 mb-3">
        <label>Clearing Methods</label>
        <input class="form-control" value="Merchant Code" readonly>
        </div>

        </div>

        <!-- LOCATION SECTION -->
<div class="accordion mt-3">

@foreach($club_location as $club)

@php
$inventory = $clubInventory[$club->id] ?? null;
$merchants = $clubMerchants[$club->id] ?? [];
@endphp

@if($inventory)

<div class="card mb-2">

    <!-- CLUB -->
    <div class="card-header">
        <strong>{{ $club->name }}</strong>
        <span class="ms-3">Qty: {{ $inventory }}</span>
    </div>

    <div class="card-body">

        @foreach($merchants as $merchant)

            <!-- MERCHANT -->
            <div class="mb-2">
                <b>{{ $merchant['merchant_name'] }}</b>
            </div>

            <!-- OUTLETS -->
            @foreach($merchant['outlets'] as $outlet)
                <div>☑ {{ $outlet['name'] }}</div>
            @endforeach

        @endforeach

    </div>

</div>

@endif

@endforeach

</div>

        <!-- SETTINGS -->
        <div class="row mt-3">

        <div class="col-md-6 mb-3">
        <label>Hide Quantity</label>
        <input class="form-control" value="{{ $data->hide_quantity ? 'Yes':'No' }}" readonly>
        </div>

        <div class="col-md-6 mb-3">
        <label>Low Stock Reminder 1</label>
        <input class="form-control" value="{{ $data->low_stock_1 }}" readonly>
        </div>

        <div class="col-md-6 mb-3">
        <label>Low Stock Reminder 2</label>
        <input class="form-control" value="{{ $data->low_stock_2 }}" readonly>
        </div>

        </div>

        </div>
        </div>
    </div>
</div>