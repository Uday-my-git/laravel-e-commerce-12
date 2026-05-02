<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\Wishlist;
use App\Models\CustomerAddress;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordEmail;
use App\Mail\NewUserRegister;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function userRegister()
    {
        return view('front-end.account.registration');
    }

    public function login()
    {
        return view('front-end.account.login');
    }

    public function profile(Request $request)
    {
        // Retrieve the currently authenticated user's ID...
        $userId = Auth::id();

        $profile = User::firstWhere('id', $userId);        

        $customerAddress = CustomerAddress::firstWhere('user_id', $userId);
        $countries = Country::orderBy('name', 'asc')->get();

        return view('front-end.account.profile', compact('profile', 'customerAddress', 'countries'));
    }

    public function updateProfile(Request $request)
    {
        $userId = Auth::id();  
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$userId.',id',
            'phone' => 'required|min:10|max:12',
        ], [], [], 'email1');

        // Retrieve the currently authenticated user's ID...
        $userId = Auth::id();  
        $profile = User::find($userId);

        $profile->name = $request->input('name');
        $profile->email = $request->input('email');
        $profile->phone = $request->input('phone');
        $profile->save();

        return redirect()->back()->with('success', 'User Personal Information Updated Successfully');
    }

    public function updateAddress(Request $request)
    {
        $userId = Auth::id();  

        $request->validate([
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email',
            'country_id' => 'required',
            'mobile'     => 'required',
            'apartment'  => 'required',
            'address'    => 'required',
            'city'       => 'required',
            'state'      => 'required',
            'zip'        => 'required|integer',
        ], [],[], 'email2');

        CustomerAddress::updateOrCreate(
            ['user_id' => $userId],
            [
                'user_id'    => $userId,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'mobile'     => $request->mobile,
                'country_id' => $request->country_id,
                'address'    => $request->address,
                'apartment'  => $request->apartment,
                'city'       => $request->city,
                'state'      => $request->state,
                'zip'        => $request->zip
            ]
        );

        return redirect()->back()->with('success', 'User Customer Information Updated Successfully!!');
    }

    public function registerProcess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required',
            'phone'    => 'required',
            'password' => 'required|min:5|max:10|confirmed',
        ]);

        if ($validator->passes()) {
            $newUser = DB::table('users')->insert([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'status'   => 0,
                'password' => Hash::make($request->password),
            ]);

            try {
                $newUser = User::firstWhere('email', $request->email);
                
                $mailData = [
                    'user'    => $newUser,
                    'subject' => 'New User Register',   
                ];

                $ccRecipients  = 'uday.thakur626@gmail.com';
                $bccRecipients = 'ts.udaythakur@gmail.com';

                $filePath = public_path('front-end-assets/order-id#540.pdf');
                
                Mail::to($newUser->email)
                    ->cc($ccRecipients)
                    ->bcc($bccRecipients)
                    ->send(new NewUserRegister($mailData, $filePath))
                ;

                Log::info('New User Registration email sent successfully to ' . $request->email);

            } catch (\Throwable $th) {
                Log::error('Failed to send email: ' . $th->getMessage());
            }

            session()->flash('success', 'User Register Successfully, Now Login Your Account');
            return response()->json(['status' => true, 'msg' => 'User Register Successfully']);

        } else {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }
    
    }

    // public function authenticate(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);
        
    //     if ($validator->fails()) {
    //         return redirect()->route('account.login')->withErrors($validator)->withInput($request->only('email'));
    //     } else {
    //         if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {

    //             // capture current URL if user login and redirect to checkout page
    //             if (session()->has('url.intended')) {                         
    //                 return redirect(session()->get('url.intended'));
    //                 // dd(session('url.intended'), url()->previous(), url()->current());
    //             }
    //             return redirect()->route('account.profile');

    //         } else {
    //             // session()->flash('error', 'User either email/password is incorrect!!');
    //             return redirect()->route('account.login')->withInput($request->only('email'))->with('error', 'User either email/password is incorrect!!');
    //         }
    //     }
    // }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:5|max:12',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('account.login')->withErrors($validator)->withInput($request->only('email'));
            
        } else {
            $users = User::firstWhere('email', $request->email);

            if (!$users) {
                return redirect()->route('account.login')->withInput($request->only('email'))->with('error', 'User either email/password is incorrect!!');
            }

            if ($users->status != 1) {
                return redirect()->route('account.login')->with('error', 'Email ID is not active. Please wait for admin approval side.');
            } 

            $credentials = ['email' => $request->email, 'password' => $request->password, 'role' => 1];

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                return redirect()->intended(route('account.profile'));
            } else {
                return redirect()->route('account.login')->withInput($request->only('email'))->with('error', 'User either email/password is incorrect !!');
            }
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate the session to clear all session data, including the CSRF token.
        $request->session()->invalidate();
    
        // Regenerate the CSRF token to prevent token reuse attacks.
        $request->session()->regenerateToken();
        return redirect()->route('account.login')->with('success', 'You logout successfully');
    }


    /************************************** 
        Get Order details of product order of front-end side
    ***************************************/
    public function orderGet(Request $request)
    {
        $data = [];
        $userId = Auth::id();

        if ($userId) $data['order'] = Order::latest()->where('user_id', $userId)->paginate(10);

        return view('front-end.account.order', $data);
    }

    public function get_orderDetail($orderId)
    {
        // Retrieve the currently authenticated user's ID...
        $data = [];
        $userId = Auth::id();

        if (!$orderId) return redirect()->route('front-end.get_orderDetail')->with('error', 'Order Id not found.');

        if ($userId) {
            $order = Order::firstWhere(['user_id' => $userId, 'id' => $orderId]);
            $data['payment'] = Payment::firstWhere('order_id', $orderId);

            if (!$order) {
                return redirect()->back()->with('error', 'Order not found or unauthorized access.');
            } else {
                $data['orderDetails'] = OrderItem::where('order_id', $orderId)->get();
                
                $data['orderDetailsCount'] = OrderItem::where('order_id', $orderId)->count();
            }
            $data['order'] = $order;
           
        } else {
            return redirect()->route('front-end.get_orderDetail')->with('error', 'Please log in to view your orders.');
        }
     
        return view('front-end.account.order-details', $data);
    }

    /************************************** 
        Product add in Wishlist
    ***************************************/

    public function wishlist(Request $request)
    {
        $data = [];
        $userId = Auth::id();

        $wishlists = Wishlist::where('user_id', $userId)->with('product')->get();
        $data['wishlists'] = $wishlists;

        return view('front-end.account.wishlist', $data);
    }

    public function deleteWishlistProduct(Request $request)
    {
        $userId = Auth::id();

        // The filled function determines whether the given value is not "blank"
        if (filled($userId) && filled($request->product_id)) {
            $wishlistItem = Wishlist::firstWhere(['user_id' => $userId, 'product_id' => $request->product_id]);
            
            if ($wishlistItem) {
                $wishlistItem->delete();

                $status = true;
                $msg = 'Remove product from Wishlist successfully';
                
            } else {
                $status = false;
                $msg = 'Not found remove product from Wishlist !!!';
            }

            session()->flash($status ? 'success' : 'error', $msg);
        }

        return response()->json(['status' => $status, 'msg' => $msg]);
    }

    public function changePassword(Request $request)
    {
        return view('front-end.account.changePassword');
    }

    public function processChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:4|max:12',
            'new_password' => 'required|min:4|max:12',
            'confirm_password' => 'required|same:new_password',
        ]);
 
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            $userId = Auth::id();

            if ($userId) {
                $user = User::select('id', 'pasResetPasswordEmailsword')->firstWhere('id', $userId);
                $currentPassword = Hash::check($request->old_password, $user->password);
                
                if (filled($currentPassword) && !empty($currentPassword)) {
                    User::findOrFail($userId)->update([
                        'password' => Hash::make($request->new_password)
                    ]);

                    $request->session()->flash('success', 'Password Update Successfully!!');
                    return response()->json(['status' => true, 'msg' => 'Password Update Successfully!!']);
                    
                } else {
                    $request->session()->flash('error', 'Old Password is Incorrect Please Check Password First!1');
                    return response()->json(['pwdErrs' => false, 'msg' => 'Old Password is Incorrect Please Check Password First!']);
                }
            } else {
                return response()->json(['status' => false, 'msg' => 'Invalid user if found or inligeal authentication!!']);
            }
        }
    }

    // Send email link for change forgot password for user, & 
    // click on the link after then redirect to change passowrd page for reset password
    public function forgotPassword(Request $request)
    {
        return view('front-end.account.forgot-password');
    }

    // send the mail with token of forgot  password on user email which exist in d.b
    public function forgotPasswordProcess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
 
        if ($validator->fails()) {
            return redirect()->route('front-end.forgotPassword')->withInput()->withErrors($validator);
        } else {
            $tokenExist = \DB::table('password_reset_tokens')->where('email', $request->email)->exists();

            if ($tokenExist) {
                \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            } 

            $token = Str::random(60);

            \DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            $user = User::where('email', $request->email)->first();

            $mailData = [
                'token'   => $token,
                'user'    => $user,
                'subject' => 'You have requested to reset yours password',
            ];

            try {
                Mail::to($request->email)->send(new ResetPasswordEmail($mailData));

                $status = 'success';
                $msg    = 'Please check your inbox to reset your password.';
            } catch (\Throwable $e) {
                $status = 'error';
                $msg    = 'Failed to send email: ' . $e->getMessage();
            }
            return redirect()->route('front-end.forgotPassword')->with($status, $msg);
        }
    }

    // this check the token behalf on save the token in d.b & then process to change new password of reset-password page
    public function resetPassword($token)
    {
        $token = \DB::table('password_reset_tokens')->where('token', $token)->first();

        if (is_null($token)) return redirect()->route('front-end.forgotPassword')->with('error','Invalid Request Response !!!');

        return view('front-end.account.reset-password', ['token' => $token]);
    }

    public function processResetPassword(Request $request)
    {
        if ($request->filled('token')) {
            $token = \DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->where('created_at', '>=', now()->subMinute())     // Expires token forgot password after 60 sec.
                ->first();

            if (is_null($token)) {
                Log::error('Token expires after 1 minutes');
                return redirect()->route('front-end.forgotPassword')->with('error','Invalid Request Response or token are expires!!!');
            } else {
                $validator = Validator::make($request->all(), [
                    'new_password' => 'required|min:5|max:12',
                    'confirm_password' => 'required|same:new_password',
                ]);

                if ($validator->fails()) {
                    return redirect()->route('front-end.resetPassword', $request->token)->withErrors($validator);
                } else {
                    $userData = \DB::table('users')->where('email', $token->email)->first();

                    if (!$userData) {
                        return redirect()->route('front-end.forgotPassword')->with('error', 'User not found!');
                    }

                    \DB::table('users')->where('id', $userData->id)->update([
                        'password' => Hash::make($request->new_password),
                        'updated_at' => now(),
                    ]);

                    \DB::table('password_reset_tokens')->where('email', $userData->email)->delete();
                    return redirect()->route('account.login')->with('success','Password change succesfull, Now login your account');
                }
            }
        } else {
            return redirect()->route('front-end.forgotPassword')->with('error', 'Invalid request !!');
        }
    }


}
