@section('title','Store Login')
    
<div class="d-flex flex-column flex-root" id="kt_app_root" style="background-image: url({{ asset('media/bg5-dark.webp') }})">
    <a href="{{url('/')}}" class="mt-15 mb-5 mx-auto">  
        <img alt="Logo" src="{{ asset('media/logo.png') }}" class="logo-default w-150px w-lg-120px">
    </a>
    <div class="mx-auto">
        <!--begin::Wrapper-->
        <div class="w-lg-500px p-10 mx-auto bg-white shadow">
            <!--begin::Form--> 
            <form class="form w-100"  method="POST" wire:submit.prevent="submitLogin()">
                <!--begin::Heading-->
                @csrf
                <div class="text-center mb-11">
                    <!--begin::Title-->
                    <h1 class="text-dark fw-bolder mb-3">Store Login</h1>
                    <!--begin::Subtitle-->
                    <div class="text-gray-500 fw-semibold fs-6">Welcome! Login to your account</div>
                    <!--end::Subtitle=-->
                </div>
                <!--begin::Input group=-->
                <div class="fv-row mb-8">
                    <!--begin::Email-->
                    <label>Email</label>
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
                <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold my-3">
                    <!--begin::Link--> 
                    <a href="{{ route('partner.forgot-password') }}" class="text-base">Forgot Password ?</a>
                    <!--end::Link-->
                </div>
                <!--end::Wrapper-->
                <!--begin::Submit button-->
                <div class="d-grid mb-10">
                    <button type="submit"  class="btn btn-base bg-gradient-right">
                        <!--begin::Indicator label-->
                        <span class="indicator-label">Sign In</span>
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
  
 
</div>


