@extends('layouts.master-layouts')

@section('title') Redeem Voucher @endsection
@section('content')

    @component('components.breadcrumb')
    @slot('li_1') Admin @endslot
    @slot('li_1_link') {{url('/')}} @endslot
    @slot('title') Redeem Voucher @endslot
    @endcomponent




    <div class="row">


        <div class="col-xl-8" id="redeemVoucherSection">
            <div class="card h-100">
                <div class="card-body" id="redeemVoucherSection">
                    <h5 class="fw-semibold sh_sub_title">Redeem Voucher</h5>

                    <form action="{{url('admin/redeem-voucher')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">




                            <div class="col-12 mt-3">
                                <label class="sh_dec" for="status"> Voucher Code </label>
                                <input name="reason" id="code" class="sh_dec form-control " spellcheck="false">
                            </div>
                            @if(count($errors) > 0)
                                <div class="p-1">
                                    @foreach($errors->all() as $error)
                                        <div class="alert alert-warning alert-danger fade show" role="alert">{{$error}} </div>
                                    @endforeach
                                </div>
                            @endif
                            @if(Session::has('message'))
                                <div class="col-12">
                                    <div class="alert alert-warning alert-success fade show">{{ Session::get('message') }}</p>
                                    </div>
                            @endif
                            </div>
                            <div class="col-12 mt-4">
                                <button class="btn btn-info waves-effect waves-light" type="reset">Reset</button>
                                <button class="btn btn-success waves-effect waves-light" type="button"
                                    onclick="startQRScanner()">Scan QR Code</button>
                                <button class="btn btn-primary waves-effect waves-light" type="button"
                                    onclick="verifyVoucher()">Submit</button>
                            </div>
                        </div>
                    </form>

                </div>


            </div>


        </div>



        <div class="col-xl-8" id="thankYouSection" style="display: none;">
            <div class="card h-100">
                <div class="card-body text-center">

                    <h5 class="fw-semibold sh_sub_title">Thank you</h5>
                    <p>Your voucher has been successfully redeemed.</p>

                </div>
            </div>
        </div>

    </div>

    <!-- QR Scanner Modal -->
    <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrScannerModalLabel">Scan QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        onclick="stopQRScanner()"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qr-reader" style="width: 100%;"></div>
                    <div id="qr-reader-results"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="stopQRScanner()">Close</button>
                </div>
            </div>
        </div>
    </div>




@endsection

@section('script')
    <!-- QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let html5QrcodeScanner = null;

        $(document).ready(function () {

        });


        function startQRScanner() {
            $('#qrScannerModal').modal('show');

            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5Qrcode("qr-reader");
            }

            // Use back camera directly
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            html5QrcodeScanner.start(
                { facingMode: "environment" }, // force back camera
                config,
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                console.error("Unable to start scanning", err);
            });
        }

        // function startQRScanner() {
        //     // Show the modal
        //     $('#qrScannerModal').modal('show');

        //     // Initialize QR Scanner
        //     html5QrcodeScanner = new Html5QrcodeScanner(
        //         "qr-reader",
        //         {
        //             fps: 10,
        //             qrbox: { width: 250, height: 250 },
        //             facingMode: "environment"
        //                 aspectRatio: 1.0
        //         },
        //             /* verbose= */ false
        //     );

        //     html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        // }

        function onScanSuccess(decodedText, decodedResult) {
            // Handle successful scan
            console.log(`Code matched = ${decodedText}`, decodedResult);

            // Set the scanned code in the input field
            $('#code').val(decodedText);

            // Stop scanning and close modal

            // Automatically verify the scanned voucher
            verifyVoucher();
            stopQRScanner();
        }

        function onScanFailure(error) {
            // Handle scan failure - usually just ignore these as they're frequent
            // console.warn(`Code scan error = ${error}`);
        }

        function stopQRScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop()
                    .then(() => {
                        console.log("QR scanner stopped.");
                        return html5QrcodeScanner.clear(); // only after stopped
                    })
                    .then(() => {
                        console.log("QR scanner cleared.");
                    })
                    .catch(err => {
                        console.error("Failed to stop/clear scanner", err);
                    });
                html5QrcodeScanner = null;
            }
            $('#qrScannerModal').modal('hide');
        }

        function verifyVoucher() {
            var code = $('#code').val();
            if (code == '') {
                alert('Please enter voucher code');
                return false;
            }

            // make ajax call here
            $.ajax({
                url: "{{url('admin/redeem-voucher')}}",
                type: "POST",
                data: {
                    _token: '{{csrf_token()}}',
                    Voucher_No: code
                },
                success: function (response) {
                    console.log(response);
                    $('#redeemVoucherSection').hide();
                    $('#thankYouSection').show();
                    show_message('', response.status.status_message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                },
                error: function (xhr) {
                    console.log(xhr.responseJSON.status.status_message);
                    show_message('fail', xhr.responseJSON.status.status_message);
                }
            });
        }

        // Clean up scanner when modal is closed
        $('#qrScannerModal').on('hidden.bs.modal', function (e) {
            stopQRScanner();
        });
    </script>
@endsection