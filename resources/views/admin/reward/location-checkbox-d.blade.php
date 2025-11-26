<div>
    @foreach($locations as $location)
        <div class="row mb-2">
            <div class="col-md-1">
                <input type="checkbox" name="locations_digital[{{ $location->id }}][selected]" value="1"
                    class="form-check-input locations_digital_checkbox" data-location-id="{{ $location->id }}"
                    data-location="{{ $location->name.' ('.$location->code.')' }}">
            </div>
            <div class="col-md-5">
                <label class="form-label">{{ $location->name.' ('.$location->code.')' }}</label>
            </div>

        </div>
    @endforeach

</div>