<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\Brand;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\ProductRating;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null)
    {
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

        $categorys = Category::orderBy('name', 'asc')->with('sub_category_fun')->where('status', 1)->get();

        $brands = Brand::orderBy('name', 'asc')->where('status', 1)->get();

        // Apply Category filters for product
        $products = Product::where('status', 1);

        if (filled($request->search)) {
            $products = $products->where('title', 'like', '%'. $request->search .'%');
        }

        if (!empty($categorySlug)) {
            $category = Category::firstWhere('slug', $categorySlug);

            if ($category) {
                $products = $products->where('category_id', $category->id);
                $categorySelected = $category->id;      // for subcategory dropdowan remain open when click on instead of close dropdowan
            } else {
                $categorySelected = null;
            }
        }

        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::firstWhere('slug', $subCategorySlug);

            if ($subCategory) {
                $products = $products->where('sub_category_id', $subCategory->id);
                $subCategorySelected = $subCategory->id;
            } else {
                $subCategorySelected = null;
            }
        }

        // Apply Brnads filter of url
        if ($request->filled('brand')) {
            $brandsArray = array_map('intval', explode(',', $request->input('brand')));
            $products = $products->whereIn('brand_id', $brandsArray);
        }

        // Apply Price range filter
        if ($request->input('min_price') != '' && $request->input('max_price') != '') {
            if ($request->input('max_price') == 1000) {
                $products = $products->whereBetween('price', [intval($request->input('min_price')), 1000000]);
            } else {
                $products = $products->whereBetween('price', [intval($request->input('min_price')), intval($request->input('max_price'))]); 
            }  
        }

        // Apply Sorting filters
        if ($request->input('sort') != '') {
            $sort = $request->input('sort', 'latest');
    
            $products = match($sort) {                                          // type 1
                'latest'     => $products->orderBy('id', 'desc'),
                'old'        => $products->orderBy('id', 'asc'),
                'price_asc'  => $products->orderBy('price', 'asc'),
                'price_desc' => $products->orderBy('price', 'desc'),
                default => $products->orderBy('id', 'desc'),
            };

            /*if ($request->input('sort') == 'latest') {          // type 2
                $products = $products->orderBy('id', 'desc');
            } else if ($request->input('sort') == 'price_asc') {
                $products = $products->orderBy('price', 'asc');
            } else {
                $products = $products->orderBy('price', 'desc');
            }*/
        } else {
            $products = $products->orderBy('id', 'desc');
        }

        $products = $products->orderBy('id', 'desc')->paginate(12);       

        $data['categorys']           = $categorys;
        $data['brands']              = $brands;
        $data['products']            = $products;
        $data['categorySelected']    = $categorySelected;
        $data['subCategorySelected'] = $subCategorySelected;
        $data['brandsArray'] = $brandsArray;
        $data['sort'] = $request->input('sort');

        $data['minPrice'] = intval($request->input('min_price'));
        $data['maxPrice'] = (intval($request->input('max_price')) == 0) ? 1000 : intval($request->input('max_price'));

        return view('front-end.shop', $data);
    }

    // get product details of product page
    public function product($slug)
    {
        $products = Product::where('slug', $slug)
            ->withCount('product_ratings_fun')
            ->withSum('product_ratings_fun', 'rating')
            ->with(['product_images_fun', 'product_ratings_fun'])
            ->firstOrFail()
        ;
      
        if (is_null($products)) abort(404);

        $relatedProducts = [];

        if (!empty($products->related_products)) {            // type 1 Use Laravel Collections instead of arrays like type 2
            $relatedIds = collect(explode(',', $products->related_products))
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
            ;

            $relatedProducts = $products->whereIn('id', $relatedIds)->where('status', 1)->get();
        }

        /* if (!empty($products->related_products)) {           // type 2
            $relatedProductArr = array_filter(explode(',', $products->related_products), fn($id) => is_numeric($id));
            $relatedProducts = Product::whereIn('id', $relatedProductArr)->where('status', 1)->get();
        } */

        // Product rating count for show average rating display
        $avgRating = '0.00';  // initializze avgRating or avgRatingPercent
        $avgRatingPercent = 0;

        if ($products->product_ratings_fun_count > 0) {
            $avgRating = ($products->product_ratings_fun_sum_rating / $products->product_ratings_fun_count);
            $avgRatingPercent = ($avgRating * 100) / 5;
        }

        $data['products'] = $products;
        $data['avgRating'] = $avgRating;
        $data['avgRatingPercent'] = $avgRatingPercent;
        $data['relatedProducts'] = $relatedProducts;

        return view('front-end.product', $data);
    }

    public function userRating(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required|unique:product_ratings,email',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->passes()) {
            $productRating = new ProductRating;
            $productRating->product_id = $id;
            $productRating->username   = $request->username;
            $productRating->email      = $request->email;
            $productRating->rating     = $request->rating;
            $productRating->comment    = $request->comment;
            $productRating->save();

            $request->session()->flash('success', 'Ratings form submitted successfull !!');
            return response()->json(['status' => true, 'msg' => 'Ratings form submitted successfull !!']);
        } else {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }
    }

    
}
