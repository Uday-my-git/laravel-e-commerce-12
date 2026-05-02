<?php  

    use App\Models\Category;
    use App\Models\ProductImage;
    use App\Models\Page;
    use App\Models\Order;
    use App\Models\Payment;
    use App\Mail\OrderEmail;
    use Illuminate\Support\Facades\Log;

    function getCategoryFun()
    {
        return Category::orderBy('name', 'asc')
            ->with('sub_category_fun')
            ->orderBy('id', 'desc')
            ->where('status', 1)
            ->where('showHome', 'Yes')
            ->get();
    }

    function getProductImageFun($product_id)
    {
        // Alternative to retrieving the first model matching the query constraints...
        return ProductImage::firstWhere('product_id', $product_id);
    }


    // Send order email for userType
    function orderEmail($orderId, $userType)
    {
        Log::info('Admin Side - OrderEmail send');

        $order = Order::where('id', $orderId)->with('items')->first();
        $payment = Payment::where('order_id', $orderId)->first();

        if (!$order) {
            return false; // No order found
        }

        if ($userType === 'customer') {
            $subject = 'Thanks for your orders';
            $email = $order->email;
        } else {
            $subject = 'You have received an orders!!';
            $email   = config('mail.admin_email');
        }
        
        $mailData = [
            'subject' => $subject,
            'order'   => $order,
            'payment' => $payment,
            'userType' => $userType,
        ];
        Log::info('mailData', $mailData);

        try {
            Mail::to($email)->send(new OrderEmail($mailData));
            
            Log::info('Order email sent successfully', [
                'order_id' => $order->id,
                'email'    => $email,
                'userType' => $userType,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order email', [
                'order_id' => $order->id,
                'email'    => $email,
                'error'    => $e->getMessage(),
            ]);  
        }
    }

    function staticPagesFun()
    {
        return $pages = Page::orderBy('name', 'asc')->get();
    }
 

?>