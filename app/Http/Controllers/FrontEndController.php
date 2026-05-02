<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;   
use App\Models\Category;
use App\Models\Product;
use App\Models\Page;
use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\ContactUsEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class FrontEndController extends Controller
{
    public function index() 
    {
        $featuredProducts = Product::orderBy('id', 'desc')->with('sub_category_fun')->where('is_featured', 'Yes')->where('status', 1)->limit(8)->get();
        $data['featuredProducts'] = $featuredProducts;
        
        $latestProducts = Product::orderBy('id', 'desc')->where('status', 1)->limit(8)->get();
        $data['latestProducts'] = $latestProducts;

        return view('front-end.home', $data);
    }

    public function addToWishlist(Request $request) 
    {        
        if (!Auth::check()) { 
            session(['url.intended' => url()->previous()]);

            return response()->json([
                'status' =>false, 
                'msg' => '<div class="alert alert-danger"><Product Not Found !!!/div>'
            ]);
        }

        $product = Product::firstWhere('id', $request->productId);
        $productTitle = $product->title;

        if (is_null($product)) return response()->json(['status' => true, 'msg' => 'Product Not Found In Wishlist']);
        
        Wishlist::updateOrCreate(
            [
                'user_id' => Auth::user()->id,
                'product_id' => $request->productId
            ],
            [
                'user_id' => Auth::user()->id,
                'product_id' => $request->productId
            ]
        );

        return response()->json(['status' => true, 'msg' => 'Product <strong>'.$product->title.'</strong> added in Wishlist', 'productTitle' => $productTitle]);
    }

    
    // Show dnamic pages of related about us or other pages in footer section
    public function pages($slug)
    {
        $pages = Page::firstWhere('slug', $slug);

        if (!$pages || is_null($pages)) abort(404); 

        return view('front-end.pages', ['pages' => $pages]);
    }

    public function sendContactForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|min:5|max:1000',
        ]);
 
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            /*
                $mailData = [                                               // Type 1
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'subject' => $request->input('subject'),
                    'mail_subject' => 'You have received a contact form',
                ];

                $admin = User::find(1);

                if (!$admin || !$admin->email) {
                    $status = false;
                    $msg = 'Admin email not configured Or Failed to send email!!';
                } else {
                    try {
                        Mail::to($admin->email)->send(new ContactUsEmail($mailData));

                        $status = true;
                        $msg = 'Thanks for contacting us, we will get back to you soon !!';
                        
                    } catch (\Exception $e) {
                        $status = false;
                        $msg = 'Failed to send email: ' . $e->getMessage();
                    }
                } 
            */
 
            $mailData = $validator->validated();                          // Type 2 Optimze version
            $mailData['mail_subject'] = 'You have received a contact form';

            $adminEmail = config('mail.admin_email');

            if (!$adminEmail) {
                Log::error('Admin email is not configured', [
                    $status = false,
                    $msg = 'Service temporarily unavailable.'
                ]);
            }

            try {
                Mail::to($adminEmail)->send(new ContactUsEmail($mailData));

                $status = true;
                $msg = 'Thanks for contacting us, we will get back to you soon !!';

            } catch (\Throwable $e) {
                Log::error('Mail send failed', [
                    'error' => $e->getMessage(),
                    'data'  => $mailData
                ]);

                $status = false;
                $msg = 'Unable to send your message at the moment. Please try again later.' . $e->getMessage();
            }
           
            return response()->json(['status' => $status, 'msg' => $msg]);
        }
    }





}
