<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <title>Contact Email</title>
</head>
<body>
   
   <h1>You have received contact email</h1>

   <p>Name: {{ $mailData['name'] }}</p>
   <p>Email: {{ $mailData['email'] }}</p>
   <p>Subject: {{ $mailData['subject'] }}</p>

   <strong>Messages:-</strong>
   <p>{{ $mailData['mail_subject'] }}</p>

</body>
</html>