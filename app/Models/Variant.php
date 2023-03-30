<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];

    // productvariants
    public function productvariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function productVariantGroupBy()
    {
        return $this->hasMany(ProductVariant::class)->select('variant')->groupBy('variant');
    }

}
