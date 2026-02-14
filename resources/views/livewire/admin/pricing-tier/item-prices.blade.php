{{-- resources/views/livewire/admin/pricing-tier/item-prices.blade.php --}}

@section('pricingTiersActive', 'active')

<div>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Manage Item Prices: {{ $tier->name }}
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('pricing-tiers.index') }}" class="text-muted text-hover-primary">Pricing Tiers</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Item Prices</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2">
{{--                        <button wire:click="applyGlobalDiscount"--}}
{{--                                class="btn btn-sm btn-light-primary"--}}
{{--                                wire:confirm="Apply {{ $tier->discount_percentage }}% discount to all items?">--}}
{{--                            <i class="fas fa-percentage"></i>--}}
{{--                            Apply {{ $tier->discount_percentage }}% to All--}}
{{--                        </button>--}}

                        @if($pricedItemsCount > 0)
                            <button wire:click="clearAllPrices"
                                    class="btn btn-sm btn-light-danger"
                                    wire:confirm="Remove all custom prices for this tier?">
                                <i class="fas fa-eraser"></i>
                                Clear All Prices
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">

                    <!--begin::Stats Cards-->
                    <div class="row g-5 g-xl-8 mb-5">
                        <!--begin::Tier Info Card-->
                        <div class="col-xl-8">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="fw-bold text-gray-600 mb-1">Tier Name</div>
                                            <div class="fs-5 fw-bold text-gray-800">{{ $tier->name }}</div>
                                            @if($tier->name_ar)
                                                <div class="fs-7 text-muted">{{ $tier->name_ar }}</div>
                                            @endif
                                        </div>
                                        <div class="col-md-3">
                                            <div class="fw-bold text-gray-600 mb-1">Default Discount</div>
                                            <div class="fs-4 fw-bold text-success">
                                                <i class="fas fa-percentage"></i>
                                                {{ $tier->discount_percentage }}%
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="fw-bold text-gray-600 mb-1">Priority</div>
                                            <div class="fs-5 fw-bold text-gray-800">{{ $tier->priority }}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="fw-bold text-gray-600 mb-1">Status</div>
                                            <span class="badge badge-{{ $tier->is_active ? 'success' : 'danger' }} fs-7">
                                            {{ $tier->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Tier Info Card-->

                        <!--begin::Stats Card-->
                        <div class="col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex flex-column h-100">
                                        <div class="fw-bold text-gray-600 mb-3">Pricing Coverage</div>

                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-light-success fs-6">
                                                <i class="fas fa-check"></i>
                                                {{ $pricedItemsCount }} Custom
                                            </span>
                                                <span class="badge badge-light-warning fs-6">
                                                <i class="fas fa-clock"></i>
                                                {{ $unpricedItemsCount }} Default
                                            </span>
                                            </div>
                                            <div class="fs-2x fw-bold text-gray-800">
                                                {{ $pricedPercentage }}%
                                            </div>
                                        </div>

                                        <div class="progress h-8px w-100">
                                            <div class="progress-bar bg-{{ $pricedPercentage >= 75 ? 'success' : ($pricedPercentage >= 50 ? 'warning' : 'danger') }}"
                                                 role="progressbar"
                                                 style="width: {{ $pricedPercentage }}%"
                                                 aria-valuenow="{{ $pricedPercentage }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"></div>
                                        </div>

                                        <div class="text-muted fs-7 mt-2">
                                            {{ $pricedItemsCount }} of {{ $totalItems }} items have custom pricing
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Stats Card-->
                    </div>
                    <!--end::Stats Cards-->

                    <div class="card">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="fas fa-search fs-3 position-absolute ms-5"></i>
                                    <input type="text" wire:model.live.debounce.300ms="search"
                                           class="form-control form-control-solid w-250px ps-13"
                                           placeholder="Search items...">
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <div class="d-flex gap-2">
                                    <select wire:model.live="service_id" class="form-select form-select-solid w-200px">
                                        <option value="">All Services</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>

                                    <select wire:model.live="pricing_status" class="form-select form-select-solid w-150px">
                                        <option value="">All Items</option>
                                        <option value="priced">Custom Priced</option>
                                        <option value="default">Using Default</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Item</th>
                                        <th>Service</th>
                                        <th>Original Price</th>
                                        <th>Custom Price</th>
                                        <th>Discount %</th>
                                        <th>Final Price</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                    @forelse($items as $item)
                                        @php
                                            $customPrice = $item->tierPrices->first();
                                            $finalPrice = $tier->getPriceForItem($item->id);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->image)
                                                        <div class="symbol symbol-45px me-3">
                                                            <img src="{{ asset('uploads/' . $item->image) }}" alt="">
                                                        </div>
                                                    @endif
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold">{{ $item->name }}</span>
                                                        @if($item->name_ar)
                                                            <span class="text-muted fs-7">{{ $item->name_ar }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $item->service->name ?? '-' }}</td>
                                            <td>{{ number_format($item->price, 2) }} SAR</td>
                                            <td>
                                                @if($customPrice && $customPrice->custom_price)
                                                    <span class="badge badge-light-primary">
                                                    {{ number_format($customPrice->custom_price, 2) }} SAR
                                                </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($customPrice && $customPrice->discount_percentage)
                                                    <span class="badge badge-light-success">
                                                    {{ number_format($customPrice->discount_percentage, 2) }}%
                                                </span>
                                                @elseif(!$customPrice)
                                                    <span class="badge badge-light-info">
                                                    {{ number_format($tier->discount_percentage, 2) }}%
                                                </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                <span class="fw-bold text-success">
                                                    {{ number_format($finalPrice, 2) }} SAR
                                                </span>
                                                    @if($item->price != $finalPrice)
                                                        <span class="text-muted fs-7">
                                                        <i class="fas fa-arrow-down text-success"></i>
                                                        {{ number_format((($item->price - $finalPrice) / $item->price) * 100, 1) }}% off
                                                    </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($customPrice)
                                                    <span class="badge badge-{{ $customPrice->is_active ? 'success' : 'danger' }}">
                                                    {{ $customPrice->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                @else
                                                    <span class="badge badge-light-warning">
                                                    <i class="fas fa-star"></i> Default
                                                </span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    @if($customPrice)
                                                        <button wire:click="openModal({{ $item->id }}, {{ $customPrice->id }})"
                                                                class="btn btn-sm btn-light btn-active-light-primary">
                                                            <i class="fas fa-edit"></i>
                                                            Edit
                                                        </button>
                                                        <button wire:click="deletePrice({{ $customPrice->id }})"
                                                                class="btn btn-sm btn-light btn-active-light-danger"
                                                                wire:confirm="Remove custom price for this item?">
                                                            <i class="fas fa-trash-alt"></i>
                                                            Remove
                                                        </button>
                                                    @else
                                                        <button wire:click="openModal({{ $item->id }})"
                                                                class="btn btn-sm btn-light-success">
                                                            <i class="fas fa-plus"></i>
                                                            Set Price
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-10">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-box-open fs-3x text-muted mb-3"></i>
                                                    <span class="text-muted">No items found</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap pt-5">
                                <div class="fs-6 fw-semibold text-gray-700">
                                    Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }}
                                    of {{ $items->total() }} entries
                                </div>
                                {{ $items->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--begin::Price Modal-->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold">{{ $editingPriceId ? 'Edit' : 'Set' }} Item Price</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" wire:click="closeModal">
                            <i class="fas fa-times fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                        @if($selectedItem)
                            <div class="mb-7">
                                <div class="d-flex align-items-center mb-3">
                                    @if($selectedItem->image)
                                        <div class="symbol symbol-50px me-3">
                                            <img src="{{ asset('uploads/' . $selectedItem->image) }}" alt="">
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $selectedItem->name }}</div>
                                        <div class="text-muted fs-7">Original Price: {{ number_format($selectedItem->price, 2) }} SAR</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-5">
                                <div class="col-12">
                                    <label class="fs-6 fw-semibold mb-2">Custom Price (SAR)</label>
                                    <input type="number" step="1" wire:model="custom_price" class="form-control" placeholder="Leave empty to use discount %">
                                    @error('custom_price') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-12">
                                    <div class="text-center fw-bold text-muted my-3">OR</div>
                                </div>

                                <div class="col-12">
                                    <label class="fs-6 fw-semibold mb-2">Discount Percentage (%)</label>
                                    <input type="number" step="0.01" wire:model="discount_percentage" class="form-control" placeholder="10.00">
                                    @error('discount_percentage') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="fs-6 fw-semibold mb-2">Effective From</label>
                                    <input type="date" wire:model="effective_from" class="form-control">
                                    @error('effective_from') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="fs-6 fw-semibold mb-2">Effective Until</label>
                                    <input type="date" wire:model="effective_until" class="form-control">
                                    @error('effective_until') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="price_active">
                                        <label class="form-check-label" for="price_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2 mt-7">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="button" wire:click="savePrice" class="btn btn-base" wire:loading.attr="disabled">
                                <span wire:loading.remove>Save Price</span>
                                <span wire:loading>
                            Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!--end::Price Modal-->
</div>
