<div class="modal fade" id="{{ (isset($data->id)) ? 'EditModal' : 'AddModal' }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sh_sub_title">{{ (isset($data->id)) ? 'Edit' : 'Add' }} {{ $title ?? ''}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1" method="POST" action="javascript:void(0)" id="{{ (isset($data->id)) ? 'edit_frm' : 'add_frm' }}" data-id="{{ $data->id ?? ''}}">
                    @csrf
                    @if(isset($data->id)) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="name">Name<span class="required-hash">*</span></label>
                                <input id="name" type="text" class="sh_dec form-control" name="name" placeholder="Enter name" value="{{ $data->name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="category_order">Order<span class="required-hash">*</span></label>
                                <input id="category_order" type="number" min="0" class="sh_dec form-control" name="category_order" placeholder="Enter category order" value="{{ $data->category_order ?? '' }}" min="0">
                                <span>Sorting will be based on Ascending or Descending Numeric Order.


                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">

                                <h5 class="sh_dec mb-1">FAQ For <span class="required-hash">*</span></h5>
                                <div class="d-flex">

                                    <div class="form-check me-2 ">
                                        <input class="form-check-input" value="Chat Bot" type="radio" name="is_for" id="Chat Bot" @checked(isset($data->is_for) && $data->is_for == 'Chat Bot')>
                                        <label class="form-check-label sh_dec" for="Chat Bot">
                                            Chat Bot
                                        </label>
                                    </div>
                                    <div class="form-check me-2">
                                        <input class="form-check-input" value="FAQ" type="radio" name="is_for" id="FAQ" @checked(isset($data->is_for) && $data->is_for == 'FAQ')>
                                        <label class="sh_dec form-check-label" for="FAQ">
                                            FAQ
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" value="Both" type="radio" name="is_for" id="Both" @checked(isset($data->is_for) && $data->is_for == 'Both')>
                                        <label class="form-check-label sh_dec" for="Both">
                                            Both
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="status"> Status<span class="required-hash">*</span></label>
                                <select class="sh_dec form-select " name="status" >
                                    <option class="sh_dec" value="Active" {{ (isset($data->status) && $data->status ==
                                        'Active') ?
                                        'selected' : '' }}>Active</option>
                                    <option class="sh_dec" value="Disabled" {{ (isset($data->status) && $data->status ==
                                        'Disabled') ? 'selected' : '' }}>Disabled</option>
                                </select>
                                <div class="error" id="status_error"></div>
                            </div>
                        </div>

                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" type="reset" onclick="remove_errors()">Reset</button>
                        </div>
                        <div class="col-6 mt-3 d-grid">
                            <button class="sh_btn btn btn-primary waves-effect waves-light" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>