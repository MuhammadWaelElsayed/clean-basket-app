
@section('b2bClientsActive', 'active')

<div>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            B2B Clients Management
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">B2B Clients</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        @can('manage_b2b_clients')
                            <a href="{{ route('b2b-clients.create') }}" class="btn btn-sm fw-bold btn-base" wire:navigate>
                                <i class="ki-duotone ki-plus fs-2"></i>Add New Client
                            </a>
                        @endcan
                    </div>
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
                                           placeholder="Search clients...">
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Card title-->

                            <!--begin::Card toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Toolbar-->
                                <div class="d-flex justify-content-end gap-2" data-kt-customer-table-toolbar="base">
                                    <!--begin::Filter-->
                                    <select wire:model.live="is_active" class="form-select form-select-solid w-150px">
                                        <option value="">All Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>

                                    <select wire:model.live="pricing_tier_id" class="form-select form-select-solid w-200px">
                                        <option value="">All Pricing Tiers</option>
                                        @foreach($pricingTiers as $tier)
                                            <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Filter-->
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
                                        <th wire:click="sortBy('id')" style="cursor: pointer;">
                                            ID
                                            @if($sortField === 'id')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('company_name')" style="cursor: pointer;">
                                            Company
                                            @if($sortField === 'company_name')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th>Contact Person</th>
                                        <th wire:click="sortBy('email')" style="cursor: pointer;">
                                            Email
                                            @if($sortField === 'email')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th>Phone</th>
                                        <th>Pricing Tier</th>
                                        <th>Vendor</th>
                                        <th>Driver</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                    @forelse($clients as $client)
                                        <tr>
                                            <td>{{ $client->id }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold">{{ $client->company_name }}</span>
                                                    @if($client->tax_number)
                                                        <span class="text-muted fs-7">TAX: {{ $client->tax_number }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $client->contact_person }}</td>
                                            <td>
                                                <a href="mailto:{{ $client->email }}" class="text-gray-600 text-hover-primary">
                                                    {{ $client->email }}
                                                </a>
                                            </td>
                                            <td>{{ $client->phone ?? '-' }}</td>
                                            <td>
                                                @if($client->pricingTier)
                                                    <span class="badge badge-light-primary">
                                                    {{ $client->pricingTier->name }}
                                                    ({{ $client->pricingTier->discount_percentage }}%)
                                                </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $client->vendor->business_name ?? '-' }}</td>
                                            <td>{{ $client->driver->name ?? '-' }}</td>
                                            <td>
                                                @can('manage_b2b_clients')
                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox"
                                                               wire:click="toggleStatus({{ $client->id }})"
                                                            {{ $client->is_active == true ? 'checked' : '' }}>
                                                    </div>
                                                @else
                                                    <span class="badge badge-light-{{ $client->is_active == true ? 'success' : 'danger' }}">
                                            </span>
                                                @endcan
                                            </td>
                                            <td>{{ $client->created_at->format('d M Y') }}</td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    @can('manage_b2b_clients')
                                                        <a href="{{ route('b2b-clients.edit', $client->id) }}"
                                                           class="btn btn-sm btn-light btn-active-light-primary"
                                                           wire:navigate>
                                                            <i class="fas fa-edit"></i>
                                                            Edit
                                                        </a>

                                                        <a href="{{ route('b2b-clients.change-password', $client->id) }}"
                                                           class="btn btn-sm btn-light btn-active-light-warning"
                                                           wire:navigate>
                                                            <i class="fas fa-key"></i>
                                                            Password
                                                        </a>

                                                        <button wire:click="confirmDelete({{ $client->id }})"
                                                                class="btn btn-sm btn-light btn-active-light-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                            Delete
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-10">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="ki-duotone ki-user fs-5x text-muted mb-5">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    <span class="text-muted">No B2B clients found</span>
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
                                    Showing {{ $clients->firstItem() ?? 0 }} to {{ $clients->lastItem() ?? 0 }}
                                    of {{ $clients->total() }} entries
                                </div>
                                {{ $clients->links() }}
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

    <!--begin::Delete Modal-->
    <div class="modal fade" id="kt_modal_delete" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Delete B2B Client</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    @if($clientToDelete)
                        <div class="alert alert-danger">
                            <p class="mb-0">Are you sure you want to delete <strong>{{ $clientToDelete->company_name }}</strong>?</p>
                            <p class="mb-0 mt-3">This action cannot be undone. All associated data will be permanently deleted.</p>
                        </div>
                    @endif
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" wire:click="delete" class="btn btn-danger" data-bs-dismiss="modal">
                            Delete Client
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Delete Modal-->

</div>
@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('show-delete-modal', (event) => {
                const modal = new bootstrap.Modal(document.getElementById('kt_modal_delete'));
                modal.show();
            });

            @this.on('hide-delete-modal', (event) => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_delete'));
                if (modal) {
                    modal.hide();
                }
            });
        });
    </script>
@endpush
