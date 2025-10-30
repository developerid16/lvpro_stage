@extends('layouts.master-layouts')

@section('title') QR Setting @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('title') QR Setting @endslot
@endcomponent


<div class="row">
    <div class="col-md-9"><div class="card">
        {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
            <h4 class="card-title mb-0">Notification Setting</h4>
    
        </div>--}}
        <form action="" id="form" enctype="multipart/form-data">
            <div class="card-body">
    
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label sh_dec">Dots Style</label>
                        <div class="input-group">
    
                            <select class="form-select" name="dotsOptionsType" id="dotsOptionsType">
                                <option value="square" @selected($data['dotsOptionsType']==='square' )>Square</option>
                                {{-- <option value="dots">Dots</option> --}}
                                <option value="rounded" @selected($data['dotsOptionsType']==='rounded' )>Rounded</option>
                                <option value="extra-rounded" @selected($data['dotsOptionsType']==='extra-rounded' )>Extra
                                    rounded</option>
                                <option value="classy" @selected($data['dotsOptionsType']==='classy' )>Classy</option>
                                <option value="classy-rounded" @selected($data['dotsOptionsType']==='classy-rounded' )>
                                    Classy rounded</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label sh_dec">Dots Color</label>
                        <div class="input-group">
    
                            <input type="color" required name="dotsOptionsColor" value="{{$data['dotsOptionsColor']}}"
                                id="dotsOptionsColor">
                        </div>
                    </div>
    
                    <div class="col-md-4 mb-3">
                        <label class="form-label sh_dec">Corners Square Style</label>
                        <div class="input-group">
    
                            <select class="form-select" name="cornersSquareOptionsType" id="cornersSquareOptionsType">
                                <option value="" @selected($data['cornersSquareOptionsType']==='' )>None</option>
                                <option value="square" @selected($data['cornersSquareOptionsType']==='square' )>Square
                                </option>
                                <option value="dot" @selected($data['cornersSquareOptionsType']==='dot' )>Dot</option>
                                <option value="extra-rounded" @selected($data['cornersSquareOptionsType']==='extra-rounded'
                                    )>Extra
                                    rounded</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label sh_dec">Corners Square Color</label>
                        <div class="input-group">
    
                            <input type="color" required name="cornersSquareOptionsColor"
                                value="{{$data['cornersSquareOptionsColor']}}" id="cornersSquareOptionsColor">
                        </div>
                    </div>
    
                </div>
    
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label sh_dec">Background Color</label>
                        <div class="input-group">
                            <input type="color" required name="backgroundOptionsColor"
                                value="{{$data['backgroundOptionsColor']}}" id="backgroundOptionsColor">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label sh_dec">Corners Dot Style</label>
                        <div class="input-group">
                            <select name="cornersDotOptionsType" class="form-select" id="cornersDotOptionsType">
                                <option>None</option>
                                <option value="square" @selected($data['cornersDotOptionsType']==='square' )>Square</option>
                                <option value="dot" @selected($data['cornersDotOptionsType']==='dot' )>Dot</option>
                            </select>
                        </div>
                    </div>
    
                    <div class="col-md-3 mb-3">
                        <label class="form-label sh_dec">Image Margin</label>
                        <div class="input-group">
                            <div class="input-group-text sh_dec">Px</div>
                            <input type="number" min="1" class="sh_dec form-control" placeholder=""
                                name="imageOptionsMargin" required value="{{$data['imageOptionsMargin']}}"
                                id="imageOptionsMargin">
                        </div>
                    </div>
    
                    <div class="col-md-3 mb-3">
                        <label class="form-label sh_dec">Image </label>
                        <div class="input-group">
                            <input type="file" accept=".png" class="sh_dec form-control" name="QrImage"
                                onchange="imageChange(event)">
                        </div>
                        <img src="{{asset('images/qr.png')}}" alt="" srcset="" height="100" width="100">
                    </div>
    
    
    
                </div>
    
    
    
    
            </div>
            <div class="card-footer">
                <button class="sh_btn btn btn-primary mt-3 save-btn" type="submit"><i class="mdi mdi-file"></i>
                    Save</button>
                <button class="btn btn-warning mt-3  " type="button" onclick="previewQRCode()">
                    Click to Preview</button>
            </div>
        </form>
    </div></div>
    <div class="col-md-3"><div class="card">
        <div class="card-header">
            Preview
        </div>
        <div class="card-body" id="canvas-body">
            <div id="canvas"></div>
        </div>
    </div></div>
</div>





@endsection

@section('script')
<script type="text/javascript" src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
<script>
    var ModuleBaseUrl = "{{route('admin.qr-setting.store') }}";
     var image = "{{asset('images/qr.png')}}"

  
        $(document).ready(function () {
    
       const qrCode = new QRCodeStyling({
        width: 250,
        height: 250,
        type: "svg",
        data: "Please do not scan me.",
        image:image,
        dotsOptions: {
            color: $('#dotsOptionsColor').val(),
            type: $('#dotsOptionsType').val()
        },
        backgroundOptions: {
            color: $('#backgroundOptionsColor').val(),
        },
        imageOptions: {
            crossOrigin: "anonymous",
            margin: $('#imageOptionsMargin').val()
        },
         cornersSquareOptions: {
                type: $('#cornersSquareOptionsType').val(),
                color:$('#cornersSquareOptionsColor').val()
            },
            cornersDotOptions: {
                type: $('#cornersDotOptionsType').val()
            }
    });

    qrCode.append(document.getElementById("canvas"));

        $(document).on("submit","#form",function (e) {
            e.preventDefault()
            const btn = $('.save-btn');
            btn.attr("disabled", true);
            show_message("success","Please wait...");
              var form_data = new FormData($('#form')[0]);
             
            $.ajax({
                url: ModuleBaseUrl,
                headers : {
                    'X-CSRF-Token' : "{{ csrf_token() }}",
                },
                type:"POST",
                data: form_data,
                processData: false,
                contentType: false,
                success:function(response){
                    show_message(response.status,response.message);
                    window.location.reload();
                      
            btn.attr("disabled", false);
                },
                error: function(response) {
            btn.attr("disabled", false);
                    console.log("response",response);
                 }
            });
        });

        
        
    });
    async function imageChange(event){
        console.log('event',event);
        const file = event.target.files[0];
        image = await convertBase64(file);
        
    }
    const convertBase64 = (file) => {
    return new Promise((resolve, reject) => {
        const fileReader = new FileReader();
        fileReader.readAsDataURL(file);

        fileReader.onload = () => {
            resolve(fileReader.result);
        };

        fileReader.onerror = (error) => {
            reject(error);
        };
    });
};

    function previewQRCode(){

        $('#canvas-body #canvas').remove()
        $('#canvas-body').append('<div id="canvas"></div>')
        const qrCode = new QRCodeStyling({
        width: 250,
        height: 250,
        type: "svg",
        data: "Please do not scan me.",
        image:image,
        dotsOptions: {
            color: $('#dotsOptionsColor').val(),
            type: $('#dotsOptionsType').val()
        },
        backgroundOptions: {
            color: $('#backgroundOptionsColor').val(),
        },
        imageOptions: {
            crossOrigin: "anonymous",
            margin: $('#imageOptionsMargin').val()
        },
         cornersSquareOptions: {
                type: $('#cornersSquareOptionsType').val(),
                color:$('#cornersSquareOptionsColor').val()
            },
            cornersDotOptions: {
                type: $('#cornersDotOptionsType').val()
            }
    });

    qrCode.append(document.getElementById("canvas"));

    }
</script>
@endsection