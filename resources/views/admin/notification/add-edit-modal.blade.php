<script>
    $(document).on('shown.bs.modal', '#EditModal', function () {
    
        initFlatpickrDate(this);  
    });  
</script>
<div class="modal fade" id="{{ isset($data->id) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">{{ isset($data->id) ? 'Edit' : 'Add' }} {{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data"  id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}"  data-id="{{ $data->id ?? '' }}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Title *</label>
                            <input type="text" name="title" class="form-control" value="{{ $data->title ?? '' }}" placeholder="Title">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Type *</label>
                            <select name="type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="reminders" {{ ($data->type ?? '') === 'reminders' ? 'selected' : '' }}>Reminders</option>
                                <option value="promotions" {{ ($data->type ?? '') === 'promotions' ? 'selected' : '' }}>Promotions</option>
                                {{-- <option value="my_purchases" {{ ($data->type ?? '') === 'my_purchases' ? 'selected' : '' }}>My Purchases</option> 
                               <option value="redeemed" {{ ($data->type ?? '') === 'redeemed' ? 'selected' : '' }}>Redeemed</option> --}}
                            </select>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label>Date *</label>
                            <input id="date" type="text"  class="sh_dec form-control js-flat-date" name="date" value="{{ isset($data->date) ? \Carbon\Carbon::parse($data->date)->format('Y-m-d') : '' }}" placeholder="YYYY-MM-DD"/>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Image</label>
                            <input type="file" name="img" id="img" class="form-control">
                            <img id="img_preview" src="{{ isset($data->img) ? asset('uploads/image/'.$data->img) : '' }}" style="max-width:50px;  {{ isset($data->img) ? '' : 'display:none' }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Short Description</label>
                            <input type="text" name="short_desc" placeholder="Short Description" class="form-control" value="{{ $data->short_desc ?? '' }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Description</label>
                            <textarea name="desc" class="form-control" rows="3" placeholder="Description">{{ $data->desc ?? '' }}</textarea>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-6 d-grid">
                            <button type="reset" class="btn btn-outline-danger">Reset</button>
                        </div>
                        <div class="col-6 d-grid">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
