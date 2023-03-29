<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariantPrice;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        try {
            $products = Product::latest()->paginate(5);
            return view('products.index', compact('products'));
        } catch (\Throwable $th) {
            Toastr::error('Something went wrong', 'Error');
            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductRequest $request)
    {

        try {
            // Begin a database transaction
            DB::beginTransaction();

            // Create a new product
            $product                = new Product();
            $product->title         = $request->product_name;
            $product->sku           = $request->product_sku;
            $product->description   = $request->product_description;
            $product->save();

            // Create product variants
            if (!empty($request->product_variant)) {

                foreach ($request->product_variant as $productVariant) {
                    if (count($productVariant) < 2) {
                        Toastr::error('Variant option value missing!', 'Error');
                        return redirect()->back()->withInput();
                    }
                    foreach ($productVariant['value'] as $value) {
                        $productVar             = new ProductVariant();
                        $productVar->product_id = $product->id;
                        $productVar->variant    = $value;
                        $productVar->variant_id = $productVariant['option'];
                        $productVar->save();
                    }
                }
            }

            // Create product variant prices
            if (!empty($request->product_preview)) {
                foreach ($request->product_preview as $productPreview) {
                    // Extract the variants from the preview data
                    $variantList    = explode('/', $productPreview['variant']);
                    $variants       = array_filter($variantList);
                    // Create a new product variant price for the product
                    $productVariantPrice                = new ProductVariantPrice();
                    $productVariantPrice->product_id    = $product->id;
                    $productVariantPrice->price         = $productPreview['price'];
                    $productVariantPrice->stock         = $productPreview['stock'];
                    $productVariantPrice->save();
                    // Assign the variants to the product variant price
                    foreach ($variants as $key => $variant) {
                        $productVariant = ProductVariant::where('variant', $variant)->where('product_id', $product->id)->first();

                        if ($key == 0) {
                            $productVariantPrice->product_variant_one   = optional($productVariant)->id;
                        } elseif ($key == 1) {
                            $productVariantPrice->product_variant_two   = optional($productVariant)->id;
                        } elseif ($key == 2) {
                            $productVariantPrice->product_variant_three = optional($productVariant)->id;
                        }
                        $productVariantPrice->save();
                    }
                }
            }

            // Commit the database transaction
            DB::commit();

            // Redirect the user with a success message
            Toastr::success('Product created successfully', 'Success');
            return redirect()->route('product.index');
        } catch (\Throwable $th) {
            // Roll back the database transaction on error
            DB::rollBack();
            // Redirect the user with an error message
            Toastr::error('Something went wrong', 'Error');
            return redirect()->back();
        }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
