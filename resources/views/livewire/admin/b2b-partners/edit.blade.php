<div>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Edit B2B Partner
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
                            <li class="breadcrumb-item text-muted">Edit #{{ $partner->id }}</li>
                        </ul>
                    </div>

                    <!--begin::Actions-->
                    <div class="d-flex align-items-center gap-2">
                        <button wire:click="openSecretModal" class="btn btn-light-primary">
                            <i class="fas fa-key me-2"></i>
                            Manage Secrets
                        </button>
                    </div>
                    <!--end::Actions-->
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

                    <form wire:submit.prevent="update">
                        <div class="row">
                            <!--begin::Main Column-->
                            <div class="col-lg-8">
                                <!--begin::Basic Information Card-->
                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Basic Information</h3>
                                        <div class="card-toolbar">
                                            <span class="badge badge-light-dark">ID: #{{ $partner->id }}</span>
                                        </div>
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

                                <!--begin::API Secrets Card-->
                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-key text-primary me-2"></i>
                                            API Secrets
                                        </h3>
                                        <div class="card-toolbar">
                                            <button type="button" wire:click="openSecretModal" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus me-1"></i>
                                                Generate New Secret
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if($secrets->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-row-bordered align-middle gy-4">
                                                    <thead>
                                                    <tr class="fw-bold text-muted bg-light">
                                                        <th class="ps-4 rounded-start">Secret Preview</th>
                                                        <th>Status</th>
                                                        <th>Created</th>
                                                        <th class="text-end rounded-end pe-4">Actions</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($secrets as $secret)
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-shield-alt fs-2x text-primary me-3"></i>
                                                                    <code class="font-monospace text-gray-700">
                                                                        {{ substr($secret->secret, 0, 16) }}...{{ substr($secret->secret, -8) }}
                                                                    </code>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @if($secret->active)
                                                                    <span class="badge badge-light-success">
                                                                        <i class="fas fa-check me-1"></i>
                                                                        Active
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-light-secondary">
                                                                        <i class="fas fa-ban me-1"></i>
                                                                        Inactive
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-gray-700 fw-bold">
                                                                        {{ $secret->created_at ? $secret->created_at->format('M d, Y') : 'N/A' }}
                                                                    </span>
                                                                    <span class="text-muted fs-7">
                                                                        {{ $secret->created_at ? $secret->created_at->format('h:i A') : '' }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td class="text-end pe-4">
                                                                <div class="d-flex justify-content-end gap-1">
                                                                    @if(!$secret->active)
                                                                        <button type="button"
                                                                                wire:click="activateSecret({{ $secret->id }})"
                                                                                class="btn btn-sm btn-icon btn-light btn-active-light-success"
                                                                                data-bs-toggle="tooltip"
                                                                                title="Activate Secret">
                                                                            <i class="fas fa-toggle-on"></i>
                                                                        </button>

                                                                        <button type="button"
                                                                                data-delete-secret="{{ $secret->id }}"
                                                                                class="btn btn-sm btn-icon btn-light btn-active-light-danger"
                                                                                data-bs-toggle="tooltip"
                                                                                title="Delete Secret">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    @else
                                                                        <span class="badge badge-light-primary">
                                                                            <i class="fas fa-lock me-1"></i>
                                                                            Current Active Secret
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-10">
                                                <i class="fas fa-key fs-5x text-muted mb-3"></i>
                                                <p class="text-muted">No secrets found. Generate one to get started.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <!--end::API Secrets Card-->
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
                                                    Partner is currently active
                                                </span>
                                            @else
                                                <span class="badge badge-light-danger">
                                                    <i class="fas fa-times me-1"></i>
                                                    Partner is currently inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!--end::Status Card-->

                                <!--begin::Metadata Card-->
                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Metadata</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-5">
                                            <label class="fs-6 fw-bold text-gray-600">Created At</label>
                                            <div class="text-gray-800">
                                                {{ $partner->created_at ? $partner->created_at->format('M d, Y h:i A') : 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="mb-5">
                                            <label class="fs-6 fw-bold text-gray-600">Last Updated</label>
                                            <div class="text-gray-800">
                                                {{ $partner->updated_at ? $partner->updated_at->format('M d, Y h:i A') : 'N/A' }}
                                            </div>
                                        </div>
                                        <div>
                                            <label class="fs-6 fw-bold text-gray-600">Total Secrets</label>
                                            <div class="text-gray-800">
                                                <span class="badge badge-light-primary">{{ $secrets->count() }}</span>
                                                <span class="text-muted ms-2">
                                                    ({{ $secrets->where('active', true)->count() }} active)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Metadata Card-->

                                <!--begin::Actions Card-->
                                <div class="card mb-5">
                                    <div class="card-body">
                                        <div class="d-flex flex-column gap-3">
                                            <button type="submit"
                                                    class="btn btn-primary btn-lg"
                                                    wire:loading.attr="disabled">
                                                <span wire:loading.remove>
                                                    <i class="fas fa-save me-2"></i>
                                                    Update Partner
                                                </span>
                                                <span wire:loading>
                                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                                    Updating...
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

                                <!--begin::Danger Zone Card-->
                                <div class="card border-danger">
                                    <div class="card-header bg-light-danger">
                                        <h3 class="card-title text-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Danger Zone
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3">
                                            Deleting this partner will remove all associated data including API secrets. This action cannot be undone.
                                        </p>
                                        <button type="button"
                                                id="deletePartnerBtn"
                                                class="btn btn-danger btn-lg w-100">
                                            <i class="fas fa-trash me-2"></i>
                                            Delete Partner
                                        </button>
                                    </div>
                                </div>
                                <!--end::Danger Zone Card-->
                            </div>
                            <!--end::Sidebar Column-->
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Content-->
        </div>
    </div>

    <!--begin::Secret Generation Modal-->
    @if($showSecretModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-key text-primary me-2"></i>
                            Generate New API Secret
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeSecretModal"></button>
                    </div>
                    <div class="modal-body">
                        @if($newSecret)
                            <div class="alert alert-success d-flex align-items-center mb-5">
                                <i class="fas fa-check-circle fs-2x me-3"></i>
                                <div class="flex-grow-1">
                                    <h4 class="mb-1">New Secret Generated!</h4>
                                    <p class="mb-0">Copy this secret now. It won't be shown again.</p>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label fw-bold fs-4">Your New API Secret:</label>
                                <div class="input-group input-group-lg">
                                    <input type="text"
                                           class="form-control font-monospace bg-light-primary"
                                           value="{{ $newSecret }}"
                                           id="secretInput"
                                           readonly>
                                    <button class="btn btn-primary" type="button" onclick="copySecret()">
                                        <i class="fas fa-copy me-1"></i>
                                        Copy
                                    </button>
                                </div>
                                <div class="alert alert-light-warning mt-3 d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle fs-2x me-3 text-warning"></i>
                                    <div>
                                        <p class="fw-bold mb-1">Important Security Notice:</p>
                                        <ul class="mb-0">
                                            <li>Store this secret securely in your application</li>
                                            <li>Never share it publicly or commit it to version control</li>
                                            <li>All previous secrets have been automatically deactivated</li>
                                            <li>This secret will not be shown again</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light-info d-flex align-items-center mb-5">
                                <i class="fas fa-info-circle fs-2x me-3"></i>
                                <div>
                                    <h5 class="mb-1">Before You Continue</h5>
                                    <p class="mb-0">
                                        Generating a new secret will automatically deactivate all previous secrets for this partner.
                                        Make sure you're ready to update your integration with the new secret.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-light rounded p-5 mb-5">
                                <h6 class="fw-bold mb-3">What will happen:</h6>
                                <ul class="mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        A new 64-character secret will be generated
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-ban text-danger me-2"></i>
                                        All existing secrets will be deactivated
                                    </li>
                                    <li>
                                        <i class="fas fa-key text-primary me-2"></i>
                                        Only the new secret will work for API authentication
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeSecretModal">
                            {{ $newSecret ? 'Close' : 'Cancel' }}
                        </button>
                        @if(!$newSecret)
                            <button type="button" class="btn btn-primary" wire:click="generateSecret">
                                <i class="fas fa-sync-alt me-2"></i>
                                Generate New Secret
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!--end::Secret Generation Modal-->
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            @this.on('partner-updated', (event) => {
                Swal.fire({
                    title: 'Success!',
                    text: 'Partner has been updated successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            });

            @this.on('secret-generated', (event) => {
                Swal.fire({
                    title: 'Secret Generated!',
                    text: 'New API secret has been generated. Make sure to copy it now.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 3000
                });
            });

            // Unsaved changes warning
            window.addEventListener('beforeunload', function (e) {
                if (@this.isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Delete partner confirmation
            document.getElementById('deletePartnerBtn')?.addEventListener('click', function() {
                Swal.fire({
                    title: 'Are you absolutely sure?',
                    html: '<p>This will permanently delete:</p><ul class="text-start"><li>The partner</li><li>All API secrets</li><li>All associated data</li></ul><p class="text-danger fw-bold mt-3">This action cannot be undone!</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Only execute if user clicked "Yes, delete it!"
                        @this.call('deletePartner');
                    }
                    // If user clicks Cancel or closes dialog, nothing happens
                });
            });

            // Delete secret confirmation
            document.addEventListener('click', function(e) {
                const deleteSecretBtn = e.target.closest('[data-delete-secret]');
                if (deleteSecretBtn) {
                    e.preventDefault();
                    e.stopPropagation();

                    const secretId = deleteSecretBtn.getAttribute('data-delete-secret');

                    Swal.fire({
                        title: 'Delete Secret?',
                        text: 'This will permanently delete this API secret. This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        focusCancel: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Only execute if user clicked "Yes, delete it!"
                            @this.call('deleteSecret', parseInt(secretId));
                        }
                        // If cancelled, nothing happens
                    });
                }
            });
        });

        function copySecret() {
            const secretInput = document.getElementById('secretInput');
            secretInput.select();
            secretInput.setSelectionRange(0, 99999);

            navigator.clipboard.writeText(secretInput.value).then(() => {
                Swal.fire({
                    title: 'Copied!',
                    text: 'Secret copied to clipboard successfully',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }).catch(() => {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to copy secret. Please copy manually.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    </script>
@endpush
