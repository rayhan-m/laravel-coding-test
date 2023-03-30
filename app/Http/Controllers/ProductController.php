<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariantPrice;
use App\Http\Traits\FileuploadTrait;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    use FileuploadTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $search     = [];
            $products   = Product::query();
            // title filter
            if ($request->has('title') && $request->title != null) {
                $products        = $products->where('title', 'like', '%' . $request->title . '%');
                $search['title'] = $request->title;
            }
            // price range finter
            if ($request->has('price_from') && $request->has('price_to') && $request->price_from != null && $request->price_to != null) {
                $products = $products->whereHas('productVariantPrices', function ($query) use ($request) {
                    $query->whereBetween('price', [$request->price_from, $request->price_to]);
                });

                $search['price_from']   = $request->price_from;
                $search['price_to']     = $request->price_to;
            }
            // variant filter
            if ($request->has('variant') && $request->variant != null) {
                $products = $products->whereHas('productVariants', function ($query) use ($request) {
                    $query->where('variant', $request->variant);
                });
                $search['variant'] = $request->variant;
            }
            // date filter
            if ($request->has('date') && $request->date != null) {
                $products       = $products->whereDate('created_at', $request->date);
                $search['date'] = $request->date;
            }
            $products = $products->latest()->paginate(5);

            // product variants 
            $variants = ProductVariant::select('variant')->groupBy('variant')->get();

            return view('products.index', compact('products','variants','search'));
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

            // Save the product image

            if ($request->product_image != null) {
                $img = new ProductImage();
                $img->product_id = $product->id;
                $img->file_path = $this->uploadFile($request);
                $img->thumbnail = true;
                $img->save();
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
    public function edit($id)
    {
        try{
            $variants = Variant::all();
            $product = Product::where('id', $id)->with('productVariants','productVariantPrices')->first();
            $product_variant_group=$product->productVariants->pluck('variant_id');
            $product_variant_group=$product_variant_group->unique();
            $product_variant_values=[];
            foreach ($product->productVariants as $key => $single_valiant) {
                $product_variant_values[$single_valiant->variant_id][]=$single_valiant->variant;
            }
            return view('products.edit', compact('variants','product','product_variant_values'));
        } catch (\Throwable $th) {
            Toastr::error('Something went wrong', 'Error');
            return redirect()->back();
        }
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
        
        try {
            // Begin a database transaction
            DB::beginTransaction();

            // Create a new product
            $product->title         = $request->product_name;
            $product->sku           = $request->product_sku;
            $product->description   = $request->product_description;
            $product->save();
            // Create product variants
            if (!empty($request->product_variant)) {
                ProductVariant::where('product_id',$product->id)->delete();
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
                ProductVariantPrice::where('product_id',$product->id)->delete();
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

            // Save the product image

            if ($request->product_image != null) {
                ProductImage::where('product_id',$product->id)->delete();
                $img = new ProductImage();
                $img->product_id = $product->id;
                $img->file_path = $this->uploadFile($request);
                $img->thumbnail = true;
                $img->save();
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
