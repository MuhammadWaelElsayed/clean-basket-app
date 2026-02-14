
@section('b2bClientsActive', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Add New B2B Client
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
                        <li class="breadcrumb-item text-muted">Create</li>
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
                    <div class="card-body pt-10">
                        <!--begin::Form-->
                        <form wire:submit.prevent="store" class="form">
                            <!--begin::Company Information-->
                            <div class="mb-10">
                                <h3 class="fw-bold text-dark mb-7">Company Information</h3>

                                <div class="row g-9">
                                    <!--begin::Company Name-->
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Company Name</label>
                                        <input type="text" wire:model="company_name" class="form-control" placeholder="ABC Trading Company">
                                        @error('company_name') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Company Name-->

                                    <!--begin::Contact Person-->
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Contact Person</label>
                                        <input type="text" wire:model="contact_person" class="form-control" placeholder="John Doe">
                                        @error('contact_person') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Contact Person-->

                                    <!--begin::Email-->
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Email Address</label>
                                        <input type="email" wire:model="email" class="form-control" placeholder="contact@company.com">
                                        @error('email') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Email-->

                                    <!--begin::Phone-->
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Phone Number</label>
                                        <input type="text" wire:model="phone" class="form-control" placeholder="+96512345678">
                                        @error('phone') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Phone-->

                                    <!--begin::Tax Number-->
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Tax Number</label>
                                        <input type="text" wire:model="tax_number" class="form-control" placeholder="TAX123456">
                                        @error('tax_number') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Tax Number-->

                                    <!--begin::Address-->
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Address</label>
                                        <textarea wire:model="address" class="form-control" rows="1" placeholder="Block 5, Street 50, Kuwait City"></textarea>
                                        @error('address') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Address-->

                                    <!--begin::service fees-->
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Service fees</label>
                                        <input step="any" required type="number" wire:model="service_fees" class="form-control"/>
                                        @error('service_fees') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::service fees-->

                                    <!--begin::delivery fees-->
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Delivery fees</label>
                                        <input step="any" required type="number" wire:model="delivery_fees" class="form-control"/>
                                        @error('delivery_fees') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::delivery fees-->

                                    <!--begin::Vendor-->
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Vendor (Optional)</label>
                                        <select wire:model="vendor_id" class="form-select">
                                            <option value="">Select Vendor</option>
                                            @foreach($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('vendor_id') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Vendor-->

                                    <!--begin::Driver-->
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Driver (Optional)</label>
                                        <select wire:model="driver_id" class="form-select">
                                            <option value="">Select Driver</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('driver_id') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Driver-->

                                </div>
                            </div>
                            <!--end::Company Information-->

                            <div class="separator separator-dashed my-10"></div>

                            <!--begin::Account Settings-->
                            <div class="mb-10">
                                <h3 class="fw-bold text-dark mb-7">Account Settings</h3>

                                <div class="row g-9">
                                    <!--begin::Password-->
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Password</label>
                                        <input type="password" wire:model="password" class="form-control" placeholder="Min 8 characters">
                                        @error('password') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Password-->

                                    <!--begin::Password Confirmation-->
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Confirm Password</label>
                                        <input type="password" wire:model="password_confirmation" class="form-control" placeholder="Confirm password">
                                        @error('password_confirmation') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Password Confirmation-->

                                    <!--begin::Pricing Tier-->
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Pricing Tier</label>
                                        <select wire:model="pricing_tier_id" class="form-select">
                                            <option value="">Select Pricing Tier</option>
                                            @foreach($pricingTiers as $tier)
                                                <option value="{{ $tier->id }}">
                                                    {{ $tier->name }} ({{ $tier->discount_percentage }}% discount) - ({{ $tier->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pricing_tier_id') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Pricing Tier-->

                                    <!--begin::Status-->
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Status</label>
                                        <select wire:model="is_active" class="form-select">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                        @error('status') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                    </div>
                                    <!--end::Status-->
                                </div>
                            </div>
                            <!--end::Account Settings-->

                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('b2b-clients.index') }}" class="btn btn-light" wire:navigate>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-base" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Create Client</span>
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
