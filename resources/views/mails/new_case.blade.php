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
            <img src="{{ asset('media/dark-logo.png') }}" width="150px" alt="logo">
        </p>
        <div class="inner-div" style="background-color: white; padding: 2% 5%; border: 1px solid rgb(230, 230, 230); text-align: center;">
            <p style="font-size:18px; margin:5px">Hey, {{$data['user']['name']}} </p>
            <span style="background:#1f1f1f; padding:2px 10px; border-radius:10px;color:white; font-size:18px;">Case# {{$data['case']['id']}}</span>
            <h2 style="color: black">{{$data['title']}} </h2>
            <h2 style="color: black">{{$data['case']['type_legal_consultant']}} </h2>
            <b style="font-size:18px; margin:5px"> {{$data['case']['description']}} </b>
            <p style="font-size:18px; margin:5px"><b> Claim Value:</b> {{env('CURRENCY')}} {{$data['case']['case_value'] ?? 0}} </p>
            {{-- <p style="font-size:18px; margin:5px"><b> Case Budget:</b> {{env('CURRENCY')}} {{$data['case']['case_budget']}} </p> --}}
            <p style="font-size:18px; margin:5px">Let's reach the user case and show your interest to get the case deal </p>
            <br>
            @if ($data['case']['attachments']!=null)
                <p style="font-size:20px; margin:5px">Attachments:</p>
                @foreach ($data['case']['attachments'] as $key => $item)
                <a href="{{$item}}" target="_blaknk"
                 style="color:white;font-size:18px; font-weight:bold;padding:5px 20px; background:#1f1f1f;  border-radius:15px; width:200px; margin:5px;">
                    Attachment {{$key+1}} </a>
                @endforeach
            @endif

            @include('mails.elements.footer')

        </div>
    </div>
    
</body>
</html>