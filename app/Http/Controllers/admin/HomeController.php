<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\TempImage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use File;

class HomeController extends Controller
{
    // This is optimize version of index function
    public function index()
    {
        $now = Carbon::now();
        
        // Count total revenue of this month, old month or last 30 days ago month
        // Base order query (reuse everywhere with clone function)
        $validOrders = Order::where('status', '!=', 'cancelled');
         
        $totalOrders  = (clone $validOrders)->count();
        $totalRevenue = (clone $validOrders)->sum('grand_total') ?? 0;

        $totalCustomers = User::where('role', 1)->count();
        $totalProducts  = Product::count();

        // Count total cancel, pending, shipped, delivered orders
        $orderCounts = Order::select(DB::raw('count(*) as total, status'))->groupBy('status')->pluck('total', 'status');
        
        $pendingOrders   = $orderCounts['pending'] ?? 0;
        $shippedOrders   = $orderCounts['shipped'] ?? 0;
        $deliverdOrders  = $orderCounts['deliverd'] ?? 0;
        $cancelledOrders = $orderCounts['cancelled'] ?? 0;
        
        // This month revenue calculate
        $startMonth = $now->copy()->startOfMonth();
        $endMonth   = $now->copy()->endOfDay();
        $thisMonthName = $now->copy()->startOfMonth()->format('M');

        $revenueThisMonth = (clone $validOrders)
            ->whereBetween('created_at', [$startMonth, $endMonth])   // type 1
            ->sum('grand_total') ?? 0    
        ;

        // Last month revenue calculate
        $lastMonthStartDate = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $lastMonthEndDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        $lastMonthName = $now->copy()->subMonth()->endOfMonth()->format('M');

        $lastThisMonth = (clone $validOrders)
            ->whereBetween('created_at', [$lastMonthStartDate, $lastMonthEndDate]) 
            ->sum('grand_total') ?? 0    
        ;

        // Last 30 days revenue calculate
        $thirtyDaysAgo = $now->copy()->subDays(30)->format('Y-m-d');

        $revenueLastThirtyDays = (clone $validOrders)
            ->whereDate('created_at', '>=', $thirtyDaysAgo)     // type 2
            ->whereDate('created_at', '<=', $endMonth)    
            ->sum('grand_total') ?? 0    
        ;

        // Delete old temp images before today date
        $dayBeforeToday = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');

        $tempImages = TempImage::where('created_at', '<=', $dayBeforeToday)->get();

        foreach ($tempImages as $tempImg) {
            $paths = [
                public_path('temp/' . $tempImg->name),
                public_path('temp/thumb/' . $tempImg->name)
            ]; 

            foreach ($paths as $file) {
                if (File::exists($file)) {
                    File::delete($file);
                }
                TempImage::where('id', $tempImg->id)->delete();
            }
            session()->flash('success', 'Old images deleted Successfully !!!');
        }

        return view('admin.dashboard', [
            'totalOrders'     => $totalOrders,
            'pendingOrders'   => $pendingOrders,
            'shippedOrders'   => $shippedOrders,
            'deliverdOrders'  => $deliverdOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalCustomers'  => $totalCustomers,
            'totalProducts'   => $totalProducts,
            'totalRevenue'    => $totalRevenue,
            'revenueThisMonth' => $revenueThisMonth,
            'thisMonthName'    => $thisMonthName,
            'lastThisMonth'    => $lastThisMonth,
            'lastMonthName'    => $lastMonthName,
            'revenueLastThirtyDays' => $revenueLastThirtyDays
        ]);
    }

    // public function index()
    // {
    //     $now = Carbon::now();
        
    //     $totalOrders = Order::where('status', '!=', 'cancelled')->count();
    //     $totalUsers = User::where('role', 1)->count();
    //     $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('grand_total');
    //     $totalProducts = Product::count();

    //     // This month revenue calculate
    //     $startMonth = $now->copy()->startOfMonth()->format('Y-m-d');
    //     $thisMonthName = $now->copy()->startOfMonth()->format('M');

    //     $currentDate = $now->copy()->format('Y-m-d');

    //     $revenueThisMonth = Order::where('status', '!=', 'cancelled')
    //         ->whereBetween('created_at', [$startMonth, $currentDate])   // type 1
    //         ->sum('grand_total') ?? 0    
    //     ;

    //     // Last month revenue calculate
    //     $lastMonthStartDate = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
    //     $lastMonthEndDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

    //     $lastMonthName = $now->copy()->subMonth()->endOfMonth()->format('M');

    //     $lastThisMonth = Order::where('status', '!=', 'cancelled')
    //         ->whereBetween('created_at', [$lastMonthStartDate, $lastMonthEndDate]) 
    //         ->sum('grand_total') ?? 0    
    //     ;

    //     // Last 30 days revenue calculate
    //     $thirtyDaysAgo = $now->copy()->subDays(30)->format('Y-m-d');

    //     $revenueLastThirtyDays = Order::where('status', '!=', 'cancelled')
    //         ->whereDate('created_at', '>=', $thirtyDaysAgo)     // type 2
    //         ->whereDate('created_at', '<=', $currentDate)    
    //         ->sum('grand_total') ?? 0    
    //     ;

    //     return view('admin.dashboard', [
    //         'totalOrders' => $totalOrders,
    //         'totalUsers' => $totalUsers,
    //         'totalProducts' => $totalProducts,
    //         'totalRevenue' => $totalRevenue,
    //         'revenueThisMonth' => $revenueThisMonth,
    //         'thisMonthName' => $thisMonthName,
    //         'lastThisMonth' => $lastThisMonth,
    //         'lastMonthName' => $lastMonthName,
    //         'revenueLastThirtyDays' => $revenueLastThirtyDays
    //     ]);
    // }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        return redirect()->route('admin.login');

        // $request->session()->forget();
        // $request->session()->flush();
    }


}
