@section('rolesActive', 'active')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Roles Management</h1>
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
                        <li class="breadcrumb-item text-muted">Roles</li>
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
                        <!--begin::Create Role Form-->
                        <div class="mb-8">
                            <h3 class="fs-5 fw-bold mb-4">Create New Role</h3>
                            <form class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="create">
                                <div class="d-flex flex-column fv-row fv-plugins-icon-container">
                                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                        <span class="required">Role Name</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" placeholder="Enter Role Name" wire:model="name">
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="btn btn-base my-5">Create Role</button>
                            </form>
                        </div>
                        <!--end::Create Role Form-->

                        <!--begin::Edit Role Form-->
                        @if ($editingRoleId)
                            <div class="mb-8">
                                <h3 class="fs-5 fw-bold mb-4">Edit Role</h3>
                                <form class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="update">
                                    <div class="d-flex flex-column fv-row fv-plugins-icon-container">
                                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                            <span class="required">Role Name</span>
                                        </label>
                                        <input type="text" class="form-control form-control-solid" placeholder="Enter Role Name" wire:model="editingName">
                                        @error('editingName') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="btn btn-base my-5">Update Role</button>
                                    <button type="button" class="btn btn-light my-5 ms-3" wire:click="$set('editingRoleId', null)">Cancel</button>
                                </form>
                            </div>
                        @endif
                        <!--end::Edit Role Form-->

                        <!--begin::Roles Table-->
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                <tr class="fw-bold text-muted">
                                    <th>Name</th>
                                    <th>Guard</th>
                                    <th>Created At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->guard_name }}</td>
                                        <td>{{ $role->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-icon btn-light" wire:click="edit({{ $role->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-light" wire:click="delete({{ $role->id }})" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--end::Roles Table-->
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
