
@section('pricingTiersActive', 'active')

<div>

    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Pricing Tiers Management
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Pricing Tiers</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        @can('manage_b2b_pricing_tiers')
                            <a href="{{ route('pricing-tiers.create') }}" class="btn btn-sm fw-bold btn-base" wire:navigate>
                                <i class="ki-duotone ki-plus fs-2"></i>Add New Tier
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">

                    <!--begin::Stats Card-->
                    <div class="row g-5 g-xl-8 mb-5">
                        <div class="col-xl-3">
                            <div class="card card-flush h-xl-100">
                                <div class="card-body">
                                    <span class="text-gray-400 fw-semibold fs-7">Total Items</span>
                                    <div class="fs-2x fw-bold text-dark">{{ number_format($totalItems) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="card card-flush h-xl-100">
                                <div class="card-body">
                                    <span class="text-gray-400 fw-semibold fs-7">Total Tiers</span>
                                    <div class="fs-2x fw-bold text-dark">{{ $tiers->total() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Stats Card-->

                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <input type="text" wire:model.live.debounce.300ms="search"
                                           class="form-control form-control-solid w-250px ps-13"
                                           placeholder="Search tiers...">
                                </div>
                            </div>

                            <div class="card-toolbar">
                                <div class="d-flex justify-content-end gap-2">
                                    <select wire:model.live="status" class="form-select form-select-solid w-150px">
                                        <option value="">All Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <select wire:model.live="type" class="form-select form-select-solid w-150px">
                                        <option value="">All types</option>
                                        <option value="dynamic">Dynamic</option>
                                        <option value="fixed">Fixed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th wire:click="sortBy('priority')" style="cursor: pointer;">
                                            Priority
                                            @if($sortField === 'priority')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th>Type</th>
                                        <th wire:click="sortBy('name')" style="cursor: pointer;">
                                            Name
                                            @if($sortField === 'name')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th>Items min-max</th>
                                        <th>Description</th>
                                        <th>Discount</th>
                                        <th>Items Priced</th>
                                        <th>Clients</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                    @forelse($tiers as $tier)
                                        @php
                                            $pricedItems = $tier->item_prices_count;
                                            $unpricedItems = $totalItems - $pricedItems;
                                            $pricedPercentage = $totalItems > 0 ? round(($pricedItems / $totalItems) * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                            <span class="badge badge-light-primary fs-7 fw-bold">
                                                {{ $tier->priority }}
                                            </span>
                                            </td>
                                            <td>{{ $tier->type }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold">{{ $tier->name }}</span>
                                                    @if($tier->name_ar)
                                                        <span class="text-muted fs-7">{{ $tier->name_ar }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td><span>{{  ($tier->min && $tier->max) ? ($tier->min .' - '. $tier->max) : 'N/A' }}</span></td>
                                            <td>
                                                <span class="text-gray-600">{{ Str::limit($tier->description, 50) }}</span>
                                            </td>
                                            <td>
                                            <span class="badge badge-light-success fs-7 fw-bold">
                                                {{ number_format($tier->discount_percentage, 2) }}%
                                            </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex align-items-center mb-1">
                                                    <span class="badge badge-light-{{ $pricedItems > 0 ? 'success' : 'warning' }} me-2">
                                                        {{ $pricedItems }} Custom
                                                    </span>
                                                        <span class="badge badge-light-info">
                                                        {{ $unpricedItems }} Default
                                                    </span>
                                                    </div>
                                                    <div class="progress h-6px w-100">
                                                        <div class="progress-bar bg-success"
                                                             role="progressbar"
                                                             style="width: {{ $pricedPercentage }}%"
                                                             aria-valuenow="{{ $pricedPercentage }}"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="text-muted fs-7 mt-1">
                                                    {{ $pricedPercentage }}% priced ({{ $pricedItems }}/{{ $totalItems }})
                                                </span>
                                                </div>
                                            </td>
                                            <td>
                                            <span class="badge badge-light-info">
                                                {{ $tier->clients_count }} {{ Str::plural('Client', $tier->clients_count) }}
                                            </span>
                                            </td>
                                            <td>
                                                @can('manage_b2b_pricing_tiers')
                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox"
                                                               wire:click="toggleStatus({{ $tier->id }})"
                                                            {{ $tier->is_active ? 'checked' : '' }}>
                                                    </div>
                                                @else
                                                    <span class="badge badge-light-{{ $tier->is_active ? 'success' : 'danger' }}">
                                                {{ $tier->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                                @endcan
                                            </td>
                                            <td>{{ $tier->created_at->format('d M Y') }}</td>

                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    @can('manage_b2b_pricing_tiers')
                                                        <a href="{{ route('pricing-tiers.edit', $tier->id) }}"
                                                           class="btn btn-sm btn-light btn-active-light-primary"
                                                           wire:navigate>
                                                            <i class="fas fa-edit"></i>
                                                            Edit
                                                        </a>

                                                        <a href="{{ route('pricing-tiers.item-prices', $tier->id) }}"
                                                           class="btn btn-sm btn-light btn-active-light-info"
                                                           wire:navigate>
                                                            <i class="fas fa-tags"></i>
                                                            Prices
                                                        </a>
                                                    @endcan

                                                    @can('manage_b2b_pricing_tiers')
                                                        <button wire:click="confirmDelete({{ $tier->id }})"
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
                                            <td colspan="9" class="text-center py-10">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="ki-duotone ki-price-tag fs-5x text-muted mb-5">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    <span class="text-muted">No pricing tiers found</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap pt-5">
                                <div class="fs-6 fw-semibold text-gray-700">
                                    Showing {{ $tiers->firstItem() ?? 0 }} to {{ $tiers->lastItem() ?? 0 }}
                                    of {{ $tiers->total() }} entries
                                </div>
                                {{ $tiers->links() }}
                            </div>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
            </div>
        </div>
    </div>

    <!--begin::Delete Modal-->
    <div class="modal fade" id="kt_modal_delete" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Delete Pricing Tier</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    @if($tierToDelete)
                        <div class="alert alert-danger">
                            <p class="mb-0">Are you sure you want to delete <strong>{{ $tierToDelete->name }}</strong>?</p>
                            <p class="mb-0 mt-3">This action cannot be undone.</p>
                        </div>
                    @endif
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" wire:click="delete" class="btn btn-danger" data-bs-dismiss="modal">
                            Delete Tier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Reinitialize tooltips after Livewire updates
            Livewire.hook('morph.updated', () => {
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            });

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
