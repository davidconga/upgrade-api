<!DOCTYPE html>
 <html>
    <body>
        <div style="font-size:16px;">
        <p>Hi {{$username}},</p>
        <p>Welcome to {{$settings->site->site_title}}. Only one more step away. Verify your account.</p> 
        <p>Your activation code
        <span style="font-size:16px;"><b><?php echo $body ?></b></span></p>
        <p>Use this code to verify your account</p>
        <small>You have received this email as you are registered with {{$settings->site->site_title}}.</small>
        </div>
    </body>
 </html>
