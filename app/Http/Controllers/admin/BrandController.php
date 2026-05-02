<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function listing(Request $request)
    {
        $brands = Brand::latest('id');

        if ($request->get('search')) {
            $brands = $brands->where('name', 'like', '%'.$request->get('search').'%')
                ->orWhere('slug', 'like', '%'.$request->get('search').'%')
                ->orWhere('id', 'like', '%'.$request->get('search').'%');
        }

        $brands = $brands->paginate(10);
        $data['brands'] = $brands;

        return view('admin.brand.listing', $data);
    }

    public function create()
    {
        return view('admin.brand.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            $brands = new Brand();
            $brands->name = $request->name;
            $brands->slug = $request->slug;
            $brands->status = $request->status;
            $brands->save();

            $request->session()->flash('success', 'Brands inserted success');
            return response()->json(['status' => true, 'msg' => 'Brands inserted successfully ...']);
        }
    }


    public function edit(Request $request,$id)
    {
        $brands = Brand::find($id);

        if (!$brands) {
            $request->session()->flash('error', 'This user are not found in database !!');
            return redirect()->route('brand.listing');
        }

        $data['brands'] = $brands;

        return view('admin.brand.edit', $data);
    }

    public function update(Request $request, $id)
    {        
        $brands = Brand::find($id);

        if (empty($brands)) {
            $request->session()->flash('error', 'This user are not found in database !!');
            return response()->json(['status' => false, 'notFound' => true, 'msg' => 'This user are not found in database !!']);        
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:brands,name,' . $brands->id,
            'slug' => 'required|unique:brands,slug,' . $brands->id . ',id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            $brands->update([
                'name'   => $request->name,
                'slug'   => $request->slug,
                'status' => $request->status,
            ]);
        
            $request->session()->flash('success', 'Brands inserted success');
            return response()->json(['status' => true, 'msg' => 'Brands inserted successfully ...']);
        }
    }

    public function delete(Request $request, $id)
    {
        $brands = Brand::find($id);

        if (!$brands) {
            $request->session()->flash('error', 'This user are not found in database !!');
            return response()->json(['status' => true, 'msg' => 'This user are not found in database !!']);     
        }

        $brands->delete();

        $request->session()->flash('success', 'Brands Deleted Success');
        return response()->json(['status' => true, 'msg' => 'Brands deleted successfully ...']);
    }

    public function deleteAllCheckbox(Request $request)
    {
        $brands = Brand::whereIn('id', explode(',', $request->ids))->delete();

        if (!$brands) {
            $request->session()->flash('error', 'This user are not found in database !!');
            return response()->json(['status' => true, 'msg' => 'This user are not found in database !!']);     
        }

        $request->session()->flash('success', 'All Brands Deleted Successfull');
        return response()->json(['status' => true, 'msg' => 'All Brands deleted successfull ...']);
    }


}
