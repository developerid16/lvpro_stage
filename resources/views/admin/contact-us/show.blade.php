@extends('layouts.master-layouts')

@section('title') Contact Us  @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Contact Us  @endslot
@endcomponent

<div class="row">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold sh_sub_title">Customer Information</h5>

                <div class="table-responsive">
                    <table class="table sh_table">
                        <tbody>

                            <tr>
                                <th scope="row">Name</th>
                                <td>{{$data->name}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Email</th>
                                <td>{{$data->email}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Mobile</th>
                                <td>{{$data->mobile}}</td>
                            </tr>
                            <!-- <tr>
                                <th scope="row">Gender</th>
                                <td>{{$data->gender}}</td>
                            </tr> -->

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold sh_sub_title">Inquiry Information</h5>

                <div class="table-responsive">
                    <table class="table sh_table">
                        <tbody>
                            <tr>
                                <th scope="col">Category</th>
                                <td scope="col">{{$data->category}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Subject</th>
                                <td>{{$data->subject}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Message</th>
                                <td style="white-space: pre-line;">{{$data->message}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Date</th>
                                <td>{{$data->created_at->format(config('shilla.date-format'))}}</td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>


</div>

@endsection