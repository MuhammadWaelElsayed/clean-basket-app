@section('walletManualWithdrawal', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Manual Wallet Withdrawal</h1>
                </div>
                <!--end::Page title-->
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body">

                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="submit">
                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Select User</label>
                                    <div id="user_select" wire:ignore></div>
                                    @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Amount ({{ env('CURRENCY', 'SAR') }})</label>
                                    <input type="number" step="0.01" wire:model="amount" class="form-control" placeholder="Enter amount">
                                    @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label fw-bold">Description (optional)</label>
                                <textarea wire:model="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-dash-circle me-2"></i>
                                    Withdraw
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
</div>

@section('scripts')
<script>
    function initUserSelect() {
        if (window.VirtualSelect && document.getElementById('user_select')) {
            VirtualSelect.init({
                ele: '#user_select',
                options: @json($users_arr),
                placeholder: 'Select User',
                search: true,
                multiple: false,
                additionalClasses: 'filter-field',
                selectedValue: @json($user_id),
            });
            document.getElementById('user_select').addEventListener('change', function(e) {
                @this.set('user_id', this.value);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initUserSelect);

    document.addEventListener('livewire:load', function () {
        Livewire.hook('message.processed', (message, component) => {
            initUserSelect();
        });
    });
</script>
@endsection
