<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AiformController extends Controller
{
    public function listing()
    {
        return view('admin.ai-prompt.listing');
    }

    public function create()
    {
        return view('admin.ai-prompt.create');
    }


}
