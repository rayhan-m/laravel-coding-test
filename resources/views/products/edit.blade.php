@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
    </div>
    <form action="{{ route('product.update',$product) }}" method="post" autocomplete="off" spellcheck="false" enctype="multipart/form-data" >
        @csrf
              @method('PATCH')
        <section>
            <div class="row">
                <div class="col-md-6">
                    <!--                    Product-->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Product</h6>
                        </div>
                        <div class="card-body border">
                            <div class="form-group">
                                <label for="product_name">Product Name</label>
                                <input type="text"
                                       name="product_name"
                                       id="product_name"
                                       required
                                       placeholder="Product Name"
                                       class="form-control"
                                       value="{{@$product->title}}">
                            </div>
                            <div class="form-group">
                                <label for="product_sku">Product SKU</label>
                                <input type="text" name="product_sku"
                                       id="product_sku"
                                       required
                                       placeholder="Product SKU"
                                       class="form-control"
                                       value="{{@$product->sku}}">
                                    </div>
                            <div class="form-group mb-0">
                                <label for="product_description">Description</label>
                                <textarea name="product_description"
                                          id="product_description"
                                          required
                                          rows="4"
                                          class="form-control">{{@$product->description}}</textarea>
                            </div>
                        </div>
                    </div>
                    <!--                    Media-->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"><h6
                                class="m-0 font-weight-bold text-primary">Media</h6></div>
                        <div class="card-body border">
                            <div id="file-upload" class="dropzone dz-clickable">
                                <div class="dz-default dz-message"><span>Drop files here to upload</span></div>
                            </div>
                        </div>
                        <div id="ProductImage">

                        </div>
                        @if ($product->productImage)
                            <div class="mb-4 ml-4">
                                <img src="{{url( $product->productImage->file_path)}}" alt="product image" height="100" width="100">
                            </div>
                        @endif
                    </div>
                </div>
                <!--                Variants-->
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6
                                class="m-0 font-weight-bold text-primary">Variants</h6>
                        </div>
                        @php
                            $key=0;
                        @endphp
                        @forelse ($product_variant_values as $variant_key => $product_variant)

                        @php
                            $key++;
                        @endphp
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Option</label>
                                        <select id="select2-option-{{@$key}}" data-index="{{@$key}}" name="product_variant[{{@$key}}][option]" class="form-control custom-select select2 select2-option">
                                            <option value="1" {{@$variant_key == 1?"selected":""}}>
                                                Color
                                            </option>
                                            <option value="2" {{@$variant_key == 2?"selected":""}}>
                                                Size
                                            </option>
                                            <option value="6" {{@$variant_key == 6?"selected":""}}>
                                                Style
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="d-flex justify-content-between">
                                            <span>Value</span>
                                            <a href="#" class="remove-btn" data-index="{{@$key}}" onclick="removeVariant(event, this);">Remove</a>
                                        </label>
                                        <select id="select2-value-{{@$key}}" data-index="{{@$key}}" value={{@$item->variant}} selected name="product_variant[{{@$key}}][value][]" class="select2 select2-value form-control custom-select" multiple="multiple">
                                            @foreach ($product_variant as $variant_name)
                                                <option value="{{$variant_name}}" selected>{{$variant_name}} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @empty
                        @endforelse
                        {{-- <div class="card-body pb-0" id="variant-sections">
                        </div> --}}
                        <div class="card-footer bg-white border-top-0" id="add-btn">
                            <div class="row d-flex justify-content-center">
                                <button class="btn btn-primary add-btn" onclick="addVariant(event);">
                                    Add another option
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow">
                        <div class="card-header text-uppercase">Preview</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr class="text-center">
                                        <th width="33%">Variant</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                    </tr>
                                    </thead>
                                    <tbody id="variant-previews">
                                        @foreach ($product->productVariantPrices as $key=>$variant_price)
                                        <tr>
                                            <td>
                                                @php
                                                    $vari = $variant_price->productVariantOne->variant .'/'. @$variant_price->productVariantTwo->variant.'/'. @$variant_price->productVariantThree->variant;
                                                @endphp
                                                <input type="hidden" name="product_preview[{{$key}}][variant]" value="{{ $vari }}">
                                                
                                                {{ $variant_price->productVariantOne->variant }} 
                                                {{ $variant_price->productVariantTwo != null ? '/'.$variant_price->productVariantTwo->variant:'' }}
                                                {{ $variant_price->productVariantThree != null ? '/'.$variant_price->productVariantThree->variant:'' }}
                                            </td>
                                            <td>
                                                <input type="text" value="{{$variant_price->price}}" name="product_preview[{{$key}}][price]">
                                            </td>
                                            <td>
                                                <input type="text" value="{{$variant_price->stock}}" name="product_preview[{{$key}}][stock]">
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
            <button type="submit" class="btn btn-lg btn-primary">Save</button>
            <button type="button" class="btn btn-secondary btn-lg">Cancel</button>
        </section>
    </form>
@endsection

@push('page_js')
    <script type="text/javascript" src="{{ asset('js/product.js') }}"></script>
@endpush
