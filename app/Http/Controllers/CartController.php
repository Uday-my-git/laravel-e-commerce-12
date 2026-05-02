<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Country;
use App\Models\User;
use App\Models\apartment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CustomerAddress;
use App\Models\ShippingCharge;
use App\Models\DiscountCoupon;
use App\Models\WebhookEvent;
use App\Models\Payment;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Stripe\Charge;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Traits\StripePaymentTrait;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class CartController extends Controller
{
    use StripePaymentTrait;

    public function cart()
    {
        $data['cartContent'] = Cart::content();

        return view('front-end.cart', $data);
    }

    public function addToCart(Request $request)
    {
        $products = Product::with('product_images_fun')->find($request->id);

        if (is_null($products)) return response()->json(['status' => false, 'msg' => 'Product Not Found Here!!']);

        if (Cart::count() > 0) {
            $cartContent = Cart::content();

            $prodcutAlreadyExist = false;

            foreach ($cartContent as $items) {
                if ($items->id == $products->id) {
                    $prodcutAlreadyExist = true;
                }
            }

            if ($prodcutAlreadyExist == false) {
                Cart::add($products->id, $products->title, 1, $products->price, ['product_images_fun' => (!empty($products->product_images_fun)) ? $products->product_images_fun->first() : '']);

                $status = true;
                $msg = '<strong style="color:#220707"> '.$products->title.' </strong> Added In Cart Successfull';
                session()->flash('success', $msg);

            } else {
                $status = false;
                $msg = $products->title . ' Product already exists in your cart!!';   
            }

        } else {
            Cart::add($products->id, $products->title, 1, $products->price, ['product_images_fun' => (!empty($products->product_images_fun)) ? $products->product_images_fun->first() : '']);
 
            $status = true;
            $msg = '<strong style="color:#220707"> '.$products->title.' </strong> Added In Cart Successfull';
            session()->flash('success', $msg);
        }
        
        return response()->json(['status' => $status, 'msg' => $msg]);
    }

    public function updateCart(Request $request)
    {
        $status = false;   // Initialize variables for the response
        $msg = '';

        if (!empty($request->rowId)) {  
            $rowId = $request->rowId;
            $qty = $request->qty;

            $productStockQty = Cart::get($rowId);

            $products = Product::find($productStockQty->id);  
            
            if ($products->track_qty == 'Yes') {
                if ($qty <= $products->qty) {
                    Cart::update($rowId, $qty);
                    
                    $status = true;
                    $msg = '<strong style="color:#220707">'. $products->title .'</strong> Updated Successfully';
                    session()->flash('success', $msg);
                } else {
                    $status = false;
                    $msg = 'Requested Qty ('.$products->qty.' Quantity) Not Availabe In Stock';

                    session()->flash('error', $msg);
                }
            } else {
                Cart::update($rowId, $qty);

                $status = true;
                $msg = $products->title . ' Updated In Cart Successfull';
                session()->flash('error', $msg);
            }

            return response()->json(['status' => $status, 'msg' => $msg]);
        }
    }

    public function deleteCartItems(Request $request) 
    {
        if (!empty($request->rowId)) {
            $cartItem = Cart::get($request->rowId);

            if (is_null($cartItem)) {
                $status = false;
                $msg = 'The specified item was not found in the cart';
                session()->flash('error', $msg);

            } else {
                Cart::remove($request->rowId);

                $status = true;
                $msg = 'Items remove from cart successfully...';
                session()->flash('success', $msg);
            }           
        } else {
            $status = true;
            $msg = 'Item not found!';
            session()->flash('error', $msg);
        }

        return response()->json(['status' => $status, 'msg' => $msg]);
    }

    // public function checkout()
    // {
    //     if (Cart::count() == 0) {
    //         return redirect()->route('front-end.cart');
    //     }

    //     if (!Auth::check()) {                           // if user not login, redirect login page
    //         if (!session()->has('url.intended')) {      // capture current URL
    //             session(['url.intended' => url()->current()]);
    //             // dd(session(['url.intended' => url()->current()]));
    //         }
    //         return redirect()->route('account.login');
    //     }        
    //     session()->forget('url.intended');
        
    //     return view('front-end.checkout');
    // }

    public function checkout()
    {
        $discountCoupon = 0;

        if (Cart::count() == 0) {
            return redirect()->route('front-end.cart');
        }

        // if user not login, redirect login page
        if (!Auth::check()) {    
            session(['url.intended' => url()->current()]);
            return redirect()->guest(route('account.login'));
        }    

        session()->forget('url.intended');    

        $countries = Country::orderBy('name', 'asc')->get();

        $subTotal = Cart::subtotal(2, '.', '');

        // Apply coupon code & set in session for not remove after page reload
        if (session()->has('couponCode')) {
            $couponCode = session()->get('couponCode');
            
            if ($couponCode->type == 'percent') {
                $discountCoupon = ($couponCode->discount_amount / 100) * $subTotal;
            } else {
                $discountCoupon = $couponCode->discount_amount;
            }
        }

        // Calculate shipping charges according to country
        $customerAddress = CustomerAddress::firstWhere('user_id', Auth::user()->id);
        
        if (!empty($customerAddress)) {
            $shippingInfo = ShippingCharge::where('country_id', $customerAddress->country_id)->first();
            
            $totalQty = 0;    
            $totalShippingCharges = 0;    
            $grandTotal = 0;   

            foreach (Cart::content() as $cartItem) {
                $totalQty += $cartItem->qty;
            }
            
            if ($shippingInfo) 
                $totalShippingCharges = $totalQty * $shippingInfo->amount;  
            else 
                $totalShippingCharges = 0;
        } else {
            $totalShippingCharges = 0;
        }

        $grandTotal = ($subTotal - $discountCoupon) + $totalShippingCharges;
        
        return view('front-end.checkout', [
            'countries'       => $countries,
            'discountCoupon'  => $discountCoupon,
            'customerAddress' => $customerAddress,
            'totalShippingCharges' => $totalShippingCharges,
            'grandTotal' => number_format($grandTotal, 2),
        ]);
    }

    // public function checkEmailValidation_12345(Request $request)
    // {
    //     if (!empty($request->input('email'))) {
    //         $email = $request->input('email');

    //         $userEmail = CustomerAddress::where('email', $email)->first();  
            
    //         if (!is_null($userEmail)) {    
    //             return json_encode(false);
    //         } else {
    //             return json_encode(true);
    //         }
    //     }
    // }

    public function processCheckout(Request $request)
    {
        $user = Auth::user();
        
        CustomerAddress::updateOrCreate(     // Step 1- store data in custorm address table of user
            ['user_id' => $user->id],
            [
                'user_id'    => $user->id,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'mobile'     => $request->mobile,
                'country_id' => $request->country,
                'address'    => $request->address,
                'apartment'  => $request->apartment,
                'city'       => $request->city,
                'state'      => $request->state,
                'zip'        => $request->zip,
            ] 
        );

        /***** step 2- store data in order table of bookig order *****/
        
        if ($request->payment_method === 'cod') {
            $discountCodeId = $promoCode = null;
            $shipping = $discountCoupon = 0;
            
            $subTotal = Cart::subtotal(2, '.', '');

            /****** Apply coupon code  ******/
            if (session()->has('couponCode')) {
                $couponCode = session()->get('couponCode');
                
                if ($couponCode->type == 'percent') {
                    $discountCoupon = ($couponCode->discount_amount / 100) * $subTotal;
                } else {
                    $discountCoupon = $couponCode->discount_amount;
                }

                $discountCodeId = $couponCode->id;
                $promoCode = $couponCode->coupon_code;
            }

            /****** Calculate shipping charges  ******/
            $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();
            
            $totalQty = 0;

            foreach (Cart::content() as $cartItem) {
                $totalQty += $cartItem->qty;
            }

            if ($shippingInfo != null) {
               $shipping   = $totalQty * $shippingInfo->amount; 
               $grandTotal = ($subTotal - $discountCoupon) + $shipping;

            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

                $shipping   = $totalQty * $shippingInfo->amount; 
                $grandTotal = ($subTotal - $discountCoupon) + $shipping;
            }
    
            $order = new Order();
            $order->subtotal    = $subTotal;
            $order->discount    = $discountCoupon;
            $order->shipping    = $shipping;
            $order->grand_total = $grandTotal;

            $order->coupon_code_id = $discountCodeId;
            $order->coupon_code    = $promoCode;
            $order->payment_status = 'not_paid';
            $order->status         = 'pending';
            
            $order->user_id    = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name  = $request->last_name;
            $order->email      = $request->email;
            $order->mobile     = $request->mobile;
            $order->country_id = $request->country;
            $order->address    = $request->address;
            $order->apartment  = $request->apartment;
            $order->city       = $request->city;
            $order->state      = $request->state;
            $order->zip        = $request->zip;
            $order->notes      = $request->notes;
            $order->save();

            // step 3- store data in OrderItems table
            foreach (Cart::content() as $cartItems) {
                $orderItems = new OrderItem;
                
                $orderItems->product_id = $cartItems->id;
                $orderItems->order_id = $order->id;
                $orderItems->name     = $cartItems->name;
                $orderItems->qty      = $cartItems->qty;
                $orderItems->price    = $cartItems->price;
                $orderItems->total    = $cartItems->price * $cartItems->qty;
                $orderItems->save();

                // check track quantity of remaining product in product table
                $productData = Product::find($cartItems->id);

                if ($productData->track_qty == 'Yes') {   
                    $currentQty = $productData->qty;
                    $updatedQty = $currentQty - $cartItems->qty;
            
                    $productData->qty = $updatedQty;
                    $productData->save();
                }
            }

            orderEmail($order->id, 'customer');    // Send Order Email
            session()->flash('success', 'Your order id #'.$order->id.' has been placed successfully!');

            return response()->json([
                'status' => true, 
                'msg' => 'Order save successfully!!', 
                'redirect' => route('front-end.thankuPage'),
                'orderId' => $order->id
            ]);
        } else if ($request->payment_method === 'authorize_net') {
            $discountCodeId = $promoCode = null;
            $shipping = $discountCoupon = 0;
            
            $subTotal = Cart::subtotal(2, '.', '');

            // Apply coupon code
            if (session()->has('couponCode')) {
                $couponCode = session()->get('couponCode');
                
                if ($couponCode->type == 'percent') {
                    $discountCoupon = ($couponCode->discount_amount / 100) * $subTotal;
                } else {
                    $discountCoupon = $couponCode->discount_amount;
                }

                $discountCodeId = $couponCode->id;
                $promoCode = $couponCode->coupon_code;
            }

            // Calculate shipping charges 
            $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();
            
            $totalQty = 0;

            foreach (Cart::content() as $cartItem) {
                $totalQty += $cartItem->qty;
            }

            if ($shippingInfo != null) {
               $shipping   = $totalQty * $shippingInfo->amount; 
               $grandTotal = ($subTotal - $discountCoupon) + $shipping;

            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

                $shipping   = $totalQty * $shippingInfo->amount; 
                $grandTotal = ($subTotal - $discountCoupon) + $shipping;
            }

            $order = new Order();
            $order->subtotal    = $subTotal;
            $order->discount    = $discountCoupon;
            $order->shipping    = $shipping;
            $order->grand_total = $grandTotal;

            $order->coupon_code_id = $discountCodeId;
            $order->coupon_code    = $promoCode;
            $order->payment_status = 'not_paid';
            $order->status         = 'pending';
            
            $order->user_id    = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name  = $request->last_name;
            $order->email      = $request->email;
            $order->mobile     = $request->mobile;
            $order->country_id = $request->country;
            $order->address    = $request->address;
            $order->apartment  = $request->apartment;
            $order->city       = $request->city;
            $order->state      = $request->state;
            $order->zip        = $request->zip;
            $order->notes      = $request->notes;
            $order->save();

            // step 3- store data in OrderItems table
            foreach (Cart::content() as $cartItems) {
                $orderItems = new OrderItem;
                
                $orderItems->product_id = $cartItems->id;
                $orderItems->order_id   = $order->id;
                $orderItems->name       = $cartItems->name;
                $orderItems->qty        = $cartItems->qty;
                $orderItems->price      = $cartItems->price;
                $orderItems->total      = $cartItems->price * $cartItems->qty;
                $orderItems->save();

            }
            
            $paymentResponse = app(PaymentController::class)->authorizeCharge($request, $order);
            $paymentData = $paymentResponse->getData(true);

            if (empty($paymentData['status'])) {
                return $paymentResponse;
            }

            foreach (Cart::content() as $cartItems) {
                $productData = Product::find($cartItems->id);

                if ($productData && $productData->track_qty == 'Yes') {   
                    $productData->qty = $productData->qty - $cartItems->qty;
                    $productData->save();
                }
            }

            session()->flash('success', 'Your order id #'.$order->id.' has been placed successfully!');

            return response()->json([
                'status' => true,
                'orderId' => $order->id,
                'redirect' => route('front-end.thankuPage')
            ]);

        } else if ($request->payment_method === 'stripe') {
            /*============== Stripe payment here ==============*/

            $discountCodeId = $promoCode = null;
            $shipping = $discountCoupon = 0;
            
            $subTotal = Cart::subtotal(2, '.', '');

            // Apply coupon code
            if (session()->has('couponCode')) {
                $couponCode = session()->get('couponCode');
                
                if ($couponCode->type == 'percent') {
                    $discountCoupon = ($couponCode->discount_amount / 100) * $subTotal;
                } else {
                    $discountCoupon = $couponCode->discount_amount;
                }

                $discountCodeId = $couponCode->id;
                $promoCode = $couponCode->coupon_code;
            }

            // Calculate shipping charges 
            $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();
            
            $totalQty = 0;

            foreach (Cart::content() as $cartItem) {
                $totalQty += $cartItem->qty;
            }

            if ($shippingInfo != null) {
               $shipping   = $totalQty * $shippingInfo->amount; 
               $grandTotal = ($subTotal - $discountCoupon) + $shipping;

            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

                $shipping   = $totalQty * $shippingInfo->amount; 
                $grandTotal = ($subTotal - $discountCoupon) + $shipping;
            }

            $order = new Order();
            $order->subtotal    = $subTotal;
            $order->discount    = $discountCoupon;
            $order->shipping    = $shipping;
            $order->grand_total = $grandTotal;

            $order->coupon_code_id = $discountCodeId;
            $order->coupon_code    = $promoCode;
            $order->payment_status = 'not_paid';
            $order->status         = 'pending';
            
            $order->user_id    = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name  = $request->last_name;
            $order->email      = $request->email;
            $order->mobile     = $request->mobile;
            $order->country_id = $request->country;
            $order->address    = $request->address;
            $order->apartment  = $request->apartment;
            $order->city       = $request->city;
            $order->state      = $request->state;
            $order->zip        = $request->zip;
            $order->notes      = $request->notes;
            $order->save();

            // step 3- store data in OrderItems table
            foreach (Cart::content() as $cartItems) {
                $orderItems = new OrderItem;
                
                $orderItems->product_id = $cartItems->id;
                $orderItems->order_id = $order->id;
                $orderItems->name     = $cartItems->name;
                $orderItems->qty      = $cartItems->qty;
                $orderItems->price    = $cartItems->price;
                $orderItems->total    = $cartItems->price * $cartItems->qty;
                $orderItems->save();

                /***** check track quantity of remaining product in procut table *****/
                $productData = Product::find($cartItems->id);

                if ($productData->track_qty == 'Yes') {   
                    $currentQty = $productData->qty;
                    $updatedQty = $currentQty - $cartItems->qty;
            
                    $productData->qty = $updatedQty;
                    $productData->save();
                }
            }
            
            $paymentController = app(PaymentController::class)->createPaymentIntent($order);

            session()->flash('success', 'Your order id #'.$order->id.' has been placed successfully!');

            return response()->json([
                'status'        => true, 
                'msg'           => 'success',
                'orderId'       => $order->id,
                'client_secret' => $paymentController->client_secret,
                'redirect'      => route('front-end.thankuPage'), 
                // 'session'       => $session,
                // 'redirect_url' => $session->url,  // only use when createStripeSession in Trait controller or Controller with checkout session create
            ]);
        }
    }

    public function thankuPage(Request $request)
    {
        $orderId = $request->orderId;

        if(!$orderId){
            abort(404);
        }

        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $payment = Payment::where('order_id', $orderId)->where('user_id', Auth::id())->latest()->first();

        $onlinePayment = Payment::where('order_id', $order->id)
            ->whereIn('payment_gateway', ['authorize_net', 'stripe'])
            ->latest()
            ->first();

        if ($onlinePayment && $onlinePayment->status !== 'succeeded') {
            return redirect()->route('front-end.checkout')->with('error', 'Payment not completed. Please try again.');
        }

        Cart::destroy();
        session()->forget('couponCode');

        return view('front-end.thankuPage', compact('order', 'payment'));
    }

    /***************** Calculate shipping country *****************/
    
    // Improve Version, Type 1
    public function getOrderSummery(Request $request)  
    {
        $discountCoupon = 0;
        $couponHTML = null;

        $subTotal = (float) Cart::subtotal(2, '.', '');

        if ((int)$request->input('country_id') > 0) {
            // Apply coupon code
            if (session()->has('couponCode') && session('couponCode')) {

                $couponCode = session('couponCode');

                if ($couponCode->type === 'percent') {
                    $discountCoupon = round(($couponCode->discount_amount / 100) * $subTotal, 2);
                } else {
                    $discountCoupon = (float) $couponCode->discount_amount;
                }

                // Prevent negative totals
                $discountCoupon = min($discountCoupon, $subTotal);

                $couponHTML = '<div class="mt-4" id="remove-coupon-response">
                    <strong>'. e($couponCode->coupon_code) .'</strong>
                    <a class="btn btn-sm btn-danger" id="remove-discount">
                        <i class="fa fa-times"></i>
                    </a>
                </div>';
            }
            
            // Calculate shipping charges based on selected country
            $shippingInfo = ShippingCharge::where('country_id', $request->input('country_id'))->first();

            $totalQty = Cart::content()->sum('qty');

            if (!$shippingInfo) {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
            }

            $shippingCharge = 0;

            if ($shippingInfo) {
                $shippingCharge = $totalQty * (float) $shippingInfo->amount;
            }

            $grandTotal = max(0, ($subTotal - $discountCoupon) + $shippingCharge);

            return response()->json([
                'status'         => true,
                'couponHTML'     => $couponHTML,
                'discountCoupon' => number_format($discountCoupon, 2),
                'shippingCharge' => number_format($shippingCharge, 2),
                'grandTotal'     => number_format($grandTotal, 2),
            ]);
        }

        // No country selected
        $grandTotal = max(0, $subTotal - $discountCoupon);

        return response()->json([
            'status'         => true,
            'couponHTML'     => $couponHTML,
            'shippingCharge' => number_format(0, 2),
            'discountCoupon' => number_format($discountCoupon, 2),
            'grandTotal'     => number_format($grandTotal, 2),
        ]);
    }

    // Type 2
    // public function getOrderSummery(Request $request)
    // {
    //     $discountCoupon = 0;
    //     $couponHTML = 0;

    //     $subTotal = Cart::subtotal(2, '.', '');

    //     if ($request->input('country_id') > 0) {
    //         $shippingInfo = ShippingCharge::where('country_id', $request->input('country_id'))->first();

    //         $totalQty = 0;

    //         foreach (Cart::content() as $cartItem) {
    //             $totalQty += $cartItem->qty;
    //         }

    //         // Apply coupon code
    //         if (session()->has('couponCode')) {
    //             $couponCode = session()->get('couponCode');
                
    //             if ($couponCode->type == 'percent') {
    //                 $discountCoupon = ($couponCode->discount_amount / 100) * $subTotal;
    //             } else {
    //                 $discountCoupon = $couponCode->discount_amount;
    //             }

    //             /*********** show coupon code when new coupon code apply wihout page reload ***********/
    //             $couponHTML = '<div class="mt-4" id="remove-coupon-response">
    //                 <strong>'. session()->get('couponCode')->coupon_code .'</strong>
    //                 <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
    //             </div>';
    //         }
            
    //         if ($shippingInfo !== null) {
    //             $shippingCharge = $totalQty * $shippingInfo->amount;
    //             $grandTotal = ($subTotal - $discountCoupon) + $shippingCharge;
    //         } else {
    //             $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

    //             $shippingCharge = $totalQty * $shippingInfo->amount;
    //             $grandTotal = ($subTotal - $discountCoupon) + $shippingCharge;
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'couponHTML' => $couponHTML,
    //             'discountCoupon' => number_format($discountCoupon, 2),
    //             'shippingCharge' => number_format($shippingCharge, 2),
    //             'grandTotal' => number_format($grandTotal, 2),
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status' => true,
    //             'couponHTML' => $couponHTML,
    //             'shippingCharge' => number_format(0, 2),
    //             'discountCoupon' => number_format($discountCoupon, 2),
    //             'grandTotal' => number_format(($subTotal -$discountCoupon), 2),
    //         ]);
    //     }
    // }

    public function applyCouponCode(Request $request)
    {
        if (!empty($request->country_id) && !empty($request->couponCode)) {
            $couponCode = DiscountCoupon::where('coupon_code', $request->couponCode)->first();
            
            if (is_null($couponCode)) return response()->json(['status' => false, 'msg' => 'Invalid discount coupon']);

            if ($couponCode->status == 1) {
                $now = Carbon::now();
                $userId = Auth::id();

                if ($couponCode->starts_at != '') {
                    $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $couponCode->starts_at); 
                    
                    if ($now->lt($startDate)) 
                        return response()->json(['status' => false, 'msg' => 'Current date or time should be greater then Start Date']);
                }

                if ($couponCode->expires_at != '') {
                    $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $couponCode->expires_at);

                    if ($now->gt($endDate)) 
                        return response()->json(['status' => false, 'msg' => 'Expires date must be greter then start date or time']);
                }
                
                // check maximum number of used this coupon code 
                if ($couponCode->max_uses > 0) {
                    $couponUsed = Order::where('coupon_code_id', $couponCode->id)->count();
                    
                    if ($couponUsed > $couponCode->max_uses) 
                        return response()->json(['status' => false, 'msg' => 'Maximum limit used this coupon code, try other !!']);
                }

                // check maximum number of user used this coupon code
                if ($couponCode->max_uses_user > 0) {
                    $couponUsed = Order::where(['coupon_code_id' => $couponCode->id, 'user_id' => $userId])->count();
                    
                    if ($couponUsed >= $couponCode->max_uses_user) 
                        return response()->json(['status' => false, 'msg' => 'Maximum limit used this coupon code, try other !!']);
                }

                // check minimum amount of product price
                if ($couponCode->max_uses_user > 0) {
                    $subTotal = Cart::subtotal(2, '.', '');
                    
                    if ($subTotal < $couponCode->min_amount)
                        return response()->json(['status' => false, 'msg' => 'Your minimum amount must be grether then subtotal']);
                }

                session()->put('couponCode', $couponCode);
                return $this->getOrderSummery($request);

            } else {
                return response()->json(['status' => false, 'msg' => 'Coupon Code Status Is De-Active, Please Active Coupon Code First!!']);
            }
        }
    }

    public function removeCouponCode(Request $request)
    {
        session()->forget('couponCode');
        return $this->getOrderSummery($request);
    }

    /*=========
        Free domain Virendera
        InfinityFree dashboard:- https://dash.infinityfree.com/accounts

        Username:- if0_41783586
        Label:- Website for virendra-barber.kesug.com

        url:-  http://virendra-barber.kesug.com/

        Account Password:-  1WSYsfYHjTAj
    =========*/



  
}
