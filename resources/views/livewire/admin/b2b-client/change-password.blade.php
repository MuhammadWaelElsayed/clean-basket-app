
@section('b2bClientsActive', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Change Password
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('b2b-clients.index') }}" class="text-muted text-hover-primary">B2B Clients</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Change Password</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Change Password for: <strong>{{ $client->company_name }}</strong></h3>
                    </div>
                    <div class="card-body pt-10">
                        <!--begin::Form-->
                        <form wire:submit.prevent="changePassword" class="form">
                            <div class="row g-9">
                                <!--begin::New Password-->
                                <div class="col-md-6 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">New Password</label>
                                    <input type="password" wire:model="new_password" class="form-control" placeholder="Min 8 characters">
                                    @error('new_password') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>
                                <!--end::New Password-->

                                <!--begin::Confirm Password-->
                                <div class="col-md-6 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Confirm New Password</label>
                                    <input type="password" wire:model="new_password_confirmation" class="form-control" placeholder="Confirm password">
                                    @error('new_password_confirmation') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>
                                <!--end::Confirm Password-->

                                <!--begin::Revoke Tokens-->
                                <div class="col-12 fv-row">
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" wire:model="revoke_all_tokens" id="revoke_tokens">
                                        <label class="form-check-label fs-6 fw-semibold" for="revoke_tokens">
                                            Revoke all active API tokens (force logout from all devices)
                                        </label>
                                    </div>
                                </div>
                                <!--end::Revoke Tokens-->
                            </div>

                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end gap-2 mt-10">
                                <a href="{{ route('b2b-clients.index') }}" class="btn btn-light" wire:navigate>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-base" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Change Password</span>
                                    <span wire:loading>
                                        Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                            </div>
                            <!--end::Actions-->
                        </form>
                        <!--end::Form-->
                    </div>
                </div>
                <!--end::Card-->
            </div>
        </div>
        <!--end::Content-->
    </div>
</div>
