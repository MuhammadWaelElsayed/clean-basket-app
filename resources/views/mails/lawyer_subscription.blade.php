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
            <b style="font-size:18px; margin:5px"> {{$data['package']['package']['name']}} </b>
            <p style="font-size:18px; margin:5px"><b> Amount Paid:</b> {{env('CURRENCY')}} {{$data['package']['package']['price']}} </p>
            <p style="font-size:18px; margin:5px"><b> Purchase Date:</b> {{date('d M, Y',strtotime($data['package']['buy_at']))}} </p>
            <p style="font-size:18px; margin:5px"><b> Expiry Date:</b> {{date('d M, Y',strtotime($data['package']['expired_at']))}} </p>
            <p style="font-size:18px; margin:5px">Let's keep facilitate and enjoying our services.</p>
            <br>
           @include('mails.elements.footer')

        </div>
    </div>
    
</body>
</html>