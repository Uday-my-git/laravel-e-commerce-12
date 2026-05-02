<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;


class SubCategoryController extends Controller
{
    public function listing(Request $request)
    {
        $subCategories = SubCategory::latest('id')
            ->select('sub_categories.*', 'categories.name as categoryName')
            ->leftjoin('categories', 'categories.id', '=', 'sub_categories.category_id')
        ;

        /*if (!empty($request->get('search'))) {
            $subCategories = $subCategories->where('sub_categories.name', 'like', '%' .$request->get('search'). '%')
                ->orWhere('sub_categories.slug', 'like', '%' .$request->get('search'). '%')
                ->orWhere('categories.name', 'like', '%' .$request->get('search'). '%');
        }*/

        if (!empty($request->get('search'))) {
            $search = $request->get('search');

            $subCategories = $subCategories->where(function (Builder $query) use ($search) {

                if (is_numeric($search)) {
                    $query->orWhere('sub_categories.id', (int) $search);
                } else {
                    $query->orWhere('sub_categories.name', 'like', '%' .$search. '%')
                        ->orWhere('sub_categories.slug', 'like', '%' .$search. '%')
                        ->orWhere('categories.name', 'like', '%' .$search. '%');
                }
            });
        }

        $subCategories = $subCategories->paginate(10);
        
        return view('admin.sub_category.listing', compact('subCategories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.sub_category.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'name' => 'required|unique:sub_categories',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            $subCategories = new SubCategory();
            $subCategories->category_id = $request->category;
            $subCategories->name        = $request->name;
            $subCategories->slug        = $request->slug;
            $subCategories->status      = $request->status;
            $subCategories->showHome    = $request->showHome;
            $subCategories->save();

            $request->session()->flash('success', 'Sub category inserted successfully !!');
            return response()->json(['status' => true, 'msg' => 'sub category inserted successfully']);
        }
    }

    public function edit(Request $request, $id)
    {
        $data['subCategories'] = SubCategory::findOrFail($id);
        
        $data['categories'] = Category::orderBy('name', 'asc')->get();

        return view('admin.sub_category.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $subCategories = SubCategory::find($id);

        if (empty($subCategories)) {
            $request->session()->flash('error', 'Subcategory not found or invalid user entry!!!');
            return response()->json(['status' => false, 'notFound' => true, 'msg' => 'Sub Categories are already deleted or invalid user entry!!!']);
        }

        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'name' => 'required|unique:sub_categories,slug,'.$subCategories->id.',id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            $subCategories->category_id = $request->category;
            $subCategories->name = $request->name;
            $subCategories->slug = $request->slug;
            $subCategories->status = $request->status;
            $subCategories->showHome = $request->showHome;
            $subCategories->save();

            $request->session()->flash('success', 'Sub category updated successfully !!');
            return response()->json(['status' => true, 'msg' => 'sub category updated successfully']);
        }
    }

    public function delete(Request $request, $id)
    {
        $subCategories = SubCategory::find($id);

        if (empty($subCategories)) {
            $request->session()->flash('error', 'Sub Categories are already deleted or invalid user entry!!!');
            return response()->json(['status' => true, 'msg' => 'Sub Categories are already deleted or invalid user entry!!!']);
        }

        $subCategories->delete();

        $request->session()->flash('success', 'Sub Categories are deleted successfull!!!');
        return response()->json(['status' => true, 'msg' => 'Sub Categories are deleted successfull!!!']);
    }




}
