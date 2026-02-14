
@section('walletSettingsActive', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Wallet Settings
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
                            <div class="mb-5">
                                <label class="form-label fw-bold">Minimum Balance ({{ env('CURRENCY', 'SAR') }})</label>
                                <input type="number" step="0.01" wire:model="min_balance" class="form-control" placeholder="Enter minimum wallet balance">
                                @error('min_balance') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-5">
                                <label class="form-label fw-bold">Maximum Balance ({{ env('CURRENCY', 'SAR') }})</label>
                                <input type="number" step="0.01" wire:model="max_balance" class="form-control" placeholder="Enter maximum wallet balance (optional)">
                                @error('max_balance') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-5">
                                <label class="form-label fw-bold">Balance Validity (Days)</label>
                                <input type="number" wire:model="balance_validity_days" class="form-control" placeholder="Number of days balance remains valid">
                                @error('balance_validity_days') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
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
