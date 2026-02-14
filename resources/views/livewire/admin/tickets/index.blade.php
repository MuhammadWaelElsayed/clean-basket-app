<div>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main" data-select2-id="select2-data-kt_app_main">
        <!--begin::Content wrapper-->
        <div class="d-flex flex-column flex-column-fluid" data-select2-id="select2-data-129-xgx3">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Tickets Management</h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" wire:navigate
                                    class="text-muted text-hover-primary">Home</a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">Tickets Management</li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                    <!--begin::Actions-->
                    <div class="d-flex align-partners-center gap-2 gap-lg-3">
                        <div class="card-toolbar">
                            @can('export_data')
                                <button type="button" class="btn btn-sm btn-light-primary" wire:click="exportData">
                                    <i class="fas fa-file-export"></i>
                                    Export
                                </button>
                            @endcan
                        </div>
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Toolbar container-->
            </div>

            <div id="kt_app_content" class="app-content flex-column-fluid"
                data-select2-id="select2-data-kt_app_content">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl"
                    data-select2-id="select2-data-kt_app_content_container">
                    <div class="card">
                        <div class="card-body">
                            <!-- Filters -->
                            <div class="row mb-5">
                                <div class="col-md-2">
                                    <input type="text" class="form-control" placeholder="Search tickets..."
                                        wire:model.live="search">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" wire:model.live="status">
                                        <option value="">All Status</option>
                                        <option value="open">Open</option>
                                        <option value="pending">Pending</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" wire:model.live="category_id">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" wire:model.live="sub_category_id">
                                        <option value="">All Sub Categories</option>
                                        @if ($category_id)
                                            @foreach ($categories->where('id', $category_id)->first()?->subCategories ?? [] as $subCategory)
                                                <option value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                {{-- <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Date range" wire:model.live="daterange" id="daterange">
                </div> --}}
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-secondary" wire:click="clearFilter">
                                        <i class="fas fa-broom"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Tickets Table -->
                            <div class="table-responsive">
                                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                    <thead>
                                        <tr class="fw-bold text-muted">
                                            <th class="w-25px">
                                                <div
                                                    class="form-check form-check-sm form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        data-kt-check="true" data-kt-check-target=".widget-9-check" />
                                                </div>
                                            </th>
                                            <th class="min-w-150px">Ticket Number</th>
                                            <th class="min-w-140px">Category</th>
                                            <th class="min-w-140px">Sub Category</th>
                                            <th class="min-w-120px">Order Code</th>
                                            <th class="min-w-120px">User</th>
                                            <th class="min-w-120px">Status</th>
                                            <th class="min-w-120px">Opened At</th>
                                            <th class="min-w-100px text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tickets as $ticket)
                                            <tr>
                                                <td>
                                                    <div
                                                        class="form-check form-check-sm form-check-custom form-check-solid">
                                                        <input class="form-check-input widget-9-check" type="checkbox"
                                                            value="1" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="#"
                                                                class="text-dark fw-bold text-hover-primary fs-6">{{ $ticket->ticket_number }}</a>
                                                            <span
                                                                class="text-muted fw-semibold text-muted d-block fs-7">
                                                                {{ Str::limit($ticket->description, 50) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                                        {{ $ticket->category ? $ticket->category->name : 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                                        {{ $ticket->subCategory ? $ticket->subCategory->name : 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                                        {{ $ticket->order_code ?: 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <span class="text-dark fw-bold text-hover-primary fs-6">
                                                                {{ $ticket->user ? $ticket->user->first_name . ' ' . $ticket->user->last_name : 'N/A' }}
                                                            </span>
                                                            <span
                                                                class="text-muted fw-semibold text-muted d-block fs-7">
                                                                {{ $ticket->user ? $ticket->user->email : 'N/A' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($ticket->status) {
                                                            'open' => 'badge badge-success',
                                                            'pending' => 'badge badge-warning',
                                                            'closed' => 'badge badge-secondary',
                                                            default => 'badge badge-info',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="{{ $statusClass }}">{{ ucfirst($ticket->status) }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                                        {{ $ticket->opened_at ? $ticket->opened_at->format('Y-m-d H:i') : 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end flex-shrink-0">
                                                        <button type="button"
                                                            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                                                            wire:click="gotoDetails('{{ $ticket->ticket_number }}')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        {{-- <button type="button" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1 updateStatusBtn"
                                                data-id="{{ $ticket->id }}" data-status="{{ $ticket->status }}">
                                                <i class="fas fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm deleteBtn"
                                                data-id="{{ $ticket->id }}">
                                                <i class="fas fa-trash"></i>
                                        </button> --}}
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No tickets found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="d-flex flex-wrap py-2 mr-3">
                                    {{ $tickets->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            // Initialize date range picker
            $(document).ready(function() {
                $('#daterange').daterangepicker({
                    opens: 'left',
                    locale: {
                        format: 'YYYY-MM-DD'
                    }
                });
            });

            // Sub Category filter
            Livewire.on('$refresh', () => {
                // Reset sub category when main category changes
                @this.set('sub_category_id', '');
            });

            // Update Status Buttons
            var updateStatusBtns = document.getElementsByClassName('updateStatusBtn');
            for (let i = 0; i < updateStatusBtns.length; i++) {
                updateStatusBtns[i].onclick = function(e) {
                    var id = $(this).data('id');
                    var currentStatus = $(this).data('status');
                    var newStatus = currentStatus === 'open' ? 'pending' : 'open';

                    e.preventDefault();
                    Swal.fire({
                        title: 'Update Ticket Status',
                        text: 'Do you want to change the status to ' + newStatus + '?',
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonColor: '#5E6278',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, update it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch('updateStatus', {
                                id: id,
                                status: newStatus
                            });
                        }
                    });
                };
            }

            // Delete Buttons
            var deleteBtns = document.getElementsByClassName('deleteBtn');
            for (let i = 0; i < deleteBtns.length; i++) {
                deleteBtns[i].onclick = function(e) {
                    var id = $(this).data('id');

                    e.preventDefault();
                    Swal.fire({
                        title: 'Delete Ticket',
                        text: 'Are you sure you want to delete this ticket? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonColor: '#5E6278',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch('deleteTicket', {
                                id: id
                            });
                        }
                    });
                };
            }
        </script>
    @endpush
</div>
