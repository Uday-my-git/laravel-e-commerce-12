<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function downloadPDF(Request $request, $orderId)
    {
        if (!empty($orderId)) {
            $data['orders'] = Order::where('orders.id', $orderId)
                ->select('orders.*', 'countries.name as countriesName')
                ->leftJoin('countries', 'orders.country_id', '=', 'countries.id')
                ->first();

            $data['orderItems'] = OrderItem::where('order_id', $orderId)->get();

            $pdf = Pdf::loadView('admin.pdf_download.pdf', $data)->setPaper('a4', 'portrait')->setWarnings(false);
        }
        return $pdf->download('order-id#' .$orderId. '.pdf');
    }

    public function listingPDF(Request $request, $orderId)
    {
        $data['orders'] = Order::where('orders.id', $orderId)
            ->select('orders.*', 'countries.name as countriesName')
            ->leftJoin('countries', 'orders.country_id', '=', 'countries.id')
            ->first();

        $data['orderItems'] = OrderItem::where('order_id', $orderId)->get();

        return view('admin.pdf_download.listingPDF', $data);
    }
}
