
@section('title','Forgot Password')
    
<div class="d-flex flex-column flex-root" id="kt_app_root" style="background-image: url({{ asset('media/bg5-dark.webp') }})">
    <a href="https://master.devicebee.com/babynbeyond/home" class="mt-15 mb-5 mx-auto">  
        <img alt="Logo" src="{{ asset('media/logo.png') }}" class="logo-default w-100px w-lg-120px">
    </a>

    @if ($step==1)
        <div class="mx-auto">
            <!--begin::Wrapper-->
            <div class="w-lg-500px p-10 mx-auto bg-white shadow">
                <!--begin::Form--> 
                <form class="form w-100"  method="POST" wire:submit.prevent="submitForgot()">
                    <!--begin::Heading-->
                    @csrf
                    <div class="text-center mb-11">
                        <!--begin::Title-->
                        <h1 class="text-dark fw-bolder mb-3">Forgot your passowrd?</h1>
                        <!--begin::Subtitle-->
                        <div class="text-gray-500 fw-semibold fs-6">Don't worry just enter your registered email to get reset it.</div>
                        <!--end::Subtitle=-->
                    </div>
                    <!--begin::Input group=-->
                    <div class="fv-row mb-8">
                        <!--begin::Email-->
                        <label>Email</label>
                        <input type="text" placeholder="Email" wire:model="email" autocomplete="off" class="form-control bg-transparent" />
                        @error('email') <span class="text-danger">{{$message}}</span> @enderror
                    </div>
                
                    <!--end::Wrapper-->
                    <!--begin::Submit button-->
                    <div class="d-grid mb-10">
                        <button type="submit"  class="btn btn-base bg-gradient-right">
                            <!--begin::Indicator label-->
                            <span class="indicator-label">Forgot Password</span>
                            <!--begin::Indicator progress-->
                            <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            <!--end::Indicator progress-->
                        </button>
                    </div>
                    <!--end::Submit button-->
                </form>
                <div class="d-flex flex-center flex-wrap px-5">
                    <!--begin::Links-->
                    <div class="d-flex fw-semibold text-base fs-base">
                        <a href="{{ url('/about') }}" class="px-5 text-base" target="_blank">Copyright By {{env('APP_NAME')}} {{'@'.date('Y')}}</a>
                    </div>
                    <!--end::Links-->
                </div>
                <!--end::Form-->
            </div>
            <!--end::Wrapper-->
        </div>
    @elseif($step==2)
        <div class="mx-auto">
            <!--begin::Wrapper-->
            <div class="w-lg-500px p-10 mx-auto bg-white shadow">
                <!--begin::Form--> 
                <form class="form w-100"  method="POST" wire:submit.prevent="submitReset()">
                    <!--begin::Heading-->
                    @csrf
                    <div class="text-center mb-11">
                        <!--begin::Title-->
                        <h1 class="text-dark fw-bolder mb-3">Reset your passowrd</h1>
                        <!--begin::Subtitle-->
                        <div class="text-gray-500 fw-semibold fs-6">Enter OTP which you have recieved on your email and enter new password.</div>
                        <!--end::Subtitle=-->
                    </div>
                    <!--begin::Input group=-->
                    <div class="fv-row mb-5">
                        <!--begin::Email-->
                        <input type="hidden" placeholder="Email" wire:model="email" autocomplete="off" />
                        <label>OTP</label>
                        <input type="number"  placeholder="OTP" wire:model="otp" autocomplete="off" class="form-control bg-transparent" />
                        @error('otp') <span class="text-danger">{{$message}}</span> @enderror
                    </div>
                    <div class="fv-row mb-5">
                        <!--begin::Email-->
                        <label>New Password</label>
                        <input type="text"  placeholder="New Password" wire:model="new_password" autocomplete="off" class="form-control bg-transparent" />
                        @error('new_password') <span class="text-danger">{{$message}}</span> @enderror
                    </div>
                    <div class="fv-row mb-5">
                        <!--begin::Email-->
                        <label>Confirm Password</label>
                        <input type="text"  placeholder="Confirm Password" wire:model="confirm_password" autocomplete="off" class="form-control bg-transparent" />
                        @error('confirm_password') <span class="text-danger">{{$message}}</span> @enderror
                    </div>
                    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold my-3">
                        <!--begin::Link--> 
                        <a href="#" wire:click="submitForgot()" class="text-base">Resend OTP?</a>
                        <!--end::Link-->
                    </div>
                    <!--begin::Submit button-->
                    <div class="d-grid mb-10">
                        <button type="submit"  class="btn btn-base bg-gradient-right">
                            <!--begin::Indicator label-->
                            <span class="indicator-label">Reset Password</span>
                            <!--begin::Indicator progress-->
                            <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            <!--end::Indicator progress-->
                        </button>
                    </div>
                    <!--end::Submit button-->
                </form>
                <div class="d-flex flex-center flex-wrap px-5">
                    <!--begin::Links-->
                    <div class="d-flex fw-semibold text-base fs-base">
                        <a href="{{ url('/about') }}" class="px-5 text-base" target="_blank">Copyright By {{env('APP_NAME')}} {{'@'.date('Y')}}</a>
                    </div>
                    <!--end::Links-->
                </div>
                <!--end::Form-->
            </div>
            <!--end::Wrapper-->
        </div>
    @endif
    
  
 
</div>


