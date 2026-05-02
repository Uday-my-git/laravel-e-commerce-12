<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class PagesController extends Controller
{
    public function listing(Request $request)
    {
        $pages = Page::latest();

        if (filled($request->search)) {
            $search = trim($request->search);

            $pages = $pages->where('name', 'like', '%'. $search .'%')
                ->orWhere('slug', 'like', '%'. $search .'%');
        }

        $pages = $pages->paginate(6);

        return view('admin.pages.listing', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:pages,slug',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            $pages = new Page();
            $pages->name = $request->name;
            $pages->slug = $request->slug;
            $pages->content = $request->content;
            $pages->save();

            session()->flash('success', 'User pages create successfully...');
            return response()->json([
                'status' => true, 
                'msg' => 'User pages create successfully...', 
                'redirect' => route('pages.listing')   
            ]);
        }
    }

    public function edit($id)
    {
        $pages = Page::findOrFail($id);
        
        return view('admin.pages.edit', compact('pages'));
    }

    public function update(Request $request, $id)
    {
        $pages = Page::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => [
                'required',
                Rule::unique('pages')->ignore($pages->id),
            ],
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            $pages->name = $request->name;
            $pages->slug = $request->slug;
            $pages->content = $request->content;
            $pages->save();

            // session()->flash('success', 'User pages updated successfully...');
            return response()->json([
                'status' => true, 
                'msg' => 'User pages updated successfully...',
                'redirect' => route('pages.listing')
            ]);
        }
    }

    public function delete(Request $request, $id)
    {
        $pages = Page::find($id);

        if (empty($pages->id)) {
            $request->session()->flash('error', 'User pages not found Or something else error !!');
            return response()->json(['notFound' => false, 'msg' => 'User pages not found Or something else error !!']);
        }
        $pages->delete();

        $request->session()->flash('success', 'User pages deleted successfully...');
        return response()->json(['status' => true, 'msg' => 'User pages deleted successfully...']);
    }

}
