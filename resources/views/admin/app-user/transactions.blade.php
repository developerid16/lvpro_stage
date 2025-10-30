@extends('layouts.master-layouts')

@section('title') Customer Management @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Customer Transactions @endslot
@endcomponent

<div class="row">

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="sh_sub_title fw-semibold">Transactions of {{$user->name}} (The data presented covers the last 2 years, starting from today's date.)</h5>
                <form action="{{ url('admin/app-user-transactions/'.$user->id, []) }}" method="get" class="mb-3">
                    <div class="row">
                       
                        <div class="form-group col-3">
                            <label for="exampleFormControlInput1">Transaction Type</label>
                            <select name="filter" class="form-select">
                                <option value="" @selected(isset($filter['filter']) &&$filter['filter']=='' )>All
                                </option>
                                <option value="transactions" @selected(isset($filter['filter'])
                                    &&$filter['filter']=='transactions' )>Transaction
                                </option>
                                <option value="milestone" @selected(isset($filter['filter'])
                                    &&$filter['filter']=='milestone' )>Other Rewards
                                </option>
                                <option value="purchased" @selected(isset($filter['filter'])
                                    &&$filter['filter']=='purchased' )>Keys used</option>
                            </select>
                        </div>
                        <div class="form-group col-3">
                            <label for="exampleFormControlInput1">Order By</label>
                            <select name="sort" class="form-select">
                                <option value="desc" @selected(isset($filter['sort']) &&$filter['sort']=='desc' )>Most
                                    Recent
                                </option>
                                <option value="asc" @selected(isset($filter['sort']) &&$filter['sort']=='asc' )>Earliest
                                </option>
                            </select>
                        </div>
                        <div class="col-2">


                            <button class="btn btn-primary mt-4" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="sh_table table" id="bstable" data-toggle="table"
                        data-page-list="[100, 500, 1000, 2000, All]" data-page-size="100" data-side-pagination="client"
                        data-pagination="true" data-search="false" data-total-field="count" data-data-field="items"
                        data-show-columns="false" data-show-toggle="false" data-show-export="false"
                        data-filter-control="true" data-show-columns-toggle-all="false">
                        <thead>
                            <tr>
                                <th scope="col">Transaction Details</th>
                                <th scope="row">Keys</th>
                                <th scope="row">Date</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($masterData as $data)


                            <tr>

                                <td scope="col"> {{$data['text_original'] ?? ''}}
                                    @if (isset($data['text_original']))
                                    <br>
                                    @endif
                                    {{$data['text']}}

                                    @if (isset($data['products']))
                                    <br>
                                    @foreach ($data['products'] as $product)
                                    <br>

                                    SKU: {{$product['sku']}} ({{$product['amount']}})

                                    @endforeach
                                    @endif
                                    @if (isset($data['limit_reach']) && $data['limit_reach'] == 1 )
                                    <br> <span class="text-danger">  User reached yearly limit.</span>
                                   
                                    @endif
                                </td>
                                <td scope="col"> {{$data['type'] === 'negative' ? 'Keys Used:' : 'Keys Earned:' }} {{
                                    number_format($data['keys'] ?? '')}} </td>




                                <td>{{$data['date']->format(config('shilla.date-format' ) . " g:i:s a") }}
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

            </div>
        </div>



    </div>


</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/js/crud.js')}}"></script>
@endsection
