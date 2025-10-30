@extends('layouts.master-layouts')

@section('title') Content Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin
@endslot
@slot('title') Application Management @endslot
@endcomponent



<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Contact Us</h4>

    </div>--}}
    <form action="{{url('admin/application-management/save')}}" id="form" enctype="multipart/form-data" method="POST">
        @csrf
        <div class="card-body">


            <div class="row">
                <div class="col-md-4 mb-3">
                    <div>
                        <label class="form-label sh_dec">Maintenance Mode</label>
                        <div class="input-group">

                            <select class="form-select" name="maintenance_mode" id="maintenance_mode">
                                <option value="On" @selected($data['maintenance_mode']==='On' )>On</option>
                                <option value="Off" @selected($data['maintenance_mode']==='Off' )>Off
                                </option>

                            </select>
                        </div>
                    </div>
                    <label class="form-label sh_dec mt-3">Maintenance Title</label>
                    <div class="input-group">
                        <input class="form-control sh_dec" placeholder="Title" name="maintenance_title"
                            value="{{$data['maintenance_title']}}" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Maintenance Descriptions</label>
                    <div class="input-group">
                        <textarea name="maintenance_descriptions" id="" cols="30" rows="10"
                            class="form-control">{{$data['maintenance_descriptions']}}</textarea>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label sh_dec">Maintenance Icon</label>
                    <div class="input-group">
                        <input type="file" name="maintenance_icon" class="form-control"  accept="image/png">
                    </div>
                    <img src="{{asset('images/'.$data['maintenance_icon']) . '?'.rand(1,109)}}" class="mt-3" alt="" srcset="" height="100" width="100">
                </div>

            </div>



        </div>
        <div class="card-footer">
            <button class="sh_btn btn btn-primary mt-3 save-btn" type="submit">
                <i class="mdi mdi-file"></i>
                Save</button>
        </div>
    </form>
</div>


@endsection

@section('script')
 <script>
    var ModuleBaseUrl = "{{url('admin/application-management/save')}}";
     

  
        $(document).ready(function () {
    
   

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
</script>
@endsection