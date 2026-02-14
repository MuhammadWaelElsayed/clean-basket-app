<style>
    @media only screen and (max-width: 600px) {
        .main-img > img{
            width: 70%
        }
        .inner-div{
            padding: 2% 0% !important;
        }
    }
    
    </style>
        <div class="main-div" style="background-color: #F1F2F2; color: #3d4852; padding: 5% 5%; font-family: 'Google Sans' !important;">
            <p class="fesilah-logo" style="text-align: center;"> 
                <img src="{{ asset('media/logo.png') }}" width="150px" alt="logo">
            </p>
            <div class="inner-div" style="background-color: white; padding: 2% 3%; border: 1px solid rgb(230, 230, 230); text-align: center;">
                <p class="main-img" style="text-align: center;">
                    <img src="{{ asset('media/forgot-email.png') }}" width="250vw" alt="lock-img">
                </p>
                <hr>
                <h2 style="color: black;">FORGOT YOUR PASSWORD?</h2>
                <p style="font-size:18px;color: black; margin:5px">Hey, {{$data['name']}} </p>
                <p style="font-size:18px;color: black; margin:5px">We'll have you back online in no time. </p>
                <p style="font-size:18px;color: black; margin:5px">Please Use the OTP code below to reset your password.</p> 
                <p style="font-size:18px;color: black; margin:5px">OTP Code</p> 
                
                    <p style="color:white;font-size:24px; font-weight:bold; background:#3b3b3b; padding:5px 20px; border-radius:15px; width:200px; margin:0px auto;">
                   {{$data['otp']}} </p> 
                   
                <p style="font-size:18px;color: black">If you did'nt request this, you can ignore this email. </p> 
              
            </div>
            @include('mails.elements.footer')
            
        </div>
    
    
        