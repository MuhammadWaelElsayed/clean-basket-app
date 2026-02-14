@section('permissionsActive', 'active')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Permissions Assignment</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">Permissions</li>
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
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Select Role-->
                        <div class="mb-8">
                            <h3 class="fs-5 fw-bold mb-4">Select Role to Assign Permissions</h3>
                            <div class="d-flex flex-column fv-row fv-plugins-icon-container">
                                <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                    <span class="required">Role</span>
                                </label>
                                <select class="form-select form-select-solid" wire:model.live="selectedRole">
                                    <option value="">-- Select Role --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedRole') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <!--end::Select Role-->

                        @if ($selectedRole)
                            <!--begin::Permissions Groups-->
                            <form wire:submit.prevent="assign">
                                @foreach ($groupedPermissions as $group => $perms)
                                    @if (!empty($perms))
                                        <div class="mb-6">
                                            <h4 class="fs-6 fw-bold mb-3">{{ ucfirst($group) }} Permissions</h4>
                                            <div class="row g-3">
                                                @foreach ($perms as $perm)
                                                    <div class="col-md-4">
                                                        <div class="form-check form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" value="{{ $perm }}" wire:model="permissions" id="perm_{{ $perm }}">
                                                            <label class="form-check-label" for="perm_{{ $perm }}">{{ $perm }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                <button type="submit" class="btn btn-base my-5">Assign Permissions</button>
                            </form>
                            <!--end::Permissions Groups-->
                        @endif
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
