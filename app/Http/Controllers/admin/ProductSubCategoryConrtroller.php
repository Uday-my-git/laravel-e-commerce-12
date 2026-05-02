<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubCategory;


class ProductSubCategoryConrtroller extends Controller
{
    // show sub-category on change event of category
    public function subCategory(Request $request)
    {
        if (!empty($request->category_id)) {
            $subCategory = SubCategory::where('category_id', $request->category_id)->orderBy('name', 'asc')->get();

            return response()->json(['status' => true, 'subCategory' => $subCategory]);
        } else {
            return response()->json(['status' => false, 'subCategory' => [] ]);
        }
    }


}
