<div>
    <!-- Alert Messages -->
    @if(session()->has('success'))
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <i class="ki-duotone ki-check-circle fs-2 me-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
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
                                Issue Categories</h1>
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
                            <li class="breadcrumb-item text-muted">Issue Categories</li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                    <!--begin::Actions-->
                    <div class="d-flex align-partners-center gap-2 gap-lg-3">
                        <button type="button" class="btn btn-primary btn-sm" id="addCategoryBtn" wire:click="$set('showAddModal', true)">
                            <i class="ki-duotone ki-plus fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Add New Category
                        </button>
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
                    <!--begin::Card-->
                    <div class="card">
                        {{-- <div class="card-header">
                            <h3 class="card-title">Issue Categories Management</h3>
                        </div> --}}
                        <div class="card-body">
                            <!-- Search and Add New -->
                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Search categories..."
                                        wire:model.live="search">
                                </div>
                            </div>

                            <!-- Categories Table -->
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
                                            <th class="min-w-150px">Category Name</th>
                                            <th class="min-w-140px">Tickets Count</th>
                                            <th class="min-w-120px">Created At</th>
                                            <th class="min-w-100px text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($categories as $category)
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
                                                            <span
                                                                class="text-dark fw-bold text-hover-primary fs-6">{{ $category->name }}</span>
                                                            <span
                                                                class="text-dark fw-bold text-hover-primary fs-6">{{ $category->name_ar }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-light-primary">{{ $category->tickets_count ?? 0 }}
                                                        tickets</span>
                                                </td>
                                                <td>
                                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                                        {{ $category->created_at ? $category->created_at->format('Y-m-d H:i') : 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end flex-shrink-0">
                                                        <a href="{{ route('admin.sub-issue-categories', $category->id) }}"
                                                            class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1"
                                                            title="Manage Sub Categories">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        {{-- <button type="button" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1 editCategoryBtn"
                                                                data-id="{{ $category->id }}" data-name="{{ $category->name }}">
                                                            <i class="fas fa-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm deleteCategoryBtn"
                                                                data-id="{{ $category->id }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button> --}}
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No categories found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="d-flex flex-wrap py-2 mr-3">
                                    {{ $categories->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    @if($showAddModal ?? false)
    <div class="modal fade show" style="display: block;" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" wire:click="$set('showAddModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" wire:model="newName" placeholder="Enter category name">
                        @error('newName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category Name Ar</label>
                        <input type="text" class="form-control" wire:model="newNameAr" placeholder="Enter category name ar">
                        @error('newNameAr') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showAddModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="createCategory">Create</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

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

        // Add Category Button
        document.addEventListener('DOMContentLoaded', function() {
            var addCategoryBtn = document.getElementById('addCategoryBtn');
            if (addCategoryBtn) {
                addCategoryBtn.onclick = function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Add New Category',
                        input: 'text',
                        inputLabel: 'Category Name',
                        showCancelButton: true,
                        cancelButtonColor: '#5E6278',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Add',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'You need to write something!'
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('createCategory', result.value);
                        }
                    });
                };
            } else {
                console.error('Add Category Button not found!');
            }
        });

        // Edit Category Buttons
        document.addEventListener('DOMContentLoaded', function() {
            var editCategoryBtns = document.getElementsByClassName('editCategoryBtn');
            for (let i = 0; i < editCategoryBtns.length; i++) {
                editCategoryBtns[i].onclick = function(e) {
                    var id = $(this).data('id');
                    var currentName = $(this).data('name');
                    var currentNameAr = $(this).data('nameAr');

                    e.preventDefault();
                    Swal.fire({
                        title: 'Edit Category',
                        input: 'text',
                        inputValue: currentName,
                        inputLabel: 'Category Name',
                        inputValueAr: currentNameAr,
                        inputLabelAr: 'Category Name Ar',
                        showCancelButton: true,
                        cancelButtonColor: '#5E6278',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Update',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'You need to write something!'
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('updateCategory', id, result.value, result.valueAr || currentNameAr);
                        }
                    });
                };
            }
        });

        // Delete Category Buttons
        document.addEventListener('DOMContentLoaded', function() {
            var deleteCategoryBtns = document.getElementsByClassName('deleteCategoryBtn');
            for (let i = 0; i < deleteCategoryBtns.length; i++) {
                deleteCategoryBtns[i].onclick = function(e) {
                    var id = $(this).data('id');

                    e.preventDefault();
                    Swal.fire({
                        title: 'Delete Category',
                        text: 'Are you sure you want to delete this category? Categories with associated tickets cannot be deleted.',
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonColor: '#5E6278',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('deleteCategory', id);
                        }
                    });
                };
            }
        });
    </script>
    @endpush
</div>
