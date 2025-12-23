<div class="modal fade" id="checkoutModal" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
           <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalTitle"></h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>



            <div id="checkoutStep" class="p-4">
                <form id="checkoutForm">

                    <!-- hidden -->
                    <input type="hidden" name="reward_id" id="checkout_reward_id">
                    <input type="hidden" name="member_id" id="checkout_member_id">

                    <input type="hidden" name="member_name" id="member_name">
                    <input type="hidden" name="member_email" id="member_email">

                    <input type="hidden" name="subtotal" id="subtotal">
                    <input type="hidden" name="admin_fee" id="admin_fee" value="0">
                    <input type="hidden" name="total" id="total">
                    <input type="hidden" name="d_reward" id="d_reward">
                    
                

                    <div class="row">
                        <div class="col-md-12 col-lg-12">
                            <div class="mb-3">
                                <label class="sh_dec" for="">Name</label>
                                <input type="text" id="d_name" class="sh_dec form-control mb-2" readonly value="test">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="">Email</label>
                                <input type="text" id="d_email" class="sh_dec form-control mb-2" readonly value="test@gmail.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="mb-3">
                                <label class="sh_dec" for="">Mobile</label>
                                <input type="text" id="d_mobile" class="sh_dec form-control mb-2" readonly value="88888888888">
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="no_update"  name="update_membership" >
                                <label class="sh_dec" class="form-check-label">
                                    I DO NOT wish to update my SAFRA member profile
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="mb-3">
                                <label class="sh_dec">Quantity</label>
                                <select id="qty" name="qty" class="sh_dec form-control">
                                    <option value="1">1</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="mb-3">
                                <label class="sh_dec">Collection</label>
                                <select id="collection" name="collection" class="sh_dec form-control">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="mb-3">
                                <label class="sh_dec">Payment Mode</label>
                                <select id="payment" class="sh_dec form-control" name="payment_mode">
                                    <option>Cash</option>
                                    <option>Online</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <div class="mb-3">
                                <label class="sh_dec">Note</label>
                                <textarea name="note" class="sh_dec form-control" rows="1"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <label class="sh_dec">Preview</label>
                             <div class="reward-detail text-left">

                                <div class="reward-img mb-3">
                                    <img id="reward_image" src="" alt="Reward Image">
                                </div>

                                <p id="reward_type"></p>
                                <p><strong id="reward_name"></strong></p>
                                <p id="reward_offer"></p>

                                <div class="row mt-3 text-left d-flex justify-content-between">
                                    <div class="col-5">
                                        <strong>Member Type</strong><br>
                                        SAFRA Member<br>
                                        SAFRA MovieMax<br>
                                        SAFRA Bitez<br>
                                        SAFRA Travel Club
                                    </div>
                                    <div class="col-3 text-left">
                                        <strong>Rate (SGD)</strong><br>
                                        <span id="rate_member"></span><br>
                                        <span id="rate_movie"></span><br>
                                        <span id="rate_bitez"></span><br>
                                        <span id="rate_travel"></span>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <strong>Sales End Date Time:</strong><br>
                                    <span id="reward_end"></span>
                                </div>

                                <div class="mt-2">
                                    <strong>Remaining Quantity at Club:</strong>
                                    <span id="reward_left"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 total-section">
                            <div>                       
                                <p><strong>Subtotal:</strong> SGD <span id="d_subtotal"></span></p>
                                <p><strong>Admin Fee:</strong> SGD <span id="d_admin"></span></p>
                                <p><strong>Total:</strong> SGD <span id="d_total"></span></p>
    
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-secondary me-2" data-bs-toggle="modal" id="btnBackToMember"  data-bs-target="#memberModal" data-reward-id="">
                                        Back
                                    </button>
    
                                    <button type="button" class="btn btn-primary" id="btnConfirm">
                                        CHECK OUT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>


            <div id="confirmationStep" style="display:none" class="p-4">

                <div class="d-flex justify-content-between">
                    <div>
                        <p class="m-0"><strong>Reward Name:</strong> <span class="name"></span></p>
                        <p class="m-0"><strong>Reward Type:</strong> <span class="type"></span></p>
                    </div>
                    <div>
                        <p class="m-0"><strong>Receipt No:</strong> <span id="receipt_no"></span></p>
                        <p class="m-0"><strong>Date:</strong> <span id="receipt_date"></span></p>
                    </div>
                </div>

                <table class="table table-bordered mt-3">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td id="confirm_reward"></td>
                        <td id="confirm_qty"></td>
                        <td>SGD <span id="confirm_price"></span></td>
                        <td>SGD <span id="confirm_amount"></span></td>
                    </tr>
                    </tbody>
                </table>

                <div class="text-end">
                    <p>Subtotal: SGD <span id="confirm_subtotal"></span></p>
                    <p>Admin Fee: SGD 0.00</p>
                    <p>Payment Mode: <span id="payment_mode"></span></p>
                    <h6>Total: SGD <span id="confirm_total"></span></h6>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button class="btn btn-danger" id="btnCancelPurchase">
                        Cancel
                    </button>

                    <button class="btn btn-success" id="btnCompletePurchase">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
