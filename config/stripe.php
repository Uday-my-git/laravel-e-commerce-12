<?php 

   return [
      'stripe_key'    => env('STRIPE_KEY'),
      'secret_secret' => env('STRIPE_SECRET'),
      'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
   ];


?>