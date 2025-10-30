@extends('layouts.master-layouts')

@section('title') Sales Report @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('li_1_link') {{url('/')}} @endslot
@slot('title') Sales Report @endslot
@endcomponent

<style>
    /* .select2-container--open{
        top:-8% !important
    } */
</style>

<div class="card">
    {{--<div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Sales Report</h4>

    </div>--}}
    <form action="">
        <div class="card-body product-select2">
            <div class="row">

                <div class="col-lg-4">
                    <div class="sh_dec">
                        <label class="sh_dec form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control start_date" value="{{$sd}}" max="{{date('Y-m-d')}}" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sh_dec">
                        <label class="sh_dec form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control end_date" value="{{$ed}}" max="{{date('Y-m-d')}}" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div>
                        <label class="sh_dec form-label">Brands</label>
                        <select name="brand" class="sh_dec form-control ">
                            <option value="">All</option>
                            @foreach ($brands as $brand)

                            <option class="sh_dec" value="{{$brand->brand_code}}" @selected($brand->brand_code && $bs === $brand->brand_code
                                )>{{$brand->brand_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="" style="position: relative">
                        <label class="sh_dec form-label">Product</label>
                        <select name="product" class="sh_dec form-control " id="product-select">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button class="sh_btn btn btn-primary">Search</button>
        </div>
    </form>
</div>

@if (count($data) > 0)

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom mb-3">
        <h4 class="card-title mb-0">Data</h4>

       <a class="btn btn-sm btn-primary" href="{{url('admin/report/sales-download')  . '?' . http_build_query(request()->query())  }}">Download Data</a>

    </div>
    <div class="card-body pt-0 sh_table_btn">
        <div class="table-responsive">
            <table class="sh_table table table-bordered"  >
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>POS</th>
                        <th>Loyalty</th>
                        <th>Location</th>
                        <th>Storage Location</th>
                        <th>Brand</th>
                        <th>Product</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Ref</th>
                        <th>SKU</th>
                        <th>Sale Amount</th>
                        <th>Quantity Issuance</th>
                        <th>Key Earn</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)


                    <tr>
                        <td>
                            {{$loop->iteration}}
                        </td>
                        <td>
                            {{$item->pos}}
                        </td>
                        <td>
                            {{$item->loyalty}}
                        </td>
                        <td>
                            {{$item->location}}
                        </td>
                        <td>
                            {{$item->storage_location}}
                        </td>
                        <td>
                            {{$item->brand->brand_name ?? ''}}
                        </td>
                        <td>
                            {{$item->brand->product_name ?? ''}}
                        </td>
                        <td>
                            {{$item->date->format(config('shilla.date-format'))   }}
                        </td>
                        <td>
                        {{$item->system_time }}
                        </td>
                        <td>
                            {{$item->ref}}
                        </td>
                        <td>
                            {{$item->sku}}
                        </td>
                        <td>
                            {{numberFormat($item->sale_amount,true)}}
                        </td>
                        <td>
                            {{$item->quantity_purchased}}
                        </td>
                        <td>
                            {{ number_format( $item->key_earn) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $data->links() }}
        </div>
    </div>
</div>
@endif


@if (count($data) === 0 &&isset($sd))
<p class="text-center ">No data found</p>
@endif

<!-- Create -->

<!-- end modal -->
@endsection

@section('script')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" integrity="sha512-ZnR2wlLbSbr8/c9AgLg3jQPAattCUImNsae6NHYnS9KrIwRdcY9DxFotXhNAKIKbAXlRnujIqUWoXXwqyFOeIQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />


<script>
    $(document).ready(function() {
          $("#product-select").select2({
             ajax: {
                url: "{{url('admin/products/search')}}",
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchTerm: params.term // search term
                    };
                },
                processResults: function(response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        });
    });
</script>

@endsection