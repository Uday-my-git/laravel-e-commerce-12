<!DOCTYPE html>
<html>
<head>
   <title>Laravel 10 - Stripe Payment Gateway Integration Example - ItSolutionStuff.com</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />

   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>

<body>   
   <div class="container">
      <h1>Laravel 10 - Stripe Payment Gateway Integration Example <br/> ItSolutionStuff.com</h1>

      <div class="row">
         <div class="col-md-6 col-md-offset-3">
               <div class="panel panel-default credit-card-box">
                  <div class="panel-heading display-table" >
                     <h2 class="panel-title" >Checkout Forms</h2>
                  </div>
                  <div class="panel-body">
                     <form action="{{ route('stripe') }}" id='checkout-form' method='post'>   
                        @csrf    
                        
                        <h3>Price: $2000 </h3>
                        <input type='hidden' name='price' value="2000">   
                        <input type='hidden' name='product_name' value='Air Begs Leptop'>   
                        <input type='hidden' name='quantity' value='2'>   

                        <br>

                        <button type="submit" class="btn btn-success mt-3" style="margin-top: 20px; width: 100%;padding: 7px;"> 
                           PAY $2000
                        </button>
                     <form>
                  </div>
               </div>        
         </div>
      </div>
   </div>
</body>