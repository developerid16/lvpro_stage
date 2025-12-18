<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approval Required</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding:30px;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <!-- Header -->
                    <h4 class="fw-bold mb-3">
                        <img src="{{ asset('/build/images/logo-dark.png') }}" alt="" height="50"> <span class="fw-normal"></span>
                    </h4>
                    <hr>

                    <!-- Greeting -->
                    <p class="fw-semibold mb-2">Hi John,</p>
                    <p>You have a <strong>pending approval request</strong> awaiting your action.</p>

                    <!-- Request Details -->
                    <h6 class="fw-bold mt-4">Request Details</h6>
                    <ul class="mb-3">
                        <li><strong>Reward Name:</strong> Old Chang Kee Physical Voucher</li>
                        <li><strong>Requested By:</strong> Albin</li>
                        <li><strong>Request Date &amp; Time:</strong> 2024-04-25 10:15 AM</li>
                    </ul>

                    <p>Please review the request and take the necessary action.</p>

                    <!-- Actions -->
                    <h6 class="fw-bold mt-4 mb-3">Actions</h6>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="#" class="btn btn-warning text-white fw-semibold">
                            ➜ View Request
                        </a>
                        <a href="#" class="btn btn-success fw-semibold">
                            ✓ Approve Request
                        </a>
                        <a href="#" class="btn btn-danger fw-semibold">
                            ✕ Reject Request
                        </a>
                    </div>

                    <!-- Info Box -->
                    <div class="alert alert-light border d-flex align-items-start">
                        <span class="me-2">📌</span>
                        <div>
                            You may approve or reject this request directly
                            <strong>using the links above</strong>.<br>
                            Alternatively, you can log in to the system and navigate to the
                            <strong>Approval</strong> section for more details.
                        </div>
                    </div>

                    <p class="mb-4">
                        If no action is taken, the request will remain in
                        <em>pending</em> status.
                    </p>

                    <!-- Footer -->
                    <p class="mb-1">Regards,</p>
                    <p class="fw-semibold">Approval System Team</p>

                </div>

                <!-- Bottom Footer -->
                <div class="card-footer bg-white text-center small text-muted">
                    This is an electronically generated email. Please do not reply.<br>
                    For any queries, contact us at:
                    <a href="mailto:support@example.com">support@example.com</a><br>
                    © {{ date('Y') }} Example Company. All rights reserved.

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
