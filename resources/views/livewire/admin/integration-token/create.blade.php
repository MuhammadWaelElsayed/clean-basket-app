@section('integrationTokensActive','active')
@section('dataShow','show')

<div>
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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        {{ $eId ? 'Edit Integration Token' : 'Add New Integration Token' }}
                    </h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" wire:navigate class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/integration-tokens') }}" wire:navigate class="text-muted text-hover-primary">Integration Tokens</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">{{ $eId ? 'Edit' : 'Create' }}</li>
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
                        <div class="card-title">
                            <h3 class="fw-bold m-0">{{ $eId ? 'Edit Integration Token' : 'Create New Integration Token' }}</h3>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <form wire:submit.prevent="store">
                            <div class="row">
                                <!--begin::Name-->
                                <div class="col-md-6 mb-5">
                                    <label class="form-label required">Token Name</label>
                                    <input type="text" wire:model="name" class="form-control form-control-solid @error('name') is-invalid @enderror"
                                           placeholder="Enter token name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!--end::Name-->

                                <!--begin::Provider-->
                                <div class="col-md-6 mb-5">
                                    <label class="form-label required">Provider</label>
                                    <input type="text" wire:model="provider" class="form-control form-control-solid @error('provider') is-invalid @enderror"
                                           placeholder="Enter provider name (e.g., webhook, api)">
                                    @error('provider')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!--end::Provider-->

                                <!--begin::Scopes-->
                                <div class="col-12 mb-5">
                                    <label class="form-label">Scopes</label>
                                    <div class="scopes-container">
                                        @foreach($scopes as $index => $scope)
                                            <div class="d-flex align-items-center mb-2">
                                                <input type="text"
                                                       wire:model="scopes.{{ $index }}"
                                                       class="form-control form-control-solid @error('scopes.'.$index) is-invalid @enderror"
                                                       placeholder="Enter scope (e.g., webhook:write)">
                                                @if(count($scopes) > 1)
                                                    <button type="button"
                                                            wire:click="removeScope({{ $index }})"
                                                            class="btn btn-sm btn-light-danger ms-2">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                        <button type="button"
                                                wire:click="addScope"
                                                class="btn btn-sm btn-light-primary">
                                            <i class="fas fa-plus"></i> Add Scope
                                        </button>
                                    </div>
                                    @error('scopes')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!--end::Scopes-->

                                <!--begin::Status-->
                                <div class="col-md-6 mb-5">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               wire:model="is_active"
                                               id="is_active">
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                                <!--end::Status-->

                                <!--begin::Expires At-->
                                <div class="col-md-6 mb-5">
                                    <label class="form-label">Expires At</label>
                                    <input type="datetime-local"
                                           wire:model="expires_at"
                                           class="form-control form-control-solid @error('expires_at') is-invalid @enderror">
                                    @error('expires_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Leave empty for no expiration</div>
                                </div>
                                <!--end::Expires At-->
                            </div>

                            <!--begin::Generated Token Display-->
                            @if($showToken && $generatedToken)
                                <div class="alert alert-warning d-flex align-items-center p-5 mb-10">
                                    <i class="fas fa-exclamation-triangle text-warning me-3 fs-2x"></i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-warning">Important: Save Your Token</h4>
                                        <span>This token will only be shown once. Please copy and save it securely.</span>
                                        <div class="mt-3">
                                            <div class="input-group">
                                                <input type="text"
                                                       value="{{ $generatedToken }}"
                                                       class="form-control"
                                                       id="generatedToken"
                                                       readonly>
                                                <button type="button"
                                                        class="btn btn-light-primary"
                                                        onclick="copyToken()">
                                                    <i class="fas fa-copy"></i> Copy
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button"
                                                wire:click="hideToken"
                                                class="btn btn-sm btn-light-warning mt-2">
                                            Hide Token
                                        </button>
                                    </div>
                                </div>
                            @endif
                            <!--end::Generated Token Display-->

                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end">
                                <a href="{{ url('admin/integration-tokens') }}"
                                   wire:navigate
                                   class="btn btn-light me-5">
                                    Cancel
                                </a>
                                <button type="submit"
                                        class="btn btn-primary">
                                    {{ $eId ? 'Update Token' : 'Create Token' }}
                                </button>
                            </div>
                            <!--end::Actions-->
                        </form>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
</div>

@section('scripts')
<script>
    function copyToken() {
        const tokenInput = document.getElementById('generatedToken');
        tokenInput.select();
        tokenInput.setSelectionRange(0, 99999); // For mobile devices

        try {
            document.execCommand('copy');
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Copied!',
                    text: 'Token copied to clipboard',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } catch (err) {
            console.error('Failed to copy token: ', err);
        }
    }

    // Auto-hide token after 30 seconds
    @if($showToken && $generatedToken)
        setTimeout(function() {
            @this.call('hideToken');
        }, 30000);
    @endif
</script>
@endsection
</div>
