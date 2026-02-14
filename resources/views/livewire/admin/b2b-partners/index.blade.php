<div>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            B2B Partners Management
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">B2B Partners</li>
                        </ul>
                    </div>

                    <!--begin::Stats-->
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-light-primary rounded p-3 d-flex align-items-center">
                            <i class="fas fa-handshake text-primary fs-2x me-3"></i>
                            <div>
                                <div class="fs-7 text-muted">Total Partners</div>
                                <div class="fs-4 fw-bold text-primary">{{ $partners->total() }}</div>
                            </div>
                        </div>
                    </div>
                    <!--end::Stats-->
                </div>
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header border-0 pt-6">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <!--begin::Search-->
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <input type="text" wire:model.live.debounce.300ms="search"
                                           class="form-control form-control-solid w-250px ps-13"
                                           placeholder="Search partners...">
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Card title-->

                            <!--begin::Card toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Toolbar-->
                                <div class="d-flex justify-content-end gap-2 flex-wrap" data-kt-partners-table-toolbar="base">
                                    <!--begin::Filter-->
                                    <select wire:model.live="active" class="form-select form-select-solid w-150px">
                                        <option value="">All Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <!--end::Filter-->

                                    <!--begin::Add Partner-->
{{--                                    @can('manage_b2b_partners')--}}
                                        <a href="{{ route('b2b.partners.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>
                                            Add Partner
                                        </a>
{{--                                    @endcan--}}
                                    <!--end::Add Partner-->
                                </div>
                                <!--end::Toolbar-->
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th wire:click="sortBy('id')" style="cursor: pointer;" class="min-w-50px">
                                            ID
                                            @if($sortField === 'id')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('name')" style="cursor: pointer;" class="min-w-200px">
                                            Partner Name
                                            @if($sortField === 'name')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('service_fees')" style="cursor: pointer;" class="min-w-120px">
                                            Service Fees
                                            @if($sortField === 'service_fees')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('delivery_fees')" style="cursor: pointer;" class="min-w-120px">
                                            Delivery Fees
                                            @if($sortField === 'delivery_fees')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th class="min-w-100px">Secrets</th>
                                        <th wire:click="sortBy('active')" style="cursor: pointer;" class="min-w-100px">
                                            Status
                                            @if($sortField === 'active')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('created_at')" style="cursor: pointer;" class="min-w-120px">
                                            Created
                                            @if($sortField === 'created_at')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th class="text-end min-w-150px">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                    @forelse($partners as $partner)
                                        <tr>
                                            <td>
                                                <span class="badge badge-light-dark">#{{ $partner->id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40px symbol-circle me-3">
                                                        <div class="symbol-label bg-light-primary text-primary fw-bold fs-5">
                                                            {{ substr($partner->name, 0, 2) }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold">{{ $partner->name }}</span>
                                                        <span class="text-muted fs-7">Partner ID: {{ $partner->id }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-dollar-sign text-success me-2"></i>
                                                    <span class="text-gray-800 fw-bold">{{ number_format($partner->service_fees, 2) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-shipping-fast text-info me-2"></i>
                                                    <span class="text-gray-800 fw-bold">{{ number_format($partner->delivery_fees, 2) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="badge badge-light-primary mb-1">
                                                        Total: {{ $partner->secrets_count }}
                                                    </span>
                                                    <span class="badge badge-light-success">
                                                        Active: {{ $partner->active_secrets_count }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
{{--                                                @can('manage_b2b_partners')--}}
                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox"
                                                               wire:click="toggleActive({{ $partner->id }})"
                                                            {{ $partner->active ? 'checked' : '' }}>
                                                    </div>
{{--                                                @else--}}
                                                    @if($partner->active)
                                                        <span class="badge badge-light-success">
                                                            <i class="fas fa-check me-1"></i>
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="badge badge-light-danger">
                                                            <i class="fas fa-times me-1"></i>
                                                            Inactive
                                                        </span>
                                                    @endif
{{--                                                @endcan--}}
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7">
                                                        {{ $partner->created_at->format('d M Y') }}
                                                    </span>
                                                    <span class="text-muted fs-8">
                                                        {{ $partner->created_at->format('h:i A') }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
{{--                                                    @can('manage_b2b_partners')--}}
                                                        <button wire:click="openSecretModal({{ $partner->id }})"
                                                                class="btn btn-sm btn-icon btn-light btn-active-light-info"
                                                                data-bs-toggle="tooltip"
                                                                title="Manage Secrets">
                                                            <i class="fas fa-key"></i>
                                                        </button>

                                                        <a href="{{ route('b2b.partners.edit', $partner->id) }}"
                                                           class="btn btn-sm btn-icon btn-light btn-active-light-primary"
                                                           data-bs-toggle="tooltip"
                                                           title="Edit Partner">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <button type="button"
                                                                data-delete-partner="{{ $partner->id }}"
                                                                class="btn btn-sm btn-icon btn-light btn-active-light-danger"
                                                                data-bs-toggle="tooltip"
                                                                title="Delete Partner">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
{{--                                                    @endcan--}}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-10">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-handshake fs-5x text-muted mb-5"></i>
                                                    <span class="text-muted fs-5 fw-bold">No partners found</span>
                                                    <span class="text-muted fs-7 mt-2">Try adjusting your filters or add a new partner</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!--end::Table-->

                            <!--begin::Pagination-->
                            <div class="d-flex justify-content-between align-items-center flex-wrap pt-5">
                                <div class="fs-6 fw-semibold text-gray-700">
                                    Showing {{ $partners->firstItem() ?? 0 }} to {{ $partners->lastItem() ?? 0 }}
                                    of {{ $partners->total() }} entries
                                </div>
                                {{ $partners->links() }}
                            </div>
                            <!--end::Pagination-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
            </div>
            <!--end::Content-->
        </div>
    </div>

    <!--begin::Create/Edit Modal-->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Partner' : 'Add New Partner' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-5">
                                <label class="form-label required">Partner Name</label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter partner name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-5">
                                <label class="form-label required">Service Fees</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" step="0.01" wire:model="service_fees" class="form-control @error('service_fees') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('service_fees') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-5">
                                <label class="form-label required">Delivery Fees</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-shipping-fast"></i></span>
                                    <input type="number" step="0.01" wire:model="delivery_fees" class="form-control @error('delivery_fees') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('delivery_fees') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-5">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" wire:model="partner_active" id="partnerActive">
                                    <label class="form-check-label" for="partnerActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="fas fa-save me-2"></i>
                            {{ $editMode ? 'Update' : 'Create' }} Partner
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!--end::Create/Edit Modal-->

    <!--begin::Secret Management Modal-->
    @if($showSecretModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-key text-primary me-2"></i>
                            Manage API Secrets
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
                                <label class="form-label fw-bold">Your New API Secret:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control font-monospace" value="{{ $newSecret }}" id="secretInput" readonly>
                                    <button class="btn btn-primary" type="button" onclick="copySecret()">
                                        <i class="fas fa-copy me-1"></i>
                                        Copy
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                    Store this secret securely. Previous secrets have been automatically deactivated.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info d-flex align-items-center mb-5">
                                <i class="fas fa-info-circle fs-2x me-3"></i>
                                <div>
                                    <h5 class="mb-1">Generate New Secret</h5>
                                    <p class="mb-0">Generating a new secret will automatically deactivate all previous secrets for this partner.</p>
                                </div>
                            </div>

                            @php
                                $secrets = \App\Models\B2BPartnerSecret::where('b2b_partner_id', $selectedPartnerId)
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                            @endphp

                            @if($secrets->count() > 0)
                                <div class="mb-5">
                                    <h6 class="fw-bold mb-3">Secret History</h6>
                                    <div class="table-responsive">
                                        <table class="table table-row-bordered">
                                            <thead>
                                            <tr class="fw-bold fs-7 text-gray-400 text-uppercase">
                                                <th>Secret Preview</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($secrets as $secret)
                                                <tr>
                                                    <td>
                                                        <code class="font-monospace">{{ substr($secret->secret, 0, 16) }}...{{ substr($secret->secret, -8) }}</code>
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
                                                        <span class="text-muted fs-7">{{ $secret->created_at->format('d M Y, h:i A') }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
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
    <!--end::Secret Management Modal-->
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            @this.on('partner-saved', (event) => {
                Swal.fire({
                    title: 'Success!',
                    text: event[0].message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            });

            @this.on('partner-status-toggled', (event) => {
                Swal.fire({
                    title: 'Updated!',
                    text: event[0].message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 1500
                });
            });

            @this.on('partner-deleted', (event) => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Partner has been deleted successfully.',
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

            // Delete partner confirmation - using event delegation
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('[data-delete-partner]');
                if (deleteBtn) {
                    e.preventDefault();
                    e.stopPropagation();

                    const partnerId = deleteBtn.getAttribute('data-delete-partner');

                    Swal.fire({
                        title: 'Delete Partner?',
                        html: '<p>This will permanently delete the partner and all associated data including API secrets.</p><p class="text-danger fw-bold mt-2">This action cannot be undone!</p>',
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
                            @this.call('deletePartner', parseInt(partnerId));
                        }
                        // If cancelled, nothing happens
                    });
                }
            });
        });

        function copySecret() {
            const secretInput = document.getElementById('secretInput');
            secretInput.select();
            secretInput.setSelectionRange(0, 99999); // For mobile devices

            navigator.clipboard.writeText(secretInput.value).then(() => {
                Swal.fire({
                    title: 'Copied!',
                    text: 'Secret copied to clipboard',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }
    </script>
@endpush
