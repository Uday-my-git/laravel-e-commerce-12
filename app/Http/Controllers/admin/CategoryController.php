<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\TempImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function listing(Request $request)
    {
        $categories = Category::latest();

        if (filled($request->search)) {
            $search = trim($request->search);

            if (is_numeric($search)) {
                $categories->where('id', (int)$search)->limit(1);
                $categories->first();
            } else {
                $categories = $categories->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            }
        }
        $categories = $categories->paginate(10);

        return view('admin.category.listing', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);

        if ($validator->passes()) {
            $categories = new Category();
            $categories->name     = $request->name;
            $categories->slug     = $request->slug;
            $categories->showHome = $request->showHome;
            $categories->status   = $request->status;
            $categories->save();

            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray  = explode('.', $tempImage->name);
                $ext       = last($extArray);
                $newImageName = $categories->id . '-' . time() . '.' . $ext;

                $destiNationPath = public_path() . '/uploads/category/thumb/' . $newImageName;

                $srcPath = public_path() . '/temp/' . $tempImage->name;
                $destPath = public_path() . '/uploads/category/' . $newImageName;

                File::copy($srcPath, $destPath);

                // Generate Image Thumbnail with Intervention image librarey
                $manager = new ImageManager(new Driver());
                $image = $manager->read($srcPath);
                $image->cover(450, 600);
                $image->save($destiNationPath);

                $categories->image = $newImageName;
                $categories->save();
            }

            $request->session()->flash('success', 'Categories inserted success');
            return response()->json(['status' => true, 'msg' => 'Categories inserted successfully ...']);
        } else {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }
    }

    public function edit(Request $request, $id)
    {
        $categories = Category::findOrFail($id);

        if (!$categories) {
            $request->session()->flash('error', 'This user are not found in database !!');
            return redirect()->route('categories.listing');
        }

        return view('admin.category.edit', compact('categories'));
    }

    public function update(Request $request, $id)
    {
        $categories = Category::find($id);

        if (empty($categories->id)) {
            $request->session()->flash('error', 'Sorry!!!, This user not found in database Or invalid user entrey'); 
            return response()->json(['status' => false, 'notFound' => true, 'msg' => 'This category not availabel for update user data!!!']);
        }

        $validator = Validator::make($request->all(), [ 
            'name' => 'required',
            // 'slug' => 'required|unique:categories,slug,'.$categories->id.',id',
            'slug' => [
                'required',
                Rule::unique('categories')->ignore($categories->id),
            ],
            // 'slug' => Rule::unique('categories')->ignore($categories),
        ]);

        if ($validator->passes()) {
            $categories->name     = $request->name;
            $categories->slug     = $request->slug;
            $categories->showHome = $request->showHome;
            $categories->status   = $request->status;

            $oldImage = $categories->image;
            $categories->save();

            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);
                
                $newImageName = $categories->id . '-' . time() . '.' . $ext;
                $destinationPath = public_path('/uploads/category/thumb/') . $newImageName;

                $srcPath = public_path('/temp/') . $tempImage->name;
                $destPath = public_path('/uploads/category/') . $newImageName;

                File::copy($srcPath, $destPath);

                // Generate Image Thumbnail with Intervention image librarey
                $manager = new ImageManager(new Driver());
                $image = $manager->read($srcPath);
                $image->cover(450, 600);
                $image->save($destinationPath);

                $categories->image = $newImageName;
                $categories->save();

                if (!empty($oldImage)) {
                    $catImage = public_path('uploads/category/') . $oldImage;
                    $thumbImage = public_path('uploads/category/thumb/') . $oldImage;

                    foreach ([$catImage, $thumbImage] as $filepath) {       // method 1
                        if (file_exists($filepath) && is_file($filepath)) unlink($filepath);
                    }
                }
            }

            $request->session()->flash('success', 'Categories updated success');
            return response()->json(['status' => true, 'msg' => 'Categories updated successfully ...']);
        } else {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }
        
    }

    public function delete(Request $request, $id)
    {
        $categories = Category::find($id);

        // if (!empty($categories->id)) {
        //     $subCat = SubCategory::where('category_id', $id)->first();
            
        //     if (!empty($subCat->category_id)) {
        //         $products = Product::where('sub_category_id', $subCat->id)->where('category_id', $categories->id)->get();
        //         dd($products);
        //     }
        // }

        // $products = Product::get();

        if (empty($categories->id)) {
            $request->session()->flash('error', 'Category not found !!!');
            return response()->json(['status' => true, 'msg' => 'Category not found !!!']);
        }

        $catImage = public_path('uploads/category/') . $categories->image;
        $thumbImage = public_path('uploads/category/thumb/') . $categories->image;

        if (!empty($categories->image)) {
            if (file_exists($catImage)) unlink($catImage);
            if (file_exists($thumbImage)) unlink($thumbImage);
        }

        if ($categories) {
            $products = Product::where('category_id', $categories->id)
                ->orWhereIn('sub_category_id', function ($q) use ($id) {
                    $q->select('id')->from('sub_categories')->where('category_id', $id);
                })
                ->delete()
            ;
            
            SubCategory::where('category_id', $id)->delete();

            $categories->delete();
        }
        $request->session()->flash('success', 'Category deleted successfully....');
        return response()->json(['status' => true, 'msg' => 'Category deleted successfully']);
    }



}
