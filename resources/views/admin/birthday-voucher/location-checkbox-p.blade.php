<div>
    @foreach($locations as $location)
        <div class="row mb-2">
            <div class="col-md-1">
                <input type="checkbox" name="locations[{{ $location->id }}][selected]" value="1" class="form-check-input">
            </div>
            <div class="col-md-5">
                <label class="form-label">{{ $location->name }}</label>
            </div>
            <div class="col-md-6">
                <input type="number" min="0" name="locations[{{ $location->id }}][inventory_qty]" class="form-control"
                    placeholder="Quantity" min="0">
            </div>
        </div>
    @endforeach
    <div class="method">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="sh_dec" > Clearing Method <span class="required-hash">*</span></label>
                    <input type="checkbox" checked value="1" class="form-check-input" disabled>
                    <label class="form-label">CSO Issuance</label>

                </div>
            </div>
        </div>
    </div>
</div>