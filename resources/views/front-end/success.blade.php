<!DOCTYPE html>
<html>
<head>
   <title>Payment Successful</title>
   <style>
      body { font-family: Arial; background: #f4f6f8; }
      .box {
         max-width: 500px;
         margin: 100px auto;
         background: #fff;
         padding: 30px;
         border-radius: 10px;
         text-align: center;
         box-shadow: 0 0 15px rgba(0,0,0,.1);
      }
      .success { color: #16a34a; font-size: 22px; }
   </style>
</head>
<body>
<div class="box">
    <div class="success">✅ Payment Successful</div>

    <p>Thank you for your order!</p>

    <hr>

    <p><b>Session ID:</b> {{ $session->id }}</p>
    <p><b>Amount:</b> {{ number_format($session->amount_total / 100, 2) }} {{ strtoupper($session->currency) }}</p>
    <p><b>Status:</b> {{ $session->payment_status }}</p>

    <br>
    <a href="{{ route('front-end.cart') }}">Continue Shopping</a>
</div>
</body>
</html>



