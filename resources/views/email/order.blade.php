<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <title>Oeder Email</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;">

<div style="max-width:700px; margin:auto; font-family: Arial, sans-serif; font-size:14px; color:#333;">

   @if ($mailData['userType'] == 'customer')
      <h1>Thanks for your orders!!</h1>
      <h2>Our Order Id:- #{{ $mailData['order']->id }}</h2>
   @else
      <h1>Admin, You have received an orders!!</h1>
      <h2>Order Id:- #{{ $mailData['order']->id }}</h2>
   @endif

   <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
      <tr>
         <td style="vertical-align: top; width:50%; padding-right:10px;">
            <h2 style="margin:0 0 10px; font-size:16px; color:#222;">Shipping Address</h2>
            
            <p style="margin:0; line-height:1.6;">
               <strong>{{ $mailData['order']->first_name }} {{ $mailData['order']->last_name }}</strong><br>

               {{ $mailData['order']->address }}<br>
               {{ $mailData['order']->city }}, {{ $mailData['order']->state }}, {{ $mailData['order']->countriesName }}<br>

               <strong>Zip:</strong> {{ $mailData['order']->zip }}<br>
               <strong>Phone:</strong> {{ $mailData['order']->mobile }}<br>
               <strong>Email:</strong> {{ $mailData['order']->email }}<br><br>

               <strong>Shipped Date:</strong> 

               @if (!empty($mailData['order']->shipped_date))
                  {{ \Carbon\Carbon::parse($mailData['order']->shipped_date)->format('d M, Y') }}
               @else
                  n/a
               @endif
            </p>
         </td>

         <td style="vertical-align: top; width:50%; padding-left:10px;">
            <h2 style="margin:0 0 10px; font-size:16px; color:#222;">Order Summary</h2>

            <p style="margin:0; line-height:1.6;">
               <strong>Invoice #:</strong> {{ $mailData['order']->id }}<br>
               <strong>Order ID:</strong> {{ $mailData['order']->id }}<br>
               <strong>Total:</strong> R.s {{ number_format($mailData['order']->grand_total, 2) }}<br>

               @if ($mailData['order']->payment_status == 'paid')
                  <strong>Payment Status:</strong> Paid ({{ $mailData['payment']->payment_gateway }}) <br>
               @else
                  <strong>Payment Status:</strong> COD <br>
               @endif

               <strong>Transaction ID</strong> {{ $mailData['payment']->transaction_id ?? 'n/a' }} <br>

               <strong>Status:</strong>
               
               @if ($mailData['order']->status == 'pending')
                  <span style="color:#d9534f;">Pending</span>
               @elseif ($mailData['order']->status == 'shipped')
                  <span style="color:#17a2b8;">Shipped</span>
               @elseif ($mailData['order']->status == 'deliverd')
                  <span style="color:#28a745;">Delivered</span>
               @elseif ($mailData['order']->status == 'confirmed')
                  <span style="color:#28a745;">Confirmed</span>
               @else
                  <span style="color:#d9534f;">Cancelled</span>
               @endif
            </p>
         </td>
      </tr>
   </table>

   <!-- Products Table -->
   <table width="100%" cellpadding="8" cellspacing="0" border="0" style="border-collapse: collapse; border:1px solid #ddd;">
      <thead>
         <tr style="background:#f7f7f7; border-bottom:1px solid #ddd;">
            <th align="left" style="border-right:1px solid #ddd;">Product</th>
            <th align="right" style="border-right:1px solid #ddd;">Price</th>
            <th align="center" style="border-right:1px solid #ddd;">Qty</th>
            <th align="right">Total</th>
         </tr>
      </thead>
      <tbody>

         @forelse ($mailData['order']->items as $orderItem)
            <tr>
               <td style="border-top:1px solid #eee;">{{ $orderItem->name }}</td>
               <td align="right" style="border-top:1px solid #eee;">{{ number_format($orderItem->price, 2) }}</td>
               <td align="center" style="border-top:1px solid #eee;">{{ $orderItem->qty }}</td>
               <td align="right" style="border-top:1px solid #eee;">{{ number_format($orderItem->total, 2) }}</td>
            </tr>
         @empty
            <tr><td colspan="4" align="center" style="color:#555; padding:10px;">Order Items Not Found</td></tr>
         @endforelse
      </tbody>
      
      <tfoot>
         <tr style="background:#f9f9f9;">
            <th colspan="3" align="right" style="padding:8px; border-top:1px solid #ddd;">Subtotal:</th>
            <td align="right" style="padding:8px; border-top:1px solid #ddd;">R.s {{ number_format($mailData['order']->subtotal, 2) }}</td>
         </tr>
         <tr style="background:#f9f9f9;">
            <th colspan="3" align="right" style="padding:8px;">Shipping:</th>
            <td align="right" style="padding:8px;">R.s {{ number_format($mailData['order']->shipping, 2) }}</td>
         </tr>
         <tr style="background:#f9f9f9;">
            <th colspan="3" align="right" style="padding:8px;">Discount {{ (!empty($mailData['order']->coupon_code)) ? '('.$mailData['order']->coupon_code.')' : '' }}:</th>
            <td align="right" style="padding:8px;">R.s {{ number_format($mailData['order']->discount, 2) }}</td>
         </tr>
         <tr style="background:#e9f7ef; font-weight:bold;">
            <th colspan="3" align="right" style="padding:10px; border-top:1px solid #ddd;">Grand Total:</th>
            <td align="right" style="padding:10px; border-top:1px solid #ddd;">R.s {{ number_format($mailData['order']->grand_total, 2) }}</td>
         </tr>
      </tfoot>
   </table>

   <p style="margin-top:20px; font-size:13px; color:#666; text-align:center;">
      Thank you for shopping with us!<br>
      For any questions, contact our support team.
   </p>

</div>

</body>
</html>