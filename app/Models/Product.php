<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function productVariantPrices()
    {
        return $this->hasMany(ProductVariantPrice::class, 'product_id', 'id');
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    // variant 
    public function variants()
    {
        return $this->belongsToMany(Variant::class, 'product_variants', 'product_id', 'variant_id');
    }

    // productimage 

    public function productImage()
    {
        return $this->belongsTo(ProductImage::class, 'id', 'product_id');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    
}
