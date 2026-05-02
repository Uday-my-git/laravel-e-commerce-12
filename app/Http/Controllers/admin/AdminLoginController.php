<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AdminLoginController extends Controller
{
    public function index()
    {
        return view('admin.login');
    }

    public function newRegister()
    {
        return view('admin.newRegister');
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:5|max:15'
        ]);

        $key = strtolower($request->email) . '|' . $request->ip();

        // ------ Check login attempts, 3 attempts max
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return redirect()->route('admin.login')->with('error', 'Too many failed login attempts. Your account is temproray blocked for 5 minutes !!');
        }

        if ($validator->passes()) {
            if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
                // Clear attempts on success
                RateLimiter::clear($key);

                $admin = Auth::guard('admin')->user();

                if ($admin->role == 2) {
                    return redirect()->route('admin.dashboard');
                } else {
                    Auth::guard('admin')->logout();
                    return redirect()->route('admin.login')->with('error', 'Your are not authorized to access admin pannel !!');
                }
                
            } else {
                // ❌ Failed login → count attempt
                RateLimiter::hit($key, 300);   // 300 second = 5 min
                $remaining = 3 - RateLimiter::attempts($key); 

                return redirect()->route('admin.login')->with('error', 'Invalid credentials. Attempts left: ' . $remaining);
            }
        } else {
            return redirect()->route('admin.login')->withErrors($validator)->withInput($request->only('email'));
        }
    }

    



}
