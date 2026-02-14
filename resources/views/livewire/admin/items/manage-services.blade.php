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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Manage Item Services</h1>
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
                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                    <!--begin::Col-->
                    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                        <!--begin::Card-->
                        <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                            <!--begin::Header-->
                            <div class="card-header pt-5">
                                <!--begin::Title-->
                                <div class="card-title d-flex flex-column">
                                    <!--begin::Amount-->
                                    <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $items->total() }}</span>
                                    <!--end::Amount-->
                                    <!--begin::Subtitle-->
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Total Items</span>
                                    <!--end::Subtitle-->
                                </div>
                                <!--end::Title-->
                            </div>
                            <!--end::Header-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                        <!--begin::Card-->
                        <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                            <!--begin::Header-->
                            <div class="card-header pt-5">
                                <!--begin::Title-->
                                <div class="card-title d-flex flex-column">
                                    <!--begin::Amount-->
                                    <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $serviceTypes->count() }}</span>
                                    <!--end::Amount-->
                                    <!--begin::Subtitle-->
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Available Services</span>
                                    <!--end::Subtitle-->
                                </div>
                                <!--end::Title-->
                            </div>
                            <!--end::Header-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-5 g-xl-10">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Select Item</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Choose an item to manage its services</span>
                                </h3>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body py-3">
                                <!--begin::Search-->
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label class="fs-6 fw-semibold mb-2">Search Items</label>
                                        <input type="text" class="form-control form-control-solid"
                                               placeholder="Search by name..."
                                               wire:model.live.debounce.300ms="searchTerm">
                                    </div>
                                </div>
                                <!--end::Search-->

                                <!--begin::Table container-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <!--begin::Table head-->
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th class="min-w-150px">Item Name</th>
                                                <th class="min-w-140px">Category</th>
                                                <th class="min-w-120px">Services Count</th>
                                                <th class="min-w-100px text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <!--end::Table head-->
                                        <!--begin::Table body-->
                                        <tbody>
                                            @foreach($items as $item)
                                            <tr>
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
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-light-primary" wire:click="selectItem({{ $item->id }})">
                                                        <i class="ki-duotone ki-gear fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        Manage
                                                    </button>
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
                                    <p class="text-muted fs-6">Try adjusting your search criteria.</p>
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
                    <div class="col-md-6">
                        @if($showForm && $item)
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Manage Services for: {{ $item->name }}</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Assign services and set prices</span>
                                </h3>
                                <div class="card-toolbar">
                                    <button type="button" class="btn btn-sm btn-light" wire:click="resetForm">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Close
                                    </button>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body py-3">
                                <form wire:submit.prevent="assignServices">
                                    <!--begin::Services Section-->
                                    <div class="mb-5">
                                        <label class="required fs-6 fw-semibold mb-2">Available Services</label>
                                        <div class="card card-flush">
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($serviceTypes as $serviceType)
                                                    <div class="col-md-6 mb-3">
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
                                        </div>
                                        @error('selectedServices') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>
                                    <!--end::Services Section-->

                                    <!--begin::Current Services-->
                                    @if($item->services->count() > 0)
                                    <div class="mb-5">
                                        <label class="fs-6 fw-semibold mb-2">Current Services</label>
                                        <div class="table-responsive">
                                            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                                <thead>
                                                    <tr class="fw-bold text-muted">
                                                        <th class="min-w-150px">Service</th>
                                                        <th class="min-w-100px">Price</th>
                                                        <th class="min-w-100px">Discount</th>
                                                        <th class="min-w-100px text-end">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($item->services as $service)
                                                    <tr>
                                                        <td>
                                                            <span class="text-dark fw-bold text-hover-primary d-block fs-6">{{ $service->name }}</span>
                                                            <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $service->name_ar }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-dark fw-bold d-block fs-6">{{ $service->pivot->price }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-muted fw-semibold d-block fs-6">{{ $service->pivot->discount_price ?? 'N/A' }}</span>
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-light-danger" wire:click="removeService({{ $service->id }})">
                                                                <i class="ki-duotone ki-trash fs-2">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                    <span class="path3"></span>
                                                                </i>
                                                                Remove
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endif
                                    <!--end::Current Services-->

                                    <!--begin::Actions-->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="indicator-label" wire:loading.remove>Assign Services</span>
                                            <span class="indicator-progress" wire:loading>Please wait...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                    <!--end::Actions-->
                                </form>
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                        @else
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card body-->
                            <div class="card-body d-flex flex-column align-items-center justify-content-center py-10">
                                <div class="text-center">
                                    <i class="ki-duotone ki-gear fs-3x text-muted mb-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <h3 class="text-muted mb-5">Select an Item</h3>
                                    <p class="text-muted fs-6">Choose an item from the list to manage its services and pricing.</p>
                                </div>
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                        @endif
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
