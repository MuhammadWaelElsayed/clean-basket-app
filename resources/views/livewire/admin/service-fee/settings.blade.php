@section('serviceFeeSettingsActive', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Service Fee Settings
                    </h1>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body">

                        @if (session()->has('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form wire:submit.prevent="save">
                            <!-- Enable/Disable Service Fee -->
                            <div class="mb-5">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="is_enabled" id="is_enabled">
                                    <label class="form-check-label fw-bold" for="is_enabled">
                                        Enable Service Fee for Small Orders
                                    </label>
                                </div>
                                <div class="form-text">When enabled, a service fee will be added to orders below the minimum amount</div>
                                @error('is_enabled') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <!-- Minimum Order Amount -->
                            <div class="mb-5">
                                <label class="form-label fw-bold">Minimum Order Amount ({{ env('CURRENCY', 'SAR') }})</label>
                                <input type="number" step="0.01" wire:model="minimum_order_amount" class="form-control" placeholder="Enter the minimum order amount">
                                <div class="form-text">Orders below this amount will be charged a service fee</div>
                                @error('minimum_order_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <!-- Service Fee Amount -->
                            <div class="mb-5">
                                <label class="form-label fw-bold">Service Fee Amount ({{ env('CURRENCY', 'SAR') }})</label>
                                <input type="number" step="0.01" wire:model="service_fee_amount" class="form-control" placeholder="Enter the service fee amount">
                                <div class="form-text">The amount that will be added to small orders</div>
                                @error('service_fee_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <!-- Service Fee Description -->
                            <div class="mb-5">
                                <label class="form-label fw-bold">Service Fee Description</label>
                                <textarea wire:model="description" class="form-control" rows="3" placeholder="Enter a description for the service fee (optional)"></textarea>
                                <div class="form-text">A description that will be shown to customers when adding the service fee</div>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <!-- Additional Information -->
                            <div class="mb-5">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Important Information:</h6>
                                    <ul class="mb-0">
                                        <li>Service fees apply to all types of orders (regular, bundle, wallet)</li>
                                        <li>Applied to all customers without exceptions</li>
                                        <li>The fee is calculated before adding the tax</li>
                                        <li>The feature can be enabled/disabled at any time</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-duotone ki-check fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Save Settings
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <!--end::Content-->
    </div>
</div>
