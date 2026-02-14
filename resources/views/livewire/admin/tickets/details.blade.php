<div>
    <!-- Alert Messages -->
    @if (session()->has('success'))
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <i class="ki-duotone ki-check-circle fs-2 me-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ki-duotone ki-cross-circle fs-2 me-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
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
                            Ticket Details</h1>
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
                            <li class="breadcrumb-item text-muted">Ticket Details</li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
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
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $ticket->ticket_number }}</span>
                                        <span
                                            class="{{ $this->getStatusBadgeClass($ticket->status) }}">{{ ucfirst($ticket->status) }}</span>
                                    </div>
                                    <span
                                        class="text-gray-400 pt-1 fw-semibold fs-6">{{ $ticket->category ? $ticket->category->name : 'No Category' }}</span>
                                    {{-- <div class="card-body pt-2 pb-4 d-flex align-items-center"> --}}
                                    <div class="d-flex flex-column content-justify-center flex-row-fluid">
                                        <div class="d-flex fw-semibold align-items-center fs-6 text-gray-400 mb-2">
                                            <span class="bullet bullet-dot bg-gray-400 w-5px h-2px me-3"></span>
                                            Opened:
                                            {{ $ticket->opened_at ? $ticket->opened_at->format('Y-m-d H:i:s') : 'N/A' }}
                                        </div>
                                        <div class="d-flex fw-semibold align-items-center fs-6 text-gray-400 mb-2">
                                            <span class="bullet bullet-dot bg-gray-400 w-5px h-2px me-3"></span>
                                            Created:
                                            {{ $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                        </div>
                                        <div class="d-flex fw-semibold align-items-center fs-6 text-gray-400">
                                            <span class="bullet bullet-dot bg-gray-400 w-5px h-2px me-3"></span>
                                            Updated:
                                            {{ $ticket->updated_at ? $ticket->updated_at->format('Y-m-d H:i:s') : 'N/A' }}
                                        </div>
                                    </div>
                                    <!-- Description -->
                                    <div class="card-flush h-md-50 mb-5 mb-xl-10">
                                        <div class="pt-5">
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">Description</span>
                                            </div>
                                        </div>
                                        <div class="pt-2 pb-4">
                                            <div class="d-flex flex-column content-justify-center flex-row-fluid">
                                                <p class="text-gray-600 fs-6">
                                                    {{ $ticket->description ?: 'No description provided' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-4">
                                    <!-- User Information -->
                                    <div class="card-flush mb-5 mb-xl-10">
                                        <div class="pt-5">
                                            <div class="d-flex flex-column">
                                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">User
                                                    Information</span>
                                            </div>
                                        </div>
                                        <div class="pt-2 pb-4">
                                            @if ($ticket->user)
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="symbol symbol-50px me-3">
                                                        <img src="{{ $ticket->user->picture }}" alt="User Avatar">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span
                                                            class="text-gray-800 text-hover-primary mb-1 fs-6 fw-bold">
                                                            {{ $ticket->user->first_name }}
                                                            {{ $ticket->user->last_name }}
                                                        </span>
                                                        <span
                                                            class="text-gray-400 fw-semibold d-block fs-7">{{ $ticket->user->email }}</span>
                                                    </div>
                                                </div>
                                                <div
                                                    class="d-flex fw-semibold align-items-center fs-6 text-gray-400 mb-2">
                                                    <span class="bullet bullet-dot bg-gray-400 w-5px h-2px me-3"></span>
                                                    Phone: {{ $ticket->user->phone ?: 'N/A' }}
                                                </div>
                                                <div class="d-flex fw-semibold align-items-center fs-6 text-gray-400">
                                                    <span class="bullet bullet-dot bg-gray-400 w-5px h-2px me-3"></span>
                                                    Member since:
                                                    {{ $ticket->user->created_at ? $ticket->user->created_at->format('Y-m-d') : 'N/A' }}
                                                </div>
                                            @else
                                                <p class="text-gray-400">User information not available</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Update Status and Note -->
                                    <div class="card-flush mb-5 mb-xl-10">
                                        <div class="pt-5">
                                            <div class="d-flex flex-column">
                                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">Update Status &
                                                    Note</span>
                                            </div>
                                        </div>
                                        <div class="pt-2 pb-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" wire:model="newStatus">
                                                    <option value="open">Open</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="closed">Closed</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="form-label">Note</label>
                                                <textarea class="form-control" wire:model="note" rows="3" placeholder="Enter note...">{{ $ticket->note }}</textarea>
                                            </div>
                                            <button type="button" class="btn btn-primary w-100"
                                                wire:click="updateStatusAndNotes">
                                                Update Status & Note
                                            </button>
                                        </div>
                                    </div>
                                </div>
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
        // Auto-hide alert messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
@endpush
