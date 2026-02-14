<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title> 
</head>
<body>


    <div class="main-div" style="background-color: #F1F2F2; color: #3d4852; padding: 5% 10%; font-family: 'Google Sans' !important;">
        <p class="fesilah-logo" style="text-align: center;"> 
            <img src="{{ asset('media/logo.png') }}" width="150px" alt="logo">
        </p>
        <div class="inner-div" style="background-color: white; padding: 2% 5%; border: 1px solid rgb(230, 230, 230); text-align: center;">
           
            <h2 style="color: black">VERIFY OTP</h2>
            <p style="font-size:18px; margin:5px">Hey, {{$data['name']}} </p>
            <p style="font-size:18px; margin:5px">Thank you for joining us. We're really happy to welcome you to the {{env('APP_NAME')}} family. </p>
            <p style="font-size:18px; margin:5px">Let's start our journey together to get legal solutions online. </p>

            <p style="font-size:20px; margin:5px">OTP Code</p>
            <p style="color:white;font-size:22px; font-weight:bold;padding:5px 20px; background:#1f1f1f;  border-radius:15px; width:200px; margin:0px auto;">
                {{$data['otp']}}  </p>

            @include('mails.elements.footer')

        </div>
    </div>
    
</body>
</html>