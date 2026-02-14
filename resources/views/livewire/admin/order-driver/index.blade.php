@section('orderDriversActive', 'active')

<div>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">External Drivers Management</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">External Drivers</li>
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
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search..." />
                            </div>
                            <!--end::Search-->
                        </div>
                        <!--begin::Card title-->

                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Toolbar-->
                            <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                                <!--begin::Filter-->
                                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-filter fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Filter
                                </button>
                                <!--begin::Menu 1-->
                                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                    <!--begin::Header-->
                                    <div class="px-7 py-5">
                                        <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
                                    </div>
                                    <!--end::Header-->

                                    <!--begin::Separator-->
                                    <div class="separator border-gray-200"></div>
                                    <!--end::Separator-->

                                    <!--begin::Content-->
                                    <div class="px-7 py-5" data-kt-customer-table-filter="form">
                                        <!--begin::Input group-->
                                        <div class="mb-10">
                                            <label class="form-label fs-6 fw-semibold">Status:</label>
                                            <select wire:model.live="statusFilter" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Select Status" data-allow-clear="true">
                                                <option value="">All Statuses</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}">{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div class="mb-10">
                                            <label class="form-label fs-6 fw-semibold">Provider:</label>
                                            <select wire:model.live="providerFilter" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Select Provider" data-allow-clear="true">
                                                <option value="">All Providers</option>
                                                @foreach($providers as $provider)
                                                    <option value="{{ $provider }}">{{ $provider }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Actions-->
                                        <div class="d-flex justify-content-end">
                                            <button type="reset" wire:click="$set('statusFilter', '')" wire:click="$set('providerFilter', '')" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-customer-table-filter="reset">Reset</button>
                                        </div>
                                        <!--end::Actions-->
                                    </div>
                                    <!--end::Content-->
                                </div>
                                <!--end::Menu 1-->
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
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customers_table">
                            <!--begin::Table head-->
                            <thead>
                                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-125px">Ride ID</th>
                                    <th class="min-w-125px">Order Number</th>
                                    <th class="min-w-125px">Driver</th>
                                    <th class="min-w-125px">Provider</th>
                                    <th class="min-w-125px">Status</th>
                                    <th class="min-w-125px">Cost</th>
                                    <th class="min-w-125px">Changed Date</th>
                                    <th class="text-end min-w-70px">Actions</th>
                                </tr>
                            </thead>
                            <!--end::Table head-->

                            <!--begin::Table body-->
                            <tbody class="fw-semibold text-gray-600">
                                @forelse($orderDrivers as $orderDriver)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold">{{ $orderDriver->external_ride_id }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($orderDriver->order)
                                                <a href="{{ route('admin.orders.edit', $orderDriver->order->id) }}" class="text-primary fw-bold">
                                                    {{ $orderDriver->order->order_code }}
                                                </a>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($orderDriver->driver)
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold">{{ $orderDriver->driver->name }}</span>
                                                    @if($orderDriver->driver->phone)
                                                        <span class="text-muted fs-7">{{ $orderDriver->driver->phone }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light-info">{{ $orderDriver->provider ?? 'غير محدد' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'ACCEPTED' => 'success',
                                                    'ARRIVING' => 'warning',
                                                    'ARRIVED' => 'primary',
                                                    'IN_PROGRESS' => 'info',
                                                    'COMPLETED' => 'success',
                                                    'CANCELLED' => 'danger',
                                                    'UNKNOWN' => 'secondary'
                                                ];
                                                $color = $statusColors[$orderDriver->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-light-{{ $color }}">{{ $orderDriver->status }}</span>
                                        </td>
                                        <td>
                                            @if($orderDriver->trip_cost)
                                                <span class="text-gray-800 fw-bold">{{ number_format($orderDriver->trip_cost, 2) }} SAR</span>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($orderDriver->time_changed)
                                                <span class="text-gray-800">{{ \Carbon\Carbon::parse($orderDriver->time_changed)->format('Y-m-d H:i') }}</span>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.order-drivers.show', $orderDriver->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                                <i class="ki-duotone ki-eye fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-10">
                                            <div class="text-gray-600">No data available</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <!--end::Table body-->
                        </table>
                        <!--end::Table-->

                        <!--begin::Pagination-->
                        <div class="d-flex flex-stack flex-wrap pt-10">
                            <div class="fs-6 fw-semibold text-gray-700">
                                Showing {{ $orderDrivers->firstItem() ?? 0 }} to {{ $orderDrivers->lastItem() ?? 0 }} of {{ $orderDrivers->total() }} results
                            </div>
                            <ul class="pagination">
                                {{ $orderDrivers->links() }}
                            </ul>
                        </div>
                        <!--end::Pagination-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
</div>
