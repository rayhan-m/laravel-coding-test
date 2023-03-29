@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">

                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                            class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Title</th>
                            <th width="25%">Description</th>
                            <th width="40%">Variant</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($products as $key => $product)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ @$product->title }} <br> Created at : {{ dateFormat(@$product->created_at) }}</td>
                                <td>{{ Str::limit(@$product->description, 50, ' ...') }}</td>
                                <td>
                                    <dl class="row mb-0" style="height: 80px; overflow: hidden"
                                        id="variant_{{ @$product->id }}">
                                        @if (@$product->productVariantPrices)
                                            @foreach ($product->productVariantPrices as $variantPrice)
                                                <dt class="col-sm-3 pb-0">
                                                    {{ @$variantPrice->productVariantOne->variant ?? '' }} /
                                                    {{ @$variantPrice->productVariantTwo->variant ?? '' }} /
                                                    {{ @$variantPrice->productVariantThree->variant ?? '' }}
                                                </dt>
                                                <dd class="col-sm-9">
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-4 pb-0">Price :
                                                            {{ number_format(@$variantPrice->price, 2) }}</dt>
                                                        <dd class="col-sm-8 pb-0">InStock :
                                                            {{ number_format(@$variantPrice->stock, 2) }}</dd>
                                                    </dl>
                                                </dd>
                                            @endforeach
                                        @endif
                                    </dl>
                                    <button onclick="$('#variant_{{ @$product->id }}').toggleClass('h-auto')"
                                        class="btn btn-sm btn-link">Show more</button>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('product.edit', @$product->id) }}"
                                            class="btn btn-success">Edit</a>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} out of
                        {{ $products->total() }}
                        products
                    </p>
                </div>
                <div class="col-md-2">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-between">
                            {{ $products->appends(request()->input())->links('pagination::bootstrap-4') }}
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
