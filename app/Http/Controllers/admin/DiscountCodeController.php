<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiscountCoupon;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class DiscountCodeController extends Controller
{
    public function listing(Request $request)
    {
        $couponCode = DiscountCoupon::orderBy('id', 'asc');

        if ($request->input('search')) {
            $couponCode = $couponCode->where('coupon_code', 'like', '%'.$request->get('search').'%')
                ->orWhere('name', 'like', '%'.$request->get('search').'%');
        }

        $couponCode = $couponCode->paginate(10);

        return view('admin.coupon.listing', compact('couponCode'));
    }

    public function create()
    {
       return view('admin.coupon.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code'     => 'required',
            'name'            => 'required',
            'description'     => 'required',
            'max_uses'        => 'required',
            'discount_amount' => 'required',
            'min_amount'      => 'required',
            'max_uses_user'   => 'required',
            'starts_at'  => 'required',
            'expires_at' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            // Starting date must be greater then current code
            if (!empty($request->starts_at)) {
                $now     = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);

                if ($startAt->lte($now) == true) {
                    return response()->json(['status' => false, 'errors' => ['starts_at' => 'Start date can not be less than, current date !!']]);
                }
            }

            // Expirey date must be greater then start date
            if (!empty($request->starts_at) && !empty($request->expires_at)) {
                $startAt   = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);

                if (!empty($expiresAt->gt($startAt)) == false) {
                    return response()->json(['status' => false, 'errors' => ['expires_at' => 'Expiary date must be greater then start date !!']]);
                }
            }

            $couponCode = DiscountCoupon::create([
                'coupon_code'   => $request->coupon_code,
                'name'          => $request->name,
                'description'   => $request->description,
                'max_uses'      => $request->max_uses,
                'max_uses_user' => $request->max_uses_user,
                'type'          => $request->type,
                'discount_amount' => $request->discount_amount,
                'min_amount' => $request->min_amount,
                'status'     => $request->status,
                'starts_at'  => $request->starts_at,
                'expires_at' => $request->expires_at,
            ]);

            session()->flash('success', 'Coupon code '.$couponCode->name.' added successfully !!');
            return response()->json(['status' => true, 'msg' => 'coupon code added successfully']);
        }
    }

    public function edit($id)
    {
        $couponCode = DiscountCoupon::find($id);

        if(empty($couponCode)) return redirect()->route('coupon.listing')->with('error', 'Invalid coupon or data found ??');

        return view('admin.coupon.edit', compact('couponCode'));
    }

    public function update(Request $request, $id)
    {
       $couponCode = DiscountCoupon::find($id);

        if(empty($couponCode)) {
            session()->flash('error', 'Coupon Code Not Found Or Invalid Data ??');
            return response()->json(['err' => false, 'msg' => 'coupon code not found or invalid data ??']);
        }

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required',
            'name'        => 'required',
            'description' => 'required',
            'max_uses'    => 'required',
            'discount_amount' => 'required',
            'min_amount'      => 'required',
            'max_uses_user'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        } else {
            // Expirey date must be greater then start date
            if (!empty($request->starts_at) && !empty($request->expires_at)) {
                $startAt   = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);

                if (!empty($expiresAt->gt($startAt)) == false) {
                    return response()->json(['status' => false, 'errors' => ['expires_at' => 'Expiry date must be greater then start date !!']]);
                }
            }
            
            $couponCode->update([
                'coupon_code'     => $request->coupon_code,
                'name'            => $request->name,
                'description'     => $request->description,
                'max_uses'        => $request->max_uses,
                'max_uses_user'   => $request->max_uses_user,
                'type'            => $request->type,
                'discount_amount' => $request->discount_amount,
                'min_amount'      => $request->min_amount,
                'status'     => $request->status,
                'starts_at'  => $request->starts_at,
                'expires_at' => $request->expires_at,
            ]);

            session()->flash('success', 'Coupon Code '.$couponCode->name.' UPDATED Successfully !!');
            return response()->json(['status' => true, 'msg' => 'coupon code UPDATED successfully']);
        }
    }
    
    public function destroy($id)
    {
        $couponCode = DiscountCoupon::find($id);

        if (is_null($couponCode)) {
            session()->flash('error', 'Records not found in the database');
            return response()->json(['status' => true, 'errors' => 'records not found in the database']);
        }

        $couponCode->delete();

        session()->flash('success', 'Discount coupons deleted successfully!!');
        return response()->json(['status' => true, 'msg' => 'discount coupons deleted successfully']);
    }

    public function deleteAll(Request $request)
    {
        $couponCode = DiscountCoupon::whereIn('id', explode(',', $request->ids))->delete();

        if (!$couponCode) {
            $request->session()->flash('error', 'This user are not found in database !!');
            return response()->json(['status' => true, 'msg' => 'This user are not found in database !!']);     
        }
               
        session()->flash('success', 'Discount coupons deleted successfully!!');
        return response()->json(['status' => true, 'msg' => "All Coupon Code Deleted successfully."]);
    }



}
