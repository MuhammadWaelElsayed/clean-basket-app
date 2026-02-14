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
            <b style="font-size:18px; margin:5px"> Name: {{$data['vendor']['name']}} </b>
            <p style="font-size:18px; margin:5px"><b>  Email:</b>  {{$data['vendor']['email']}} </p>
            <p style="font-size:18px; margin:5px"><b> Phone:</b>  {{$data['vendor']['phone']}} </p>
            <p style="font-size:18px; margin:5px"><b> Country:</b>  {{$data['vendor']['country']}} </p>
            {{-- <p style="font-size:18px; margin:5px"><b> Min Case Value:</b>  {{$data['vendor']['min_case_value']}} </p> --}}
            <p style="font-size:18px; margin:5px"><b> Cases Won:</b>  {{$data['vendor']['cases_won']}} </p>
            <p style="font-size:18px; margin:5px"><b> About:</b>  {{$data['vendor']['about']}} </p>

            <p style="font-size:18px; margin:5px">Check your admin panel to approve this partner</p>
            <br>
            <p style="font-size:20px; margin:5px">Certificate:</p>
            <a href="{{$data['vendor']['certificate']}}" target="_blaknk"
                style="color:white;font-size:18px; font-weight:bold;padding:5px 20px; background:#1f1f1f;  border-radius:15px; width:200px; margin:5px;">
                View File
            </a>
            <p style="font-size:20px; margin:5px">License:</p>
            <a href="{{$data['vendor']['license']}}" target="_blaknk"
                style="color:white;font-size:18px; font-weight:bold;padding:5px 20px; background:#1f1f1f;  border-radius:15px; width:200px; margin:5px;">
                View File
            </a>

            @include('mails.elements.footer')

        </div>
    </div>
    
</body>
</html>