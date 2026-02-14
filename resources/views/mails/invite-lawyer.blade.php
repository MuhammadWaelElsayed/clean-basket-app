<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{env('APP_NAME')}}</title> 
</head>
<body>


    <div class="main-div" style="background-color: #F1F2F2; color: #3d4852; padding: 5% 10%; font-family: 'Google Sans' !important;">
        <p class="fesilah-logo" style="text-align: center;"> 
            <img src="{{ asset('media/logo.png') }}" width="150px" alt="logo">
        </p>
        <div class="inner-div" style="background-color: white; padding: 2% 5%; border: 1px solid rgb(230, 230, 230); text-align: center;">
            <p style="font-size:18px; margin:5px">Hey! </p>
            <h2 tyle="color: black" >You're invited to join {{$data['company']['name']}}! <br>
                 To get started, please complete your account information by clicking the link below:
                </h2> <br>
            {{-- <b style="font-size:18px; margin:5px"> Name: {{$data['company']['name']}} </b> --}}
            <br>
            <a href="{{url('/become-partner?invite='.$data['invite']['id'])}}" style="color:white;font-size:22px; font-weight:bold;padding:5px 20px; background:#1f1f1f;  border-radius:6px; width:200px; margin:0px auto;">
                Setup Account
            </a>
           

            @include('mails.elements.footer')

        </div>
    </div>
    
</body>
</html>