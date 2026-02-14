@section('adminsActive', 'active')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Admins Management</h1>
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
                        <li class="breadcrumb-item text-muted">Admins</li>
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
                        <!--begin::Create Admin Form-->
                        <div class="mb-8">
                            <h3 class="fs-5 fw-bold mb-4">Create New Admin</h3>
                            <form class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="create">
                                <div class="row g-9 mb-4">
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">Name</label>
                                        <input type="text" class="form-control form-control-solid" placeholder="Enter Name" wire:model="name">
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">Email</label>
                                        <input type="email" class="form-control form-control-solid" placeholder="Enter Email" wire:model="email">
                                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="row g-9 mb-4">
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">Password</label>
                                        <input type="password" class="form-control form-control-solid" placeholder="Enter Password" wire:model="password">
                                        @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="fs-6 fw-semibold mb-2">Roles</label>
                                    <div class="row g-3">
                                        @foreach ($roles as $role)
                                            <div class="col-md-4">
                                                <div class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" wire:model="selectedRoles" value="{{ $role->name }}" id="create_role_{{ $role->id }}">
                                                    <label class="form-check-label" for="create_role_{{ $role->id }}">{{ $role->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('selectedRoles') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="btn btn-base my-5">Create Admin</button>
                            </form>
                        </div>
                        <!--end::Create Admin Form-->

                        <!--begin::Edit Admin Form-->
                        @if ($editingAdminId)
                            <div class="mb-8">
                                <h3 class="fs-5 fw-bold mb-4">Edit Admin</h3>
                                <form class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="update">
                                    <div class="row g-9 mb-4">
                                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                                            <label class="required fs-6 fw-semibold mb-2">Name</label>
                                            <input type="text" class="form-control form-control-solid" placeholder="Enter Name" wire:model="editingName">
                                            @error('editingName') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                                            <label class="required fs-6 fw-semibold mb-2">Email</label>
                                            <input type="email" class="form-control form-control-solid" placeholder="Enter Email" wire:model="editingEmail">
                                            @error('editingEmail') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="row g-9 mb-4">
                                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                                            <label class="fs-6 fw-semibold mb-2">New Password (Optional)</label>
                                            <input type="password" class="form-control form-control-solid" placeholder="Enter New Password" wire:model="editingPassword">
                                            @error('editingPassword') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="fs-6 fw-semibold mb-2">Roles</label>
                                        <div class="row g-3">
                                            @foreach ($roles as $role)
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" wire:model="editingSelectedRoles" value="{{ $role->name }}" id="edit_role_{{ $role->id }}">
                                                        <label class="form-check-label" for="edit_role_{{ $role->id }}">{{ $role->name }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('editingSelectedRoles') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="btn btn-base my-5">Update Admin</button>
                                    <button type="button" class="btn btn-light my-5 ms-3" wire:click="$set('editingAdminId', null)">Cancel</button>
                                </form>
                            </div>
                        @endif
                        <!--end::Edit Admin Form-->

                        <!--begin::Admins Table-->
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                <tr class="fw-bold text-muted">
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Created At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($admins as $admin)
                                    <tr>
                                        <td>{{ $admin->name ?? 'N/A' }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>{{ $admin->roles->pluck('name')->implode(', ') }}</td>
                                        <td>{{ $admin->created_at?->format('Y-m-d H:i:s') }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-icon btn-light" wire:click="edit({{ $admin->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-light" wire:click="delete({{ $admin->id }})" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--end::Admins Table-->
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
