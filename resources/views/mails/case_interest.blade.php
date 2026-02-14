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
            <p style="font-size:18px; margin:5px">Hey, {{$data['user']['name']}} </p>
            <span style="background:#1f1f1f; padding:2px 10px; border-radius:10px;color:white; font-size:18px;">{{$data['title']}}</span>
            <h2 style="color: black">{{$data['message']}} </h2>

            <p style="font-size:18px; margin:5px"><b> Name :</b>  {{$data['vendor']['name']}} </p>
            <p style="font-size:18px; margin:5px"><b> Email:</b>  {{$data['vendor']['email']}} </p>
            @if ($data['vendor']['show_phone']==1)
                <p style="font-size:18px; margin:5px"><b> Phone:</b>  {{$data['vendor']['phone']}} </p>
            @endif
            <p style="font-size:18px; margin:5px">Let's have chat with vendor to get the deal done.</p>
            
           @include('mails.elements.footer')
        </div>
    </div>
    
</body>
</html>