<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRating;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use File;


class ProductController extends Controller
{   
    public function listing(Request $request)
    {
        $perPage = $this->determinePerPage($request);

        $products = Product::latest()->with('product_images_fun')
            ->select('products.*', 'categories.name as categoryName')
            ->leftjoin('categories', 'products.category_id', '=', 'categories.id');
        
        if (!empty($request->get('search'))) {
            $search = $request->search;

            if (is_numeric($search)) {
                $products->where('products.id', $search);
            } else {
                $products->where(function (Builder $query) use ($search) {
                    $query->orWhere('products.title', 'like', '%' .$search. '%')
                        ->orWhere('products.slug', 'like', '%' .$search. '%')
                        ->orWhere('categories.name', 'like', '%' .$search. '%');
                });
            }
        }
        
        $products = $products->paginate($perPage)->withQueryString();
        $linksToShow = $this->calculateVisibleLinks($products);
        
        $products->onEachSide($linksToShow);

        return view('admin.products.listing', compact('products'));
    }

    private function determinePerPage(Request $request): int
    {
        return $request->query('show', 10);
    }

    private function calculateVisibleLinks($paginator): int
    {
        $totalPages = $paginator->lastPage();
 
        // Show more links for larger datasets
        return match(true) {
            $totalPages > 10 => 3,  // « 1 ... 4 5 6 7 8 9 10 ... 25 »
            $totalPages > 5 => 2,  // « 1 ... 4 5 6 7 8 ... 15 »
            default => 1           // « 1 2 3 4 5 »
        };
    }

    public function create(Request $request)
    {
        $categories = Category::orderBy('name', 'asc')->get();
        $brands = Brand::orderBy('name', 'asc')->get();

        $data['categories'] = $categories;
        $data['brands'] = $brands;

        return view('admin.products.create', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products',
            'description' => 'required',
            'price' => 'required',
            'sku' => 'required|unique:products',
            'barcode' => 'required',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required',
            'brand' => 'required',
            // 'is_featured' => 'required|in:Yes,No',
        ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes') $rules['qty'] = 'required|numeric';
           
        $validator = Validator::make($request->all(), $rules);
       
        if ($validator->passes()) {
            $product = Product::create([
                'title'             => trim($request->title),
                'slug'              => $request->slug,
                'description'       => $request->description,
                'short_description' => $request->short_description,
                'shipping_returns'  => $request->shipping_returns,

                'related_products'  => (!empty($request->related_products)) ? implode(',', $request->related_products) : '',
                'price'             => $request->price,
                'compare_price'     => $request->compare_price,
                'sku'               => $request->sku,
                'barcode'           => $request->barcode,
                'track_qty'         => $request->track_qty,
                'qty'               => $request->qty,
                'status'            => $request->status,

                'category_id'     => $request->category,
                'sub_category_id' => $request->sub_category,
                'brand_id'        => $request->brand,
                'is_featured'     => $request->is_featured,
            ]);

            // Save gallery image 
            if (!empty($request->image_array)) {
                foreach ($request->image_array as $temp_img_id) {
                    $tempImgInfo = TempImage::find($temp_img_id);
                    
                    $extArray = explode('.', $tempImgInfo->name);
                    $ext = last($extArray);

                    $productImg = new ProductImage();
                    $productImg->product_id = $product->id;
                    $productImg->image = 'NULL';
                    $productImg->save();

                    $imageName = $product->id . '-' . $productImg->id . '-' . time() . '.' . $ext;
                    $productImg->image = $imageName;
                    $productImg->save();

                    // Genetrate large image thumbnail with Intervention image librarey
                    $srcPath  = public_path() . '/temp/' . $tempImgInfo->name;
                    $destPath = public_path() . '/uploads/product/large/' . $imageName;

                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($srcPath);
                    $image->scaleDown(1400)->save($destPath);

                    // Genetrate small image thumbnail with Intervention image librarey
                    $destPath = public_path() . '/uploads/product/small/' . $imageName;
                    
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($srcPath);
                    $image->cover(300, 300)->save($destPath);
                }
            }
           
            $request->session()->flash('success', 'Product added successfully !!!');
            return response()->json(['status' => true, 'msg' => 'Product added succesfully !!!']);
        } else {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }

    }

    public function edit(Request $request, $id)
    {
        $products = Product::find($id);
        $data['products'] = $products;

        if (empty($data['products'])) {
            // $request->session()->flash('error', 'Product Not Found Here!!!');                       // Type 1  
            return redirect()->route('product.listing')->with('error', 'Product Not Found Here!!!');   // Type 2
        }

        // fetch product images 
        $productImages = ProductImage::where('product_id', $products->id)->get();

        $categories    = Category::orderBy('name', 'asc')->get();
        $subCategories = SubCategory::where('category_id', $products->category_id)->get();
        $brands        = Brand::orderBy('name', 'asc')->where('status', 1)->get();

        // Related products fetch
        $relatedProducts = [];

        if ($products->related_products != '') {
            $relatedProductArr = explode(',', $products->related_products);
            $relatedProducts = Product::whereIn('id', $relatedProductArr)->get();
        }
        
        $data['productImages'] = $productImages;
        $data['categories']    = $categories;
        $data['subCategories'] = $subCategories;
        $data['brands'] = $brands;
        $data['relatedProducts'] = $relatedProducts;

        return view('admin.products.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $products = Product::find($id);

        if (empty($products)) {
            $request->session()->flash('error', 'Product Not Found Here!!!');
            return response()->json(['status' => false, 'notFound' => true, 'msg' => 'prodcut update successfully']);
        }

        $rules = [
            'title'             => 'required',
            'slug'              => 'required|unique:products,slug,'.$products->id.',id',
            'description'       => 'required',
            'short_description' => 'required',
            'price'             => 'required|numeric',
            'sku'               => 'required|unique:products,sku,'.$products->id.',id',
            'track_qty'         => 'required|in:Yes,No',
            'category'          => 'required',
            'brand'             => 'required',
            // 'is_featured' => 'required|in:Yes,No',
        ];

        if (!blank($request->track_qty) && $request->track_qty == 'Yes') {
            $rules['qty'] = 'required|numeric';
        }   

        $validaor = Validator::make($request->all(), $rules);

        if ($validaor->fails()) {
            return response()->json(['status' => false, 'errors' => $validaor->errors()]);
        } else {
            $products->update([
                'title'             => $request->title,
                'slug'              => $request->slug,
                'description'       => $request->description,
                'short_description' => $request->short_description,
                'shipping_returns'  => $request->shipping_returns,
                'related_products'  => (!empty($request->related_products)) ? implode(',', $request->related_products) : '',
                'price'             => $request->price,
                'compare_price'     => $request->compare_price,
                'sku'               => $request->sku,
                'barcode'           => $request->barcode,
                'track_qty'         => $request->track_qty,
                'qty'    => $request->qty,
                'status' => $request->status,

                'category_id'     => $request->category,
                'sub_category_id' => $request->sub_category,
                'brand_id'        => $request->brand,
                'is_featured'     => $request->is_featured,
            ]);

            $request->session()->flash('success', 'Prodcut Update Successfully !!!');
            return response()->json(['status' => true, 'msg' => 'prodcut update successfully']);
        }
    }
    
    public function deleteProducts(Request $request, $id)
    {
        $product = Product::find($id);

        if (empty($product)) {
            $request->session()->flash('errors', 'Prodcut Not Founf!!!');
            return response()->json(['status' => false, 'msg' => 'prodcut deleted successfully']);
        }

        $productImage = ProductImage::where('product_id', $id)->get();

        if ($productImage != '') {
            foreach ($productImage as $productImages) {
                File::delete('uploads/product/large/' . $productImages->image);
                File::delete('uploads/product/small/' . $productImages->image);
            }
            ProductImage::where('product_id', $id)->delete();
        }

        $product->delete();

        $request->session()->flash('success', 'prodcut deleted successfully !!!');
        return response()->json(['status' => true, 'msg' => 'prodcut deleted successfully']);
    }

    public function getRelatedProduct(Request $request)
    {
        $tempProduct = [];

        if ($request->term != '') {
            $products = Product::where('title', 'like', '%' . $request->term . '%')->get();
            
            if (!is_null($products)) {
                foreach ($products as $product) {
                    $tempProduct[] = array('id' => $product->id, 'text' => $product->title);
                } 
            }
        }
        return response()->json(['status' => true, 'tags' => $tempProduct]);
    }

    public function productRatings(Request $request)
    {
        $ratings = ProductRating::orderBy('product_ratings.created_at', 'desc')    // Type 1
            ->with(['product.mainImage'])
            ->select('product_ratings.*', 'products.title as productTitle')
            ->leftJoin('products', 'products.id', '=', 'product_ratings.product_id')
        ;

        /*
        $ratings = ProductRating::orderBy('product_ratings.created_at', 'desc')  // Type 2 GROUP BY
            ->selectRaw('
                product_ratings.id,
                product_ratings.product_id,
                product_ratings.username,
                product_ratings.email,
                product_ratings.rating,
                product_ratings.comment,
                product_ratings.status,
                product_ratings.created_at,
                products.title as productTitle,
                MIN(product_images.image) as productImage
            ')
            ->leftJoin('products', 'products.id', '=', 'product_ratings.product_id')
            ->leftJoin('product_images', 'product_images.product_id', '=', 'product_ratings.product_id')
            ->groupBy(
                'product_ratings.id',
                'product_ratings.product_id',
                'product_ratings.username',
                'product_ratings.email',
                'product_ratings.rating',
                'product_ratings.comment',
                'product_ratings.status',
                'product_ratings.created_at',
                'products.title'
            ); 
        */

        /*
        $ratings = ProductRating::orderBy('product_ratings.created_at', 'desc')   // Type 3 Subquery
            ->select(
                'product_ratings.*',
                'products.title as productTitle',
                'pi.image as productImage'
            )
            ->leftJoin('products', 'products.id', '=', 'product_ratings.product_id')
            ->leftJoin(DB::raw('
                (
                    SELECT product_id, MIN(id) as image_id
                    FROM product_images
                    GROUP BY product_id
                ) pim
            '), 'pim.product_id', '=', 'product_ratings.product_id')
            ->leftJoin('product_images as pi', 'pi.id', '=', 'pim.image_id')
        ; */

        if (!empty($request->get('search'))) {
            $search = $request->get('search');
            
            $ratings = $ratings->where(function (Builder $query) use ($search) {

                if (is_numeric($search)) {
                    $query->orWhere('product_ratings.id', (int) $search);
                } else {
                    $query->orWhere('product_ratings.username', 'like', '%' . $search . '%')
                        ->orWhere('product_ratings.email', 'like', '%' . $search . '%')
                        ->orWhere('products.title', 'like', '%' . $search . '%');
                }
            });
        }

        $ratings = $ratings->paginate(10);
        // dd($ratings);

        return view('admin.products.ratings', ['ratings' => $ratings]);
    }

    public function changeStatustRating(Request $request)
    {
        $user = ProductRating::find($request->userId);

        if (!$user) {
            session()->flash('error', 'User not found or invalid input occured ??');
            return response()->json(['status' => false, 'msg' => 'user status not found or invalid input occured ??']);
        }

        $user->status = $request->status;
        $user->save();

       return response()->json(['status' => true, 'msg' => 'User status updated successfull !!']);
    }

    public function deleteUserRating($id)
    {
        $user = ProductRating::find($id);
      
        if (!$user) {
            session()->flash('error', 'User not found or invalid input occured ??');
            return response()->json(['status' => false, 'msg' => 'user not found or invalid input occured ??']);
        }

        $user->delete();
        return response()->json(['status' => true, 'msg' => 'User deleted successfull !!']);
    }


}
