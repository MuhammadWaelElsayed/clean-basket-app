@section('title','Admin Login')
    
<div class="d-flex flex-column flex-root" id="kt_app_root">
    <!--begin::Authentication - Sign-in -->
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <!--begin::Body-->
        <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
            <!--begin::Form-->
            <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                <!--begin::Wrapper-->
                <div class="w-lg-500px p-10">
                    <!--begin::Form--> 
                    <form class="form w-100"  method="POST" wire:submit.prevent="submitLogin()">
                        <!--begin::Heading-->
                        @csrf
                        <div class="text-center mb-11">
                            <!--begin::Title-->
                            <h1 class="text-dark fw-bolder mb-3">Admin Login</h1>
                            <!--begin::Subtitle-->
                            <div class="text-gray-500 fw-semibold fs-6">Welcome! Login to your account</div>
                            <!--end::Subtitle=-->
                        </div>
                        <!--begin::Input group=-->
                        <div class="fv-row mb-8">
                            <!--begin::Email-->
                            <label>Email</label>
                            {{-- <input type="hidden" id="web_token" wire:ignore.self wire:model="web_token" /> --}}

                            <input type="text" placeholder="Email" wire:model="email" autocomplete="off" class="form-control bg-transparent" />
                            @error('email') <span class="text-danger">{{$message}}</span> @enderror
                        </div>
                        <!--end::Input group=-->
                        <div class="fv-row mb-3">
                            <!--begin::Password-->
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group" id="show_hide_password">
                                  <input class="form-control bg-transparent"  type="{{($show_pass==false)?'password':'text'}}" placeholder="Password" wire:model="password" autocomplete="off" >
                                  <div class="input-group-addon bg-gray-100 p-4">
                                    <a type="button" wire:click="showPass()"><i class="fa fa-{{($show_pass==false)?'eye-slash':'eye'}}" aria-hidden="true"></i></a>
                                  </div>
                                </div>
                                @error('password') <span class="text-danger">{{$message}}</span> @enderror

                              </div>
                            {{-- <input type="password" placeholder="Password" name="password" autocomplete="off" class="form-control bg-transparent" /> --}}
                            <!--end::Password-->
                        </div>
                        <!--end::Input group=-->
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                            <div></div>
                            <!--begin::Link-->
                            {{-- <a href="" class="text-base">Forgot Password ?</a> --}}
                            <!--end::Link-->
                        </div>
                        <!--end::Wrapper-->
                        <!--begin::Submit button-->
                        <div class="d-grid mb-10">
                            <button type="submit"  class="btn btn-base bg-gradient-right">
                                <!--begin::Indicator label-->
                                <span class="indicator-label" wire:loading.remove>Sign In</span>
                                <span class="indicator-progress" wire:loading>Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                        <!--end::Submit button-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Form-->
            <!--begin::Footer-->
            <div class="d-flex flex-center flex-wrap px-5">
                <!--begin::Links-->
                <div class="d-flex fw-semibold text-base fs-base">
                    <a href="{{ url('/about') }}" class="px-5 text-base" target="_blank">Copyright By {{env('APP_NAME')}} {{'@'.date('Y')}}</a>
                </div>
                <!--end::Links-->
            </div>
            <!--end::Footer-->
        </div>
        <!--end::Body--> 
        <!--begin::Aside-->
        <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-right order-1 order-lg-2" style="background-image: url({{ asset('media/bg5-dark.webp') }})">
            <!--begin::Content-->
            <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
                <!--begin::Logo--> 
                <a href="{{ url('/home') }}" class="mb-0 mb-lg-12">
                    <img alt="Logo" src="{{ asset('media/logo.png') }}" class="h-100px h-lg-200px" />
                    {{-- <h1 class="text-center fs-lg-1 my-3">Fesilah</h1> --}}
                </a>
            
            </div>
            <!--end::Content-->
        </div>
        <!--end::Aside-->
    </div>
    <!--end::Authentication - Sign-in-->
 
     <!--FCM Scripts -->
     <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
     <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>
     
     <script>
         // Your Firebase configuration
         const firebaseConfig = {
             apiKey: "AIzaSyA3Gzei323NkC3SlIyOMtxttMbz9AgpH-0",
             authDomain: "legal-platform-6e119.firebaseapp.com",
             projectId: "legal-platform-6e119",
             storageBucket: "legal-platform-6e119.appspot.com",
             messagingSenderId: "1056379556496",
             appId: "1:1056379556496:web:4a4a221e24486c98357e15",
             measurementId: "G-231C8BQRL3"
         };
         // Initialize Firebase
         firebase.initializeApp(firebaseConfig);
         // Get FCM token
         const messaging = firebase.messaging();
             messaging
             .requestPermission()
             .then(function () {
                 return messaging.getToken()
             })
             .then(function(token) {
                 console.log(token);
                @this.set('web_token', token);
             })
             .catch(function (err) {
                 console.log('Firebase Token Error'+ err);
             });
     </script>


</div>

   
    


