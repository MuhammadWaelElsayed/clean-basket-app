<div class="d-flex flex-column flex-root" style="min-height: 100vh; justify-content: center; align-items: center;">
    <div class="w-lg-500px p-10 p-lg-15 mx-auto bg-white rounded shadow-sm">
        <form wire:submit.prevent="submitLogin" class="form w-100">
            <div class="text-center mb-10">
                <h1 class="text-dark mb-3">Support Login</h1>
            </div>
            @if(session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="fv-row mb-10">
                <label class="form-label fs-6 fw-bolder text-dark">Email</label>
                <input wire:model.defer="email" class="form-control form-control-lg form-control-solid" type="email" autocomplete="off" />
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="fv-row mb-10">
                <label class="form-label fs-6 fw-bolder text-dark">Password</label>
                <input wire:model.defer="password" class="form-control form-control-lg form-control-solid" type="password" autocomplete="off" />
                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="fv-row mb-10">
                <label class="form-check form-check-custom form-check-solid">
                    <input wire:model="remember" class="form-check-input" type="checkbox" />
                    <span class="form-check-label fw-bold text-gray-700">Remember me</span>
                </label>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-lg btn-primary w-100 mb-5">
                    Login
                </button>
            </div>
        </form>
    </div>
</div>
