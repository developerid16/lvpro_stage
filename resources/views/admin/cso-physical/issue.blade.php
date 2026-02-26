<!-- Issue Item Modal -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Issue Item:</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="issueForm">
                @csrf

                <div class="modal-body">

                    <input type="hidden" name="purchase_id" id="issue_purchase_id">

                    <p class="fw-bold">
                        Receipt No:
                        <span id="issue_receipt_no"></span>
                    </p>

                    <p>Please confirm you would like to proceed with the purchase?</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Remark:</label>
                        <textarea name="remark" class="form-control"  rows="4" placeholder="Enter remark (optional)"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">PDF:</label>
                        <input type="file" class="form-control" name="file" accept=".pdf">
                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button"  class="btn btn-secondary"  data-bs-dismiss="modal">
                        CLOSE
                    </button>

                    <button type="submit" class="btn btn-primary">
                        PROCEED
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
