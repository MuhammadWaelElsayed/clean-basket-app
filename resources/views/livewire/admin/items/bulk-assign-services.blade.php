@section('itemsActive','active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Bulk Assign Services</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/items') }}" class="text-muted text-hover-primary">Items</a>
                        </li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!--begin::Row-->
                <div class="row g-5 g-xl-10">
                    <!--begin::Col-->
                    <div class="col-md-8">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Select Items</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Choose items to assign services</span>
                                </h3>
                                <div class="card-toolbar">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-light-primary" wire:click="selectAllItems">
                                            <i class="ki-duotone ki-check fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light" wire:click="deselectAllItems">
                                            <i class="ki-duotone ki-cross fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Deselect All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body py-3">
                                <!--begin::Filters-->
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label class="fs-6 fw-semibold mb-2">Filter by Category</label>
                                        <select class="form-select form-select-solid" wire:model.live="categoryFilter">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fs-6 fw-semibold mb-2">Search Items</label>
                                        <input type="text" class="form-control form-control-solid"
                                               placeholder="Search by name..."
                                               wire:model.live.debounce.300ms="searchTerm">
                                    </div>
                                </div>
                                <!--end::Filters-->

                                <!--begin::Table container-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <!--begin::Table head-->
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th class="w-25px">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox"
                                                               wire:click="selectAllItems"
                                                               @if(count($selectedItems) > 0 && count($selectedItems) == $items->total()) checked @endif>
                                                    </div>
                                                </th>
                                                <th class="min-w-150px">Item Name</th>
                                                <th class="min-w-140px">Category</th>
                                                <th class="min-w-120px">Services Count</th>
                                            </tr>
                                        </thead>
                                        <!--end::Table head-->
                                        <!--begin::Table body-->
                                        <tbody>
                                            @foreach($items as $item)
                                            <tr>
                                                <td>
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox"
                                                               wire:model="selectedItems"
                                                               value="{{ $item->id }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-45px me-5">
                                                            <img src="{{ $item->image }}" alt="{{ $item->name }}">
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="#" class="text-dark fw-bold text-hover-primary fs-6">{{ $item->name }}</a>
                                                            <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $item->name_ar }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">{{ $item->category->name ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-primary fs-7 fw-bold">{{ $item->services->count() }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <!--end::Table body-->
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table container-->

                                @if($items->count() == 0)
                                <div class="text-center py-10">
                                    <i class="ki-duotone ki-search fs-3x text-muted mb-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <h3 class="text-muted mb-5">No Items Found</h3>
                                    <p class="text-muted fs-6">Try adjusting your search criteria or filters.</p>
                                </div>
                                @endif

                                <!--begin::Pagination-->
                                @if($items->hasPages())
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="d-flex flex-wrap py-2 mr-3">
                                        <div class="d-flex align-items-center py-3">
                                            <div class="d-flex align-items-center">
                                                <label class="mr-3 mb-0 d-none d-md-block text-muted">Show:</label>
                                                <select class="form-select form-select-sm form-select-solid w-75px" wire:model.live="perPage">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center py-3">
                                        {{ $items->links() }}
                                    </div>
                                </div>
                                @endif
                                <!--end::Pagination-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-md-4">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Services Configuration</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Select services and set prices</span>
                                </h3>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body py-3">
                                <form wire:submit.prevent="bulkAssignServices">
                                    <!--begin::Selected Items Count-->
                                    <div class="alert alert-info mb-5">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-information-5 fs-2hx text-info me-4">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            <div class="d-flex flex-column">
                                                <h4 class="mb-1">Selected Items</h4>
                                                <span>{{ count($selectedItems) }} items selected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Selected Items Count-->

                                    <!--begin::Services Section-->
                                    <div class="mb-5">
                                        <label class="required fs-6 fw-semibold mb-2">Available Services</label>
                                        <div class="card card-flush">
                                            <div class="card-body">
                                                @foreach($serviceTypes as $serviceType)
                                                <div class="mb-3">
                                                    <div class="form-check form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox"
                                                               wire:model="selectedServices"
                                                               value="{{ $serviceType->id }}"
                                                               id="service_{{ $serviceType->id }}">
                                                        <label class="form-check-label" for="service_{{ $serviceType->id }}">
                                                            {{ $serviceType->name }} ({{ $serviceType->name_ar }})
                                                        </label>
                                                    </div>

                                                    @if(in_array($serviceType->id, $selectedServices))
                                                    <div class="mt-2">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label class="fs-7 fw-semibold text-muted">Price</label>
                                                                <input type="number" step="0.01" class="form-control form-control-sm"
                                                                       placeholder="Price"
                                                                       wire:model="servicePrices.{{ $serviceType->id }}">
                                                                @error("servicePrices.{$serviceType->id}")
                                                                    <span class="text-danger fs-8">{{$message}}</span>
                                                                @enderror
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="fs-7 fw-semibold text-muted">Discount Price</label>
                                                                <input type="number" step="0.01" class="form-control form-control-sm"
                                                                       placeholder="Discount Price"
                                                                       wire:model="serviceDiscountPrices.{{ $serviceType->id }}">
                                                                @error("serviceDiscountPrices.{$serviceType->id}")
                                                                    <span class="text-danger fs-8">{{$message}}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @error('selectedServices') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>
                                    <!--end::Services Section-->

                                    <!--begin::Actions-->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="indicator-label" wire:loading.remove>Add Services</span>
                                            <span class="indicator-progress" wire:loading>Please wait...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>

                                        <button type="button" class="btn btn-warning" wire:click="bulkReplaceServices">
                                            <span class="indicator-label" wire:loading.remove>Replace All Services</span>
                                            <span class="indicator-progress" wire:loading>Please wait...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>

                                        <button type="button" class="btn btn-light" wire:click="resetForm">
                                            <i class="ki-duotone ki-refresh fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Reset Form
                                        </button>
                                    </div>
                                    <!--end::Actions-->
                                </form>
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
</div>
