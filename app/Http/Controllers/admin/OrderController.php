<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;


class OrderController extends Controller
{
    public function listing(Request $request)
    {
        $orders = Order::latest('orders.created_at')->select('orders.*', 'users.name', 'users.email')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id');

            if (filled($request->search)) {
                $search = trim($request->input('search'));

                if (is_numeric($search)) {
                    $orders = $orders->where('orders.id', (int)$search)->limit(1);
                    $orders->first();
                } else {
                    $orders = $orders->where(function ($query) use ($search) {
                        $query->where('users.name', 'like', "%{$search}%")
                            ->orWhere('users.email', 'like', "%{$search}%")
                            ->orWhere('orders.mobile', 'like', "%{$search}%");
                    });   
                }
            }

        $orders = $orders->paginate(10);

        return view('admin.orders.listing', [
            'orders' => $orders,
        ]);
    }

    public function orderDetail($orderId)
    {
        $orders = Order::where('orders.id', $orderId)
            ->select('orders.*', 'countries.name as countriesName')
            ->leftJoin('countries', 'orders.country_id', '=', 'countries.id')
            ->first()
        ;

        if (empty($orders)) {
            return redirect()->route('orders.listing')->with('error', 'Order not found or has been deleted.');
        }

        $orderItems = OrderItem::where('order_id', $orderId)->get();

        return view('admin.orders.order-detail', [
            'orders' => $orders,
            'orderItems' => $orderItems,
        ]);
    }


    public function changeOrderStatus(Request $request, $orderId)
    {
        $orders = Order::find($orderId);

        $orders->shipped_date = \Carbon\Carbon::parse($request->shipped_date);

        $orders->save();

        session()->flash('success', 'Order status updated successfully !!');

        return response()->json(['status' => true, 'msg' => 'Order status updated successfully !!']);
    }

    public function sendEmailInvoice(Request $request, $orderId)
    {
        $status = false;
        $msg = 'Email sending failed!';
        
        if (!empty($request->userType)) {
            $sendMail = orderEmail($orderId, $request->userType);

            if ($sendMail) {
                $status = true;
                $msg = 'Order email sent successfully!';
                session()->flash('success', $msg);
            } else {
                session()->flash('error', $msg);
            }
        } else {
            $msg = 'Please select a user type for sending the email !!';

            session()->flash('error', $msg);
        }

        return response()->json(['status' => $status, 'msg' => $msg]);
    }

   


}
