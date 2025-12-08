<div class="modal fade" id="{{ isset($data->id) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="sh_sub_title modal-title">
                    {{ isset($data->id) ? 'Edit' : 'Add' }} Participating Merchant Location
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form method="POST" action="javascript:void(0)" 
                      id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}"
                      data-id="{{ $data->id ?? '' }}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif

                    <input type="hidden" name="participating_merchant_id" value="{{ $participating_merchant_id }}">

                    <div class="row">

                        <div class="col-12 col-md-6">
                            <label>Name*</label>
                            <input type="text" name="name" class="form-control" value="{{ $data->name ?? '' }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label>Code*</label>
                            <input type="text" name="code" class="form-control" value="{{ $data->code ?? '' }}">
                        </div>

                       <div class="col-12 col-md-6">
                            <label>Start Date*</label>
                            <input type="datetime-local" name="start_date" id="start_date"
                                class="form-control"
                                value="{{ isset($data->start_date) ? date('Y-m-d\TH:i', strtotime($data->start_date)) : '' }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label>End Date*</label>
                            <input type="datetime-local" name="end_date" id="end_date"
                                class="form-control"
                                value="{{ isset($data->end_date) ? date('Y-m-d\TH:i', strtotime($data->end_date)) : '' }}">
                        </div>


                        <div class="col-12 col-md-6">
                            <label>Participating Merchant*</label>
                            <select name="participating_merchant_id" class="form-select">
                                @foreach($merchants as $merchant)
                                    <option value="{{ $merchant->id }}"
                                        {{ isset($data) && $data->participating_merchant_id == $merchant->id ? 'selected' : '' }}>
                                        {{ $merchant->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label>Club Location*</label>
                            <select name="club_location_id" class="form-select">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}"
                                        {{ isset($data) && $data->club_location_id == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label>Status*</label>
                            <select name="status" class="form-select">
                                <option value="Active" {{ isset($data) && $data->status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ isset($data) && $data->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-6 mt-3 d-grid">
                            <button class="btn btn-outline-danger" type="reset">Reset</button>
                        </div>
                        <div class="col-6 mt-3 d-grid">
                            <button class="btn btn-primary" type="submit">Submit</button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let start = document.getElementById("start_date");
    let end   = document.getElementById("end_date");

    // Disable end date until start date selected
    if (!start.value) {
        end.disabled = true;
    }

    start.addEventListener("change", function () {
        if (start.value) {
            end.disabled = false;
            end.min = start.value; // prevent selecting earlier date
        } else {
            end.disabled = true;
            end.value = "";
        }
    });

});
</script>
