<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 600px;">
        <div class="modal-content p-4">

          <div id="checkoutStep">
            <form id="checkoutForm">

                <!-- hidden -->
                <input type="hidden" name="reward_id" id="checkout_reward_id">
              <input type="hidden" name="member_id" id="checkout_member_id">

              <input type="hidden" name="member_name" id="member_name">
              <input type="hidden" name="member_email" id="member_email">

              <input type="hidden" name="subtotal" id="subtotal">
              <input type="hidden" name="admin_fee" id="admin_fee" value="0">
              <input type="hidden" name="total" id="total">

               

                <div class="row">

                    <!-- LEFT -->
                    <div class="col-md-6">
                        <p>
                            <strong>Email:</strong>
                            <input type="text" id="d_email" class="form-control mb-2" readonly>
                        </p>

                        <p>
                            <strong>Mobile:</strong>
                            <input type="text" id="d_mobile" class="form-control mb-2" readonly>
                        </p>


                        <p>
                            <strong>Mobile:</strong>
                            <input type="text" id="d_mobile" class="form-control mb-2" name="email" readonly>
                        </p>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="no_update"  name="update_membership" >
                            <label class="form-check-label">
                                I DO NOT wish to update my SAFRA member profile
                            </label>
                        </div>

                        <div class="mb-2">
                            <label>Quantity</label>
                            <select id="qty" name="qty" class="form-control">
                                <option value="1">1</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Collection</label>
                            <select id="collection" name="collection" class="form-control">
                                <option>Digital Voucher</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Payment Mode</label>
                            <select id="payment" class="form-control" name="payment_mode">
                                <option>Cash</option>
                                <option>Online</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Note</label>
                            <textarea name="note" class="form-control"></textarea>
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="col-md-6 total-section">
                      <div>
                        <p><strong>Reward:</strong> <span id="d_reward"></span></p>
                        <p><strong>Subtotal:</strong> SGD <span id="d_subtotal"></span></p>
                        <p><strong>Admin Fee:</strong> SGD <span id="d_admin"></span></p>
                        <p><strong>Total:</strong> SGD <span id="d_total"></span></p>

                        <button type="button" class="btn btn-secondary mt-3" id="btnCheckout">
                            CHECK OUT
                        </button>

                      </div>
                    </div>

                </div>
            </form>
          </div>

          <div id="previewStep" style="display:none">

            <h5 class="mb-3">Reward Preview</h5>

            <div class="reward-detail text-center mb-4 mt-4">

              <div class="reward-img mb-3">
                  <img id="reward_image" src="" alt="Reward Image">
              </div>

              <p id="reward_type"></p>
              <p><strong id="reward_name"></strong></p>
              <p id="reward_offer"></p>

              <div class="row mt-3 text-start d-flex justify-content-center">
                  <div class="col-5">
                      <strong>Member Type</strong><br>
                      SAFRA Member<br>
                      SAFRA MovieMax<br>
                      SAFRA Bitez<br>
                      SAFRA Travel Club
                  </div>
                  <div class="col-3 text-end">
                      <strong>Rate (SGD)</strong><br>
                      <span id="rate_member"></span><br>
                      <span id="rate_movie"></span><br>
                      <span id="rate_bitez"></span><br>
                      <span id="rate_travel"></span>
                  </div>
              </div>

              <div class="mt-3">
                  <strong>Sales End:</strong><br>
                  <span id="reward_end"></span>
              </div>

              <div class="mt-2">
                  <strong>Remaining Quantity:</strong>
                  <span id="reward_left"></span>
              </div>

              <hr>

              <div class="d-flex justify-content-between">
                  <button class="btn btn-light" id="btnBack">Back</button>
                  <button class="btn btn-primary" id="btnConfirm">Confirm</button>
              </div>
            </div>
          </div>

          <div id="confirmationStep" style="display:none">

              <h5 class="mb-3">Purchase Confirmation</h5>

              <p><strong>Reward Name:</strong> <span id="reward_name"></span></p>
              <p><strong>Reward Type:</strong> <span id="reward_type"></span></p>
              <p><strong>Receipt No:</strong> <span id="receipt_no"></span></p>
              <p><strong>Date:</strong> <span id="receipt_date"></span></p>

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
