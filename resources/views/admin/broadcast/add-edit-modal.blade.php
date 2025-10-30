<div class="modal fade" id="{{ isset($data->id) ? 'EditModal' : 'AddModal' }}" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sh_sub_title">{{ isset($data->id) ? 'Edit' : 'Add' }} {{ $title ?? '' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="z-index-1 brodcastform" enctype="multipart/form-data" method="POST"
                    action="javascript:void(0)" id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}"
                    data-id="{{ $data->id ?? '' }}">
                    @csrf

                    <input type="hidden" name="is_type" value="" id="is_type">

                    @if (isset($data->id))
                        @method('PATCH')
                    @endif
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="title">Message Title<span
                                        class="required-hash">*</span></label>
                                <input id="title" type="text" class="sh_dec form-control" name="title"
                                    placeholder="Enter title" value="{{ $data->title ?? '' }}" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 hide-edm">
                            <div>
                                <label class="sh_dec" for="date_of_publish">Broadcast Message Date & Time<span
                                        class="required-hash">*</span></label>
                                <input id="date_of_publish" type="text" class="sh_dec form-control datetimepicker"
                                    name="date_of_publish" value="{{ $data->date_of_publish ?? '' }}">
                            </div>
                            <div class="form-group form-check mb-3 pt-3">
                                <input type="checkbox" name="send_now" value="1" class="form-check-input"
                                    id="send_now" onchange="disableNow(event)">
                                <label class="form-check-label" for="send_now">Send notifications instant </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-12 hide-edm mb-3">
                            <div class="d-flex justify-content-between">

                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="SMS"
                                        data-divid="sms-broadcast" onchange="handleCheckboxChange(this,'{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}')"
                                        @checked($issmsvalid)>
                                    <label class="form-check-label" for="SMS">SMS</label>
                                    <br>
                                    <span class="text-danger">Total SMS will be sent to {{ number_format($total_sms) }}
                                        users</span>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="EmailCheck"
                                        data-divid="email-broadcast" onchange="handleCheckboxChange(this,'{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}')"
                                        @checked($isemailvalid)>
                                    <label class="form-check-label" for="EmailCheck">Email</label>
                                    <br>
                                    <span class="text-danger">Total Emails will be sent
                                        {{ number_format($total_email) }} users</span>
                                </div>

                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="PushNotification"
                                        data-divid="push-broadcast" onchange="handleCheckboxChange(this,'{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}')"
                                        @checked($isnotivalid)>
                                    <label class="form-check-label" for="PushNotification">Push Notifications</label>
                                    <br>
                                    <span class="text-danger">Total PN will be sent to {{ number_format($total_noti) }}
                                        users</span>
                                </div>
                            </div>

                        </div>

                        <div id="sms-broadcast" @if (!$issmsvalid) style="display: none" @endif>
                            <div class="col-12 col-md-12 hide-edm">
                                <div class="mb-3">
                                    <label class="sh_dec" for="sms_content">SMS content</label>
                                    <input id="sms_content" type="text" class="sh_dec form-control"
                                        name="sms_content" value="{{ $data->sms_content ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div id="email-broadcast" @if (!$isemailvalid) style="display: none" @endif>
                            <hr class="dashed ">
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="email_subject">Email Subject content </label>
                                    <input id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}-email_subject"
                                        type="text" class="sh_dec form-control mail_subject" name="email_subject"
                                        value="{{ $data->email_subject ?? '' }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="email_content">Email content</label>

                                    <textarea class="elm1 sh_dec" rows="3" name="email_content"
                                        id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}-email_content"> {{ $data->email_content ?? '' }} </textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="mb-3">
                                    <label class="sh_dec" for="emails">Attchments </label>
                                    <input id="attachments" type="file" class="sh_dec form-control email_csv"
                                        name="attachments[]" multiple>
                                    <span>You can select multiple file at a time.</span>
                                </div>
                            </div>
                        </div>


                        <div @if (isset($data->id)) style="display: none;" @endif>



                            <div class="col-6 col-md-6 hide-broadcast">
                                <div class="mb-3">
                                    <label class="sh_dec" for="emails">Testing E-Email </label>
                                    <input id="{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}-emails" type="text"
                                        class="sh_dec form-control emails" name="emails" value="">
                                    <span>Use , to for multiple emails.</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-6 hide-broadcast">
                                <div class="mb-3">
                                    <label class="sh_dec" for="emails">CSV File <a
                                            href="{{ url('admin/download-democsv-user') }}" class="text-danger"
                                            target="_blank">Demo CSV</a> </label>
                                    <input id="email_csv" type="file" class="sh_dec form-control email_csv"
                                        name="email_csv" accept=".csv">
                                </div>
                            </div>
                        </div>
                        <div id="push-broadcast" @if (!$isnotivalid) style="display: none" @endif>
                            <hr class="dashed hide-edm">
                            <div class="col-12 col-md-12 hide-edm">
                                <div class="mb-3">
                                    <label for="inapp_title">In-App Title</label>
                                    <input id="inapp_title" type="text" class="form-control" name="inapp_title"
                                        value="{{ $data->inapp_title ?? '' }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-12 hide-edm">
                                <div class="mb-3">
                                    <label for="inapp_content">In-App content</label>

                                    <textarea class="elm1" name="inapp_content"> {{ $data->inapp_content ?? '' }} </textarea>
                                </div>
                            </div>

                            <hr class="dashed hide-edm">
                            <div class="col-12 col-md-6 hide-edm">
                                <div class="mb-3">
                                    <label class="sh_dec" for="push_title">Push Title</label>
                                    <input id="push_title" type="text" class="sh_dec form-control"
                                        name="push_title" value="{{ $data->push_title ?? '' }}">
                                </div>
                            </div>
                            <div class="col-6 col-md-6 hide-edm">
                                <div class="mb-3">
                                    <label class="sh_dec" for="push_subtitle">Push Sub Title </label>
                                    <input id="push_subtitle" type="text" class="sh_dec form-control"
                                        name="push_subtitle" value="{{ $data->push_subtitle ?? '' }}">
                                </div>
                            </div>
                        </div>


                        <div class="col-12 col-md-6 d-none">
                            <div class="mb-3">
                                <label class="sh_dec" for="status">Type <span
                                        class="required-hash">*</span></label>
                                <select class="sh_dec form-select" name="type" id="type" required>
                                    <option class="sh_dec" value="Promotion"
                                        {{ isset($data->type) && $data->type == 'Promotion' ? 'selected' : '' }}>
                                        Promotion</option>
                                    <option class="sh_dec" value="Other"
                                        {{ isset($data->type) && $data->type == 'Other' ? 'selected' : '' }}>
                                        Other</option>
                                </select>
                                <div class="error sh_dec_s" id="type_error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-4 mt-3 d-grid">
                            <button class="sh_btn_sec btn btn-outline-danger waves-effect waves-light" type="reset"
                                onclick="remove_errors()">Reset</button>
                        </div>
                        <div class="col-4 mt-3 d-grid">

                            <button class="sh_btn btn btn-primary waves-effect waves-light" type="button"
                                onclick="askforconfirmation('{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}')">Submit</button>

                        </div>
                        <div class="col-4 mt-3 d-grid " @if (isset($data->id)) style="display: none !important;" @endif>

                            <button class="btn btn-info waves-effect waves-light hide-broadcast" type="button"
                                id="send-test-email"
                                onclick="sendTestTemplate('{{ isset($data->id) ? 'edit_frm' : 'add_frm' }}')">Test
                                Broadcast email</button>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
