<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'short_description', 'shipping_returns', 'price', 'compare_price', 'sku', 'barcode', 'track_qty', 'qty', 'status', 'category_id', 'sub_category_id', 'brand_id', 'is_featured', 'related_products',
    ];

    public function product_images_fun() 
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->oldest('id');
    }

    public function sub_category_fun()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function product_ratings_fun()
    {
        return $this->hasMany(ProductRating::class)->where('status', 1);
    }

    


}
