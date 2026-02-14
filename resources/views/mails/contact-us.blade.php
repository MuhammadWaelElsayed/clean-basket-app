<style>
    @font-face {
      font-family: NeulisAlt;
      src: url(http://master.devicebee.com/saveMe/font/NeulisAlt-Regular.woff2);}
    </style>
        <div class="main-div" style="background-color: #F1F2F2; color: #3d4852; padding: 5% 10%; font-family: 'Google Sans';">
            <p class="fesilah-logo" style="text-align: center;"> 
                <img src="{{ asset('media/logo.png') }}" width="150px" alt="logo">
            </p>
            <div class="inner-div" style="background-color: white; padding: 2% 5%; border: 1px solid rgb(230, 230, 230); text-align: center;">
                
                <h2>Hello! A New User is contacting you.</h2>
                <table class="fesilah-table" style="width: 100%; text-align: left; padding: 10px; background: #f5f5f5;" width="100%" align="left">
                    <tr style="width: 100%; text-align: left; padding: 10px; background: #f5f5f5;" align="left">
                        <th>User Name:</th>
                        <td> {{$data['name']}}</td>
                    </tr>
                    <tr style="width: 100%; text-align: left; padding: 10px; background: #f5f5f5;" align="left">
                        <th>Email Address:</th>
                        <td> {{$data['email']}}</td>
                    </tr>
                    <tr style="width: 100%; text-align: left; padding: 10px; background: #f5f5f5;" align="left">
                        <th>Phone Number:</th>
                        <td> {{$data['phone']}}</td>
                    </tr>
                    <tr style="width: 100%; text-align: left; padding: 10px; background: #f5f5f5;" align="left">
                        <th>Message:</th>
                        <td>{{$data['message']}}</td>
                    </tr>
                </table> <br>
               
                @include('mails.elements.footer')
                
            </div>

        </div>
    
    
        