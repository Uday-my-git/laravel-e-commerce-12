<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class SettingController extends Controller
{
    public function chagePasswordForm()
    {
        return view('admin.change_password');
    }

    public function chagePasswordFormProcess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:4|max:12',
            'new_password' => 'required|min:4|max:12',
            'confirm_password' => 'required|same:new_password',
        ]);
 
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            $adminId = Auth::guard('admin')->user()->id;
            
            $user  = User::firstWhere('id', $adminId);
            $currentPassword = Hash::check($request->old_password, $user->password);

            if (!$currentPassword) {
                $request->session()->flash('error', 'Old Password is Incorrect Please Check Password First!');
                return response()->json(['pwdErrs' => false, 'msg' => 'Old Password is Incorrect Please Check Password First!']);
            } else {
                User::findOrFail($adminId)->update([
                    'password' =>  Hash::make($request->new_password)
                ]);

                $request->session()->flash('success', 'Password Update Successfully!!');
                return response()->json(['status' => true, 'msg' => 'Password Update Successfully!!']);
            }
        }
    }



}
