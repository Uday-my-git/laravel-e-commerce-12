<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Reset Password Email</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f6f8; padding:40px 0;">
   <tr>
      <td align="center">
         <!-- Main Container -->
         <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
            <tr>
               <td style="padding:30px; text-align:center; border-bottom:1px solid #eeeeee;">
                  <h1 style="margin:0; font-size:28px; letter-spacing:2px;">
                     E-<span style="color:#f4a261;">Commerce</span>
                  </h1>
               </td>
            </tr>
            <tr>
               <td align="center" style="padding:30px;">
                  <a target="_blank" href="https://viewstripo.email" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:underline;color:#2CB543;font-size:14px">
                     <img class="adapt-img" src="https://tlr.stripocdn.email/content/guids/CABINET_2af5bc24a97b758207855506115773ae/images/80731620309017883.png" alt="Eid al-Adha" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" title="Eid al-Adha" height="373" width="560">
                  </a>
               </td>
            </tr>
            <tr>
               <td style="padding:0 40px 30px 40px; color:#333333;">
                  <h2 style="margin-top:0; font-size:24px;">
                     Forgot your password?
                  </h2>

                  <p style="font-size:15px; line-height:1.6;">
                     Hello {{ $mailData['user']->name }},
                  </p>

                  <p style="font-size:15px; line-height:1.6;">
                     We’ve received a request to reset the password for the Ecommerce account associated.
                     No changes have been made to your account yet.
                  </p>

                  <p style="font-size:15px; line-height:1.6;">
                     You can reset your password by clicking the button below. This button navigate the your set new password.
                  </p>

                  <!-- Button -->
                  <table cellpadding="0" cellspacing="0" width="100%" style="margin:30px 0;">
                     <tr>
                        <td align="center">
                           <a href="{{ route('front-end.resetPassword', $mailData['token']) }}" style="background-color:#f4a261; color:#ffffff; text-decoration:none;
                              padding:14px 28px; border-radius:6px; font-size:16px; font-weight:bold; display:inline-block;">
                              RESET YOUR PASSWORD
                           </a>
                        </td>
                     </tr>
                  </table>

                  <hr style="border:none; border-top:1px solid #eeeeee; margin:30px 0;">

                  <p style="font-size:14px; color:#555555; line-height:1.6;">
                     Just so you know: You have <strong>24 hours</strong> to pick your password.
                     After that, you'll have to ask for a new one.
                  </p>

                  <p style="font-size:14px; color:#555555; line-height:1.6;">
                     Didn’t ask for a new password? You can ignore this email.
                  </p>

                  <p style="font-size:14px; color:#555555;">
                     — The Blog team
                  </p>
               </td>
            </tr>
         </table>
      </td>
   </tr>
</table>

</body>
</html>
