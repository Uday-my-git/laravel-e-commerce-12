<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Shipping;
use App\Models\Country;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class ShippingController extends Controller
{
    public function listing(Request $request)
    {
        $countrires = Country::orderBy('name', 'asc')->get();
        $data['countrires'] = $countrires;

        $shipping = DB::table('shipping_charges')
            ->select('shipping_charges.*', 'countries.name as countryName')
            ->join('countries', 'shipping_charges.country_id', '=', 'countries.id');

        if (!empty($request->get('search'))) {
            $shipping = $shipping->where('countries.name', 'like', '%'. $request->get('search') .'%');
        }

        $data['shipping'] = $shipping->paginate(10);

        return view('admin.shipping.listing', $data);
    }   

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|unique:shipping_charges',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->route('shipping.listing')->withErrors($validator);
        } else {
            DB::table('shipping_charges')->insert([
                'country_id' => $request->country_id,
                'amount' => $request->amount,
            ]);

            // session()->flash('success', 'Shipping charges save successfullly');
            return redirect()->route('shipping.listing')->withSuccess('Shipping charges save successfullly');
        }
    } 

    public function edit(Request $request, $id)
    {
        if (empty($id)) return redirect()->with('error', 'Invalid shipping ID');  // Check if ID is null or empty

        $shipping = DB::table('shipping_charges')->find($id);

        if (!$shipping) return redirect()->back()->with('error', 'Shipping data not found for edit data!!!');
        
        $data['countrires'] = DB::table('countries')->get();

        $data['shipping'] = $shipping;

        return view('admin.shipping.edit', $data);
    }

    public function update(Request $request, $id)
    {
    
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'amount' => 'required|numeric',
        ]);

        $shipping = DB::table('shipping_charges')->where('id', $id)->exists();

        if (empty($id)) {
            return redirect()->with('error', 'Invalid shipping ID for update data');  // Check if ID is null or empty

        } else if (!$shipping) {
            return redirect()->with('error', 'Invalid shipping ID for update data');

        }  else if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();

        } else {
            DB::table('shipping_charges')->where('id', $id)->update([
                'country_id' => $request->country_id,
                'amount' => $request->amount,
            ]);

            return redirect()->route('shipping.listing')->withSuccess('Shipping charges update successfullly');
        }

        // if (empty($id)) return redirect()->with('error', 'Invalid shipping ID for update data');  // Check if ID is null or empty

        // $validator = Validator::make($request->all(), [
        //     'country_id' => 'required',
        //     'amount' => 'required|numeric',
        // ]);

        // if ($validator->passes()) {
        //     DB::table('shipping_charges')->where('id', $id)->update([
        //         'country_id' => $request->country_id,
        //         'amount' => $request->amount,
        //     ]);

        //     return redirect()->route('shipping.listing')->withSuccess('Shipping charges update successfullly');
        // } else {
        //     return redirect()->back()->withErrors($validator)->withInput();
        // }

    }

    public function delete($id)
    {
        if (empty($id)) return redirect()->with('error', 'Invalid shipping ID!!!');  // Check if ID is null or empty

        $shipping = DB::table('shipping_charges')->where('id', $id)->first();       // Check if record exists

        if ($shipping) {
            $shipping = DB::table('shipping_charges')->where('id', $id)->delete();

            return redirect()->back()->with('success', 'Shipping data deleted successfullly');
        } else {
            return redirect()->back()->withErrors('Shipping data not found !!!')->withInput();
        }
    
    }


}
