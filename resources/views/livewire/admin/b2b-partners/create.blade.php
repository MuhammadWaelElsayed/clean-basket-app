<div>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Create B2B Partner
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('b2b.partners') }}" class="text-muted text-hover-primary">B2B Partners</a>
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

                    <!--begin::Alert-->
                    @if (session()->has('success'))
                        <div class="alert alert-success d-flex align-items-center mb-5">
                            <i class="fas fa-check-circle fs-2x me-3"></i>
                            <div class="flex-grow-1">
                                {{ session('success') }}
                            </div>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger d-flex align-items-center mb-5">
                            <i class="fas fa-exclamation-circle fs-2x me-3"></i>
                            <div class="flex-grow-1">
                                {{ session('error') }}
                            </div>
                        </div>
                    @endif
                    <!--end::Alert-->

                    <form wire:submit.prevent="save">
                        <div class="row">
                            <!--begin::Main Column-->
                            <div class="col-lg-8">
                                <!--begin::Basic Information Card-->
                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Basic Information</h3>
                                    </div>
                                    <div class="card-body">
                                        <!--begin::Partner Name-->
                                        <div class="mb-7">
                                            <label class="form-label required">Partner Name</label>
                                            <input type="text"
                                                   wire:model="name"
                                                   class="form-control form-control-lg @error('name') is-invalid @enderror"
                                                   placeholder="Enter partner name">
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">This will be the display name for the partner</div>
                                        </div>
                                        <!--end::Partner Name-->
                                    </div>
                                </div>
                                <!--end::Basic Information Card-->

                                <!--begin::Fees Card-->
                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Fee Structure</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!--begin::Service Fees-->
                                            <div class="col-md-6 mb-7">
                                                <label class="form-label required">Service Fees</label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-dollar-sign"></i>
                                                    </span>
                                                    <input type="number"
                                                           step="0.01"
                                                           wire:model="service_fees"
                                                           class="form-control @error('service_fees') is-invalid @enderror"
                                                           placeholder="0.00">
                                                </div>
                                                @error('service_fees')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Fee charged for service processing</div>
                                            </div>
                                            <!--end::Service Fees-->

                                            <!--begin::Delivery Fees-->
                                            <div class="col-md-6 mb-7">
                                                <label class="form-label required">Delivery Fees</label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-shipping-fast"></i>
                                                    </span>
                                                    <input type="number"
                                                           step="0.01"
                                                           wire:model="delivery_fees"
                                                           class="form-control @error('delivery_fees') is-invalid @enderror"
                                                           placeholder="0.00">
                                                </div>
                                                @error('delivery_fees')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Fee charged for delivery services</div>
                                            </div>
                                            <!--end::Delivery Fees-->
                                        </div>

                                        <!--begin::Fee Summary-->
                                        @if($service_fees && $delivery_fees)
                                            <div class="alert alert-light-info d-flex align-items-center mt-5">
                                                <i class="fas fa-calculator fs-2x me-3 text-info"></i>
                                                <div>
                                                    <div class="fw-bold">Total Fees</div>
                                                    <div class="fs-2 fw-bolder text-info">
                                                        ${{ number_format(floatval($service_fees) + floatval($delivery_fees), 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <!--end::Fee Summary-->
                                    </div>
                                </div>
                                <!--end::Fees Card-->
                            </div>
                            <!--end::Main Column-->

                            <!--begin::Sidebar Column-->
                            <div class="col-lg-4">
                                <!--begin::Status Card-->
                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Status</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   wire:model="active"
                                                   id="partnerActive">
                                            <label class="form-check-label fw-bold" for="partnerActive">
                                                Partner Active
                                            </label>
                                        </div>
                                        <div class="form-text mt-3">
                                            @if($active)
                                                <span class="badge badge-light-success">
                                                    <i class="fas fa-check me-1"></i>
                                                    Partner will be active upon creation
                                                </span>
                                            @else
                                                <span class="badge badge-light-warning">
                                                    <i class="fas fa-pause me-1"></i>
                                                    Partner will be inactive upon creation
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!--end::Status Card-->

                                <!--begin::API Secret Info Card-->
                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-key text-primary me-2"></i>
                                            API Secret
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-light-primary mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-info-circle fs-2x me-3 text-primary"></i>
                                                <div>
                                                    <h5 class="mb-2">Initial Secret Generation</h5>
                                                    <p class="mb-0 text-gray-700">
                                                        An initial 64-character API secret will be automatically generated when you create this partner. You'll be redirected to the edit page where you can view and copy it.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::API Secret Info Card-->

                                <!--begin::Actions Card-->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex flex-column gap-3">
                                            <button type="submit"
                                                    class="btn btn-primary btn-lg"
                                                    wire:loading.attr="disabled">
                                                <span wire:loading.remove>
                                                    <i class="fas fa-save me-2"></i>
                                                    Create Partner
                                                </span>
                                                <span wire:loading>
                                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                                    Creating...
                                                </span>
                                            </button>

                                            <button type="button"
                                                    wire:click="cancel"
                                                    class="btn btn-light btn-lg"
                                                    wire:loading.attr="disabled">
                                                <i class="fas fa-times me-2"></i>
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Actions Card-->
                            </div>
                            <!--end::Sidebar Column-->
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Content-->
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Auto-save indicator
            window.addEventListener('beforeunload', function (e) {
                if (@this.name || @this.service_fees || @this.delivery_fees) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
@endpush
