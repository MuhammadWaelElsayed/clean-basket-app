@section('integrationTokensActive','active')
@section('dataShow','show')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Integration Tokens</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Service-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" wire:navigate class="text-muted text-hover-primary">Home</a>
                        </li>
                        <!--end::Service-->
                        <!--begin::Service-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Service-->
                        <!--begin::Service-->
                        <li class="breadcrumb-item text-muted">Integration Tokens</li>
                        <!--end::Service-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ url('admin/integration-tokens/create') }}" wire:navigate class="btn btn-base btn-sm">Add New Token</a>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl" data-select2-id="select2-data-kt_app_content_container">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <!--begin::Search-->
                            <form action="{{ url('admin/integration-tokens') }}" method="get">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                                    <span class="svg-icon svg-icon-1 position-absolute ms-6 mt-3">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
                                            <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"></path>
                                        </svg>
                                    </span>
                                    <!--end::Svg Icon-->
                                    <input type="text" wire:model.live="search" data-kt-token-table-filter="search" class="form-control form-control-solid w-350px ps-15 me-5"
                                    placeholder="Search Token Name, Provider or Hint.." >
                                    <div class="position-relative d-flex align-items-center">
                                        <!--begin::Filter-->
                                        <select wire:model.live="providerFilter" class="form-select form-select-solid w-150px me-3">
                                            <option value="">All Providers</option>
                                            @foreach($this->getProviders() as $provider)
                                                <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                                            @endforeach
                                        </select>
                                        <!--end::Filter-->
                                        <input class="form-control form-control-solid ps-12" wire:model.live.debounce.2000ms="daterange" id="daterange" autocomplete="off" placeholder="Select Date Range">
                                        <button type="button" wire:click="clearFilter()" class="btn {{($search!=='' || $daterange!=='' || $providerFilter!=='')?'d-block':'d-none'}} btn-sm btn-light-primary mx-3">
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <!--end::Search-->
                        </div>
                        <div class="card-toolbar">

                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <div id="kt_tokens_table_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer" id="kt_tokens_table">
                                    <!--begin::Table head-->
                                    <thead>
                                        <!--begin::Table row-->
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px pe-2 sorting_disabled" rowspan="1" colspan="1" aria-label="" style="width: 29.8906px;">
                                               #
                                            </th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_tokens_table">Name</th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_tokens_table">Provider</th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_tokens_table">Token Hint</th>
                                            <th class="min-w-100px sorting" tabindex="0" aria-controls="kt_tokens_table">Scopes</th>
                                            <th class="min-w-75px sorting" tabindex="0" aria-controls="kt_tokens_table">Status</th>
                                            <th class="min-w-125px">Expires At</th>
                                            <th class="min-w-125px">Created Date</th>
                                            <th class="min-w-75px">Actions</th>
                                        </tr>
                                        <!--end::Table row-->
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600 fs-6">
                                        @foreach ($tokens as $token)
                                            <tr class="odd">
                                                <!--begin::Checkbox-->
                                                <td>{{ ($tokens->currentPage() - 1) * $tokens->count() + $loop->iteration }}</td>

                                                <td>{{ $token->name }}</td>
                                                <td>
                                                    <span class="badge badge-light-primary">{{ ucfirst($token->provider) }}</span>
                                                </td>
                                                <td>
                                                    <code class="text-muted">{{ $token->token_hint }}</code>
                                                </td>
                                                <td>
                                                    @if($token->scopes && count($token->scopes) > 0)
                                                        @foreach($token->scopes as $scope)
                                                            <span class="badge badge-light-info me-1">{{ $scope }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <form method="post" wire:submit.prevent="activeInactive({{$token->id}},{{$token->is_active ? 1 : 0}})">
                                                    @csrf
                                                    <td>
                                                        <button wire:click="activeInactive({{$token->id}},{{$token->is_active ? 1 : 0}})"
                                                                data-status="{{$token->is_active ? 1 : 0}}"
                                                                class="activeInactive p-0 btn btn-transparent">
                                                            <span class="badge badge-light-{{$token->is_active ? 'success' : 'danger'}}">
                                                                {{$token->is_active ? 'Active' : 'Inactive'}}
                                                            </span>
                                                        </button>
                                                    </td>
                                                </form>

                                                <td>
                                                    @if($token->expires_at)
                                                        {{ $token->expires_at->format('d M, Y H:i') }}
                                                    @else
                                                        <span class="text-muted">Never</span>
                                                    @endif
                                                </td>
                                                <td>{{ date('d M, Y', strtotime($token->created_at)) }}</td>

                                                <!--begin::Action-->
                                                <td class="text-center align-middle mt-2">
                                                    <button type="button"
                                                            wire:click="viewToken({{$token->id}})"
                                                            class="btn btn-sm btn-light-info me-2"
                                                            title="View Token">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($token->created_at->diffInMinutes(now()) < 5)
                                                        <span class="badge badge-light-success" title="Recently created - Token available">
                                                            <i class="fas fa-clock"></i> New
                                                        </span>
                                                    @endif

                                                    <a href="{{ url('admin/integration-tokens/'.$token->id.'/edit') }}"
                                                       wire:navigate class="btn btn-sm h-30px btn-light-primary me-2">
                                                        <i class="fas fa-pencil"></i>
                                                    </a>

                                                    <button type="button"
                                                            wire:click="revokeToken({{$token->id}})"
                                                            class="btn btn-sm btn-light-warning me-2"
                                                            @if(!$token->is_active) disabled @endif>
                                                        <i class="fas fa-ban"></i>
                                                    </button>

                                                    <button type="button"
                                                            wire:click="setDel({{$token->id}})"
                                                            class="delBtn btn btn-sm btn-light-danger d-inline">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                                <!--end::Action-->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                            </div>
                            <br>
                            {!! $tokens->links() !!}
                            <!--end::Table-->
                        </div>
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

<!--begin::Token View Modal-->
<div class="modal fade" id="kt_modal_view_token" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">View Integration Token</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning d-flex align-items-center p-5 mb-10">
                    <i class="fas fa-exclamation-triangle text-warning me-3 fs-2x"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">Security Notice</h4>
                        <span>This token is sensitive information. Please copy and save it securely.</span>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label fw-bold">Token Name:</label>
                    <div class="form-control form-control-solid">{{ $selectedTokenName ?? '' }}</div>
                </div>

                <div class="mb-5">
                    <label class="form-label fw-bold">Provider:</label>
                    <div class="form-control form-control-solid">{{ $selectedTokenProvider ?? '' }}</div>
                </div>

                <div class="mb-5">
                    <label class="form-label fw-bold">Token:</label>
                    <div class="input-group">
                        <input type="text"
                               value="{{ $selectedToken ?? '' }}"
                               class="form-control {{ $selectedToken === 'Token cannot be retrieved for security reasons.' ? 'text-muted' : '' }}"
                               id="viewTokenInput"
                               readonly>
                        <button type="button"
                                class="btn btn-light-primary"
                                onclick="copyViewToken()"
                                @if($selectedToken === 'Token cannot be retrieved for security reasons.') disabled @endif>
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    @if($selectedToken === 'Token cannot be retrieved for security reasons.')
                        <div class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            This token was created more than 5 minutes ago and cannot be retrieved for security reasons.
                            Only the token hint is available: <code>{{ $selectedTokenHint ?? '' }}</code>
                        </div>
                    @else
                        <div class="form-text text-success">
                            <i class="fas fa-check-circle"></i>
                            This token was recently created and is available for copying.
                        </div>
                    @endif
                </div>

                <div class="mb-5">
                    <label class="form-label fw-bold">Token Hint:</label>
                    <div class="form-control form-control-solid">{{ $selectedTokenHint ?? '' }}</div>
                </div>

                <div class="mb-5">
                    <label class="form-label fw-bold">Scopes:</label>
                    <div class="form-control form-control-solid">
                        @if($selectedTokenScopes && count($selectedTokenScopes) > 0)
                            @foreach($selectedTokenScopes as $scope)
                                <span class="badge badge-light-info me-1">{{ $scope }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No scopes defined</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--end::Token View Modal-->

@section('scripts')
<link rel="stylesheet" href="{{ asset('node_modules/flatpickr/dist/flatpickr.min.css') }}">
<script src="{{ asset('node_modules/flatpickr/dist/flatpickr.min.js') }}"></script>

<script>
    $(function() {
        flatpickr('#daterange', {
            mode: "range",
            dateFormat: "Y-m-d",
        });
    });

    var statusBtns = document.getElementsByClassName('activeInactive');
    for (let i = 0; i < statusBtns.length; i++) {
        statusBtns[i].onclick = function(e){
            status = $(this).data('status');
            if(status == 0){
                var message = "Do you want to activate this token?";
            } else {
                var message = "Do you want to deactivate this token?";
            }
            e.preventDefault();
            Swal.fire({
                title: "You want to change the status!",
                text: message,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#5E6278',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).data('status', (status == 1) ? 0 : 1);
                    Livewire.dispatch('submit-active');
                }
            })
        };
    }

    var delBtns = document.getElementsByClassName('delBtn');
    for (let i = 0; i < delBtns.length; i++) {
        delBtns[i].onclick = function(e){
            e.preventDefault();
            Swal.fire({
                title: "Do you want to delete this token?",
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#5E6278',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('del-item');
                }
            })
        };
    }

    function copyViewToken() {
        const tokenInput = document.getElementById('viewTokenInput');
        const tokenValue = tokenInput.value;

        // Check if token is available for copying
        if (tokenValue === 'Token cannot be retrieved for security reasons.') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Cannot Copy Token',
                    text: 'This token cannot be retrieved for security reasons. Please create a new token if needed.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
            return;
        }

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

    // Listen for Livewire event to show modal
    document.addEventListener('livewire:init', () => {
        Livewire.on('show-token-modal', () => {
            const modal = new bootstrap.Modal(document.getElementById('kt_modal_view_token'));
            modal.show();
        });
    });
</script>
@endsection
</div>
