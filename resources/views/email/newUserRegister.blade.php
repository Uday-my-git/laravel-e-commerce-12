<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Welcome to Our Community</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f6f8; padding:40px 0;">
   <tr>
      <td align="center">
         <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <tr>
               <td style="padding:30px; text-align:center; border-bottom:1px solid #eeeeee;">
                  <h1 style="margin:0; font-size:28px; letter-spacing:2px; color:#333333;">
                     E-<span style="color:#f4a261;">Commerce</span>
                  </h1>
               </td>
            </tr>
            
           <tr>
               <td align="center" style="padding: 40px 20px; background-color: #fff8f3;">
                  <div style="width: 80px; height: 80px; background-color: #f4a261; border-radius: 50%; display: inline-block; margin-bottom: 20px;">
                     <span style="font-size: 40px; line-height: 80px; color: #ffffff;">👋</span>
                  </div>
                  
                  <h2 style="margin: 0; font-family: 'Helvetica', Arial, sans-serif; font-size: 32px; color: #333333; text-transform: uppercase; letter-spacing: 2px;">
                     Welcome <span style="color: #f4a261;">Home</span>
                  </h2>
                  
                  <p style="margin: 10px 0 0 0; font-size: 16px; color: #777777; font-style: italic;">
                     We're so glad you've joined our community.
                  </p>
                  
                  <div style="height: 3px; width: 50px; background-color: #f4a261; margin: 20px auto;"></div>
               </td>
            </tr>

            <tr>
               <td style="padding:0 40px 30px 40px; color:#333333;">
                  <h2 style="margin-top:0; font-size:24px; color:#2d3436;">
                     Welcome to the family! 🚀
                  </h2>

                  <p style="font-size:16px; line-height:1.6;">
                     Hello <strong>{{ $mailData['user']->name }}</strong>,
                  </p>

                  <p style="font-size:15px; line-height:1.6; color:#555555;">
                     We're thrilled to have you here. Your account has been successfully created, and you’re now part of a community that values quality and style.
                  </p>
                  <hr style="border:none; border-top:1px solid #eeeeee; margin:30px 0;">

                  <p>Please wait for Admin Approval to <b>Active</b> your account. Your account will be activated within 24 hours.</p>

                  <hr style="border:none; border-top:1px solid #eeeeee; margin:30px 0;">

                  <p style="font-size:14px; color:#777777; line-height:1.6;">
                     <strong>Pro Tip:</strong> Complete your profile to get personalized recommendations and exclusive member-only discounts.
                  </p>

                  <p style="font-size:14px; color:#333333; margin-top:30px;">
                     Cheers,<br>
                     <strong>The Team</strong>
                  </p>
               </td>
            </tr>

            <tr>
               <td style="padding:20px; text-align:center; background-color:#fafafa; color:#999999; font-size:12px;">
                  &copy; 2026 E-Commerce Inc. All rights reserved. <br>
                  If you didn't create this account, please ignore this email.
               </td>
            </tr>
         </table>
      </td>
   </tr>
</table>

</body>
</html>