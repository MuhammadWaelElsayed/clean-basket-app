<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">

        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading text-dark fw-bold fs-3 my-0">Working Hours</h1>
                </div>
                <div class="d-flex gap-2">
                    <button wire:click="backToPartners" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Partners
                    </button>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div class="app-container container-xxl">
                <div class="card">
                    <div class="card-body">
                        <!-- Vendor Info -->
                        <div class="card bg-light p-3">
                            <h6 class="card-title">Vendor Information</h6>
                            <div class="row mb-4 justify-content-between align-items-center">
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Business Name:</strong> {{ $vendor->business_name }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Email:</strong> {{ $vendor->email }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-0"><strong>Phone:</strong> {{ $vendor->phone }}</p>
                                </div>
                                <div class="col-md-3 d-flex justify-content-end">
                                    <button wire:click="showAddForm" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Working Hours
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Working Hours Table -->
                        @if ($workingHours->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Open Time</th>
                                            <th>Close Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($workingHours as $hour)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-info">{{ $hour->day_ar }} -
                                                        {{ $hour->day_en }}</span>
                                                </td>
                                                <td>
                                                    @if ($hour->is_closed)
                                                        <span class="text-muted">Closed</span>
                                                    @else
                                                        {{ $hour->open_time ? $hour->open_time->format('H:i') : '-' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($hour->is_closed)
                                                        <span class="text-muted">Closed</span>
                                                    @else
                                                        {{ $hour->close_time ? $hour->close_time->format('H:i') : '-' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($hour->is_closed)
                                                        <span class="badge bg-danger">Closed</span>
                                                    @else
                                                        <span class="badge bg-success">Open</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button wire:click="showEditForm({{ $hour->id }})"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button wire:click="delete({{ $hour->id }})"
                                                        wire:confirm="Are you sure you want to delete working hours?"
                                                        class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No working hours recorded for this vendor</p>
                                <button wire:click="showAddForm" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Working Hours
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        @if ($showForm)
            <div class="modal fade show" style="display: block;" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $editingId ? 'Edit Working Hours' : 'Add New Working Hours' }}
                            </h5>
                            <button type="button" wire:click="cancel" class="btn-close"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="save">
                                <!-- Day Selection -->
                                <div class="mb-3">
                                    <label for="day_of_week" class="form-label">Day</label>
                                    <select wire:model.live="day_of_week" wire:change="daySelected" class="form-select"
                                        required>
                                        <option value="">-- Select Day --</option>
                                        @foreach ($daysOfWeek as $day)
                                            <option value="{{ $day['value'] }}">{{ $day['ar'] . ' - ' . $day['en'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('day_of_week')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Closed Checkbox -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input wire:model.live="is_closed" type="checkbox" class="form-check-input"
                                            id="is_closed">
                                        <label class="form-check-label" for="is_closed">
                                            Closed on this day
                                        </label>
                                    </div>
                                </div>

                                <!-- Time Fields (only if not closed) -->
                                @if (!$is_closed)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="open_time" class="form-label">Open Time</label>
                                                <input wire:model="open_time" type="time" class="form-control"
                                                    id="open_time" required>
                                                @error('open_time')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="close_time" class="form-label">Close Time</label>
                                                <input wire:model="close_time" type="time" class="form-control"
                                                    id="close_time" required>
                                                @error('close_time')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="cancel" class="btn btn-secondary">Cancel</button>
                            <button type="button" wire:click="save" class="btn btn-primary">
                                {{ $editingId ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
        @endif
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openTimeInput = document.getElementById('open_time');
        const closeTimeInput = document.getElementById('close_time');

        if (openTimeInput && closeTimeInput) {
            openTimeInput.addEventListener('change', function() {
                validateTimes();
            });

            closeTimeInput.addEventListener('change', function() {
                validateTimes();
            });

            function validateTimes() {
                const openTime = openTimeInput.value;
                const closeTime = closeTimeInput.value;

                if (openTime && closeTime && openTime >= closeTime) {
                    closeTimeInput.setCustomValidity('Close time must be after open time');
                    closeTimeInput.classList.add('is-invalid');
                } else {
                    closeTimeInput.setCustomValidity('');
                    closeTimeInput.classList.remove('is-invalid');
                }
            }
        }
    });
</script>
