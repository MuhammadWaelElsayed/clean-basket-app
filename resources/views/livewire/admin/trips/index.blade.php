<div>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Trips Management
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Trips</li>
                        </ul>
                    </div>

                    <!--begin::Stats-->
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-light-primary rounded p-3 d-flex align-items-center">
                            <i class="fas fa-truck text-primary fs-2x me-3"></i>
                            <div>
                                <div class="fs-7 text-muted">Total Trips</div>
                                <div class="fs-4 fw-bold text-primary">{{ $trips->total() }}</div>
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
                                           placeholder="Search trips, orders, clients...">
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Card title-->

                            <!--begin::Card toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Toolbar-->
                                <div class="d-flex justify-content-end gap-2 flex-wrap" data-kt-trips-table-toolbar="base">
                                    <!--begin::Filter-->
                                    <select wire:model.live="type" class="form-select form-select-solid w-150px">
                                        <option value="">All Types</option>
                                        <option value="pickup">Pickup</option>
                                        <option value="delivery">Delivery</option>
                                    </select>

                                    <select wire:model.live="status" class="form-select form-select-solid w-150px">
                                        <option value="">All Status</option>
                                        <option value="new">New</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="in-progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="rescheduled">Rescheduled</option>
                                    </select>

                                    <select wire:model.live="is_picked_up" class="form-select form-select-solid w-150px">
                                        <option value="">All Pickup Status</option>
                                        <option value="1">Picked Up</option>
                                        <option value="0">Not Picked Up</option>
                                    </select>

                                    @if(isset($drivers))
                                        <select wire:model.live="driver_id" class="form-select form-select-solid w-200px">
                                            <option value="">All Drivers</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif

                                    @if(isset($clients))
                                        <select wire:model.live="client_id" class="form-select form-select-solid w-200px">
                                            <option value="">All Clients</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif

                                    <!--begin::Export-->
{{--                                    <button class="btn btn-light-primary">--}}
{{--                                        <i class="fas fa-download me-2"></i>--}}
{{--                                        Export--}}
{{--                                    </button>--}}
                                    <!--end::Export-->
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
                                        <th wire:click="sortBy('id')" style="cursor: pointer;" class="min-w-50px">
                                            ID
                                            @if($sortField === 'id')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('order_id')" style="cursor: pointer;" class="min-w-100px">
                                            Order
                                            @if($sortField === 'order_id')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th class="min-w-150px">Client</th>
                                        <th class="min-w-150px">Driver</th>
                                        <th wire:click="sortBy('type')" style="cursor: pointer;" class="min-w-100px">
                                            Type
                                            @if($sortField === 'type')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('status')" style="cursor: pointer;" class="min-w-120px">
                                            Status
                                            @if($sortField === 'status')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </th>
                                        <th class="min-w-100px">Distance</th>
                                        <th class="min-w-100px">Duration</th>
                                        <th class="min-w-80px">Picked Up</th>
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
                                    @forelse($trips as $trip)
                                        <tr>
                                            <td>
                                                <span class="badge badge-light-dark">#{{ $trip->id }}</span>
                                            </td>
                                            <td>
                                                {{ $trip->order->order_code }}
{{--                                                @if($trip->order)--}}
{{--                                                    <a href="{{ route('admin.orders.show', $trip->order_id) }}"--}}
{{--                                                       class="text-gray-800 text-hover-primary fw-bold">--}}
{{--                                                        {{ $trip->order->order_code }}--}}
{{--                                                    </a>--}}
{{--                                                @else--}}
{{--                                                    <span class="text-muted">-</span>--}}
{{--                                                @endif--}}
                                            </td>
                                            <td>
                                                @if($trip->client)
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px symbol-circle me-3">
                                                            <div class="symbol-label bg-light-primary text-primary fw-bold">
                                                                {{ substr($trip->client->name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 fw-bold">{{ $trip->client->name }}</span>
                                                            @if($trip->client->phone)
                                                                <span class="text-muted fs-7">{{ $trip->client->phone }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($trip->driver)
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px symbol-circle me-3">
                                                            <div class="symbol-label bg-light-success text-success fw-bold">
                                                                {{ substr($trip->driver->name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 fw-bold">{{ $trip->driver->name }}</span>
                                                            @if($trip->driver->phone)
                                                                <span class="text-muted fs-7">{{ $trip->driver->phone }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="badge badge-light-warning">Unassigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($trip->type === 'pickup')
                                                    <span class="badge badge-light-info">
                                                        <i class="fas fa-arrow-up me-1"></i>
                                                        Pickup
                                                    </span>
                                                @elseif($trip->type === 'delivery')
                                                    <span class="badge badge-light-primary">
                                                        <i class="fas fa-arrow-down me-1"></i>
                                                        Delivery
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($trip->status)
                                                    @case('new')
                                                        <span class="badge badge-light-info">New</span>
                                                        @break
                                                    @case('scheduled')
                                                        <span class="badge badge-light-primary">Scheduled</span>
                                                        @break
                                                    @case('assigned')
                                                        <span class="badge badge-light-secondary">Assigned</span>
                                                        @break
                                                    @case('in-progress')
                                                        <span class="badge badge-light-warning">In Progress</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge badge-light-success">Completed</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge badge-light-danger">Cancelled</span>
                                                        @break
                                                    @case('rescheduled')
                                                        <span class="badge badge-light-dark">Rescheduled</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($trip->distance_km)
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold">
                                                            <i class="fas fa-route text-primary me-1"></i>
                                                            {{ number_format($trip->distance_km, 2) }} km
                                                        </span>
                                                        <span class="text-muted fs-7">
                                                            {{ number_format($trip->distance_km * 0.621371, 2) }} mi
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($trip->started_at && $trip->completed_at)
                                                    @php
                                                        $duration = \Carbon\Carbon::parse($trip->started_at)->diffInMinutes($trip->completed_at);
                                                    @endphp
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold">
                                                            <i class="fas fa-clock text-warning me-1"></i>
                                                            @if($duration >= 60)
                                                                {{ floor($duration / 60) }}h {{ $duration % 60 }}m
                                                            @else
                                                                {{ $duration }}m
                                                            @endif
                                                        </span>
                                                    </div>
                                                @elseif($trip->started_at)
                                                    <span class="badge badge-light-warning">In Progress</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @can('manage_trips')
                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox"
                                                               wire:click="togglePickedUp({{ $trip->id }})"
                                                            {{ $trip->is_picked_up ? 'checked' : '' }}>
                                                    </div>
                                                @else
                                                    @if($trip->is_picked_up)
                                                        <span class="badge badge-light-success">
                                                            <i class="fas fa-check me-1"></i>
                                                            Yes
                                                        </span>
                                                    @else
                                                        <span class="badge badge-light-secondary">
                                                            <i class="fas fa-times me-1"></i>
                                                            No
                                                        </span>
                                                    @endif
                                                @endcan
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7">
                                                        {{ $trip->created_at->format('d M Y') }}
                                                    </span>
                                                    <span class="text-muted fs-8">
                                                        {{ $trip->created_at->format('h:i A') }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    @can('manage_trips')
                                                        <a href="{{ route('admin.trips.show', $trip->id) }}"
                                                           class="btn btn-sm btn-icon btn-light btn-active-light-primary"
                                                           data-bs-toggle="tooltip"
                                                           title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        @if($trip->status === 'new')
                                                            <button wire:click="scheduleTrip({{ $trip->id }})"
                                                                    class="btn btn-sm btn-icon btn-light btn-active-light-primary"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Schedule">
                                                                <i class="fas fa-calendar"></i>
                                                            </button>
                                                        @endif

                                                        @if($trip->status === 'scheduled' && !$trip->driver_id)
                                                            <button wire:click="assignDriver({{ $trip->id }})"
                                                                    class="btn btn-sm btn-icon btn-light btn-active-light-secondary"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Assign Driver">
                                                                <i class="fas fa-user-plus"></i>
                                                            </button>
                                                        @endif

                                                        @if(in_array($trip->status, ['assigned', 'scheduled']) && $trip->driver_id)
                                                            <button wire:click="startTrip({{ $trip->id }})"
                                                                    class="btn btn-sm btn-icon btn-light btn-active-light-warning"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Start Trip">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        @endif

                                                        @if($trip->status === 'in-progress')
                                                            <button wire:click="markAsCompleted({{ $trip->id }})"
                                                                    class="btn btn-sm btn-icon btn-light btn-active-light-success"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Mark as Completed">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        @endif

                                                        @if(!in_array($trip->status, ['completed', 'cancelled']))
                                                            <button wire:click="cancelTrip({{ $trip->id }})"
                                                                    class="btn btn-sm btn-icon btn-light btn-active-light-danger"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Cancel Trip">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-10">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="ki-duotone ki-truck fs-5x text-muted mb-5">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    <span class="text-muted fs-5 fw-bold">No trips found</span>
                                                    <span class="text-muted fs-7 mt-2">Try adjusting your filters</span>
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
                                    Showing {{ $trips->firstItem() ?? 0 }} to {{ $trips->lastItem() ?? 0 }}
                                    of {{ $trips->total() }} entries
                                </div>
                                {{ $trips->links() }}
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
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            @this.on('trip-completed', (event) => {
                Swal.fire({
                    title: 'Success!',
                    text: 'Trip has been marked as completed.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            });

            @this.on('trip-scheduled', (event) => {
                Swal.fire({
                    title: 'Success!',
                    text: 'Trip has been scheduled.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            });

            @this.on('trip-started', (event) => {
                Swal.fire({
                    title: 'Trip Started!',
                    text: 'Trip has been started successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            });

            @this.on('trip-cancelled', (event) => {
                Swal.fire({
                    title: 'Cancelled',
                    text: 'Trip has been cancelled.',
                    icon: 'info',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            });

            @this.on('driver-assigned', (event) => {
                Swal.fire({
                    title: 'Success!',
                    text: 'Driver has been assigned to the trip.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            });

            @this.on('picked-up-toggled', (event) => {
                Swal.fire({
                    title: 'Updated!',
                    text: event[0].message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 1500
                });
            });
        });
    </script>
@endpush
