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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                @if($selectedCategory)
                    Sub Categories for: {{ $selectedCategory->name }} {{ $selectedCategory->name_ar }}
                @else
                    Sub Categories Management
                @endif
            </h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-primary me-2" wire:click="exportData">
                    <i class="ki-duotone ki-file-down fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Export
                </button>
                @if(!$selectedCategory)
                    <a href="{{ route('admin.issue-categories') }}" class="btn btn-sm btn-light-secondary">
                        <i class="ki-duotone ki-arrow-left fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Back to Categories
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-5">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search sub categories..." wire:model.live="search">
                </div>
                @if(!$selectedCategory)
                    <div class="col-md-4">
                        <select class="form-select" wire:model.live="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }} {{ $category->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <button type="button" class="btn btn-secondary" wire:click="clearFilter">
                      Clear Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubCategoryModal">
                        Add New
                    </button>
                </div>
            </div>

            <!-- Sub Categories Table -->
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="w-25px">
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" data-kt-check="true" data-kt-check-target=".widget-9-check" />
                                </div>
                            </th>
                            <th class="min-w-150px">ID</th>
                            <th class="min-w-200px">Name</th>
                            <th class="min-w-200px">Name Ar</th>
                            @if(!$selectedCategory)
                                <th class="min-w-200px">Main Category</th>
                            @endif
                            <th class="min-w-120px">Created At</th>
                            {{-- <th class="min-w-100px text-end">Actions</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subCategories as $subCategory)
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input widget-9-check" type="checkbox" value="1" />
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                        {{ $subCategory->id }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                        {{ $subCategory->name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                        {{ $subCategory->name_ar }}
                                    </span>
                                </td>
                                @if(!$selectedCategory)
                                    <td>
                                        <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                            {{ $subCategory->mainCategory ? $subCategory->mainCategory->name : 'N/A' }} {{ $subCategory->mainCategory ? $subCategory->mainCategory->name_ar : 'N/A' }}
                                        </span>
                                    </td>
                                @endif
                                <td>
                                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">
                                        {{ $subCategory->created_at ? $subCategory->created_at->format('Y-m-d H:i') : 'N/A' }}
                                    </span>
                                </td>
                                {{-- <td class="text-end"> --}}
                                    {{-- <div class="d-flex justify-content-end flex-shrink-0">
                                        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1 updateSubCategoryBtn"
                                                data-id="{{ $subCategory->id }}" data-name="{{ $subCategory->name }}">
                                            <i class="ki-duotone ki-pencil fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm deleteSubCategoryBtn"
                                                data-id="{{ $subCategory->id }}">
                                            <i class="ki-duotone ki-trash fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </button>
                                    </div> --}}
                                {{-- </td> --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectedCategory ? '5' : '6' }}" class="text-center">No sub categories found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex flex-wrap py-2 mr-3">
                    {{ $subCategories->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Create Sub Category Modal -->
    <div class="modal fade" id="createSubCategoryModal" tabindex="-1" aria-labelledby="createSubCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSubCategoryModalLabel">Create New Sub Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" wire:model="newName" placeholder="Enter sub category name">
                        @error('newName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Name Ar</label>
                        <input type="text" class="form-control" wire:model="newNameAr" placeholder="Enter sub category name ar">
                        @error('newNameAr') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    @if(!$selectedCategory)
                        <div class="form-group mt-3">
                            <label class="form-label">Main Category</label>
                            <select class="form-select" wire:model="category_id">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} {{ $category->name_ar }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="createSubCategory('{{ $newName }}', '{{ $newNameAr }}')" data-bs-dismiss="modal">Create</button>
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

        // Update Sub Category Buttons
        document.addEventListener('DOMContentLoaded', function() {
            var updateSubCategoryBtns = document.getElementsByClassName('updateSubCategoryBtn');
            for (let i = 0; i < updateSubCategoryBtns.length; i++) {
                updateSubCategoryBtns[i].onclick = function(e) {
                    var id = $(this).data('id');
                    var currentName = $(this).data('name');
                    var currentNameAr = $(this).data('nameAr');
                    var newName = prompt('Enter new name for sub category:', currentName);
                    var newNameAr = prompt('Enter new name ar for sub category:', currentNameAr);

                    if (newName && newName !== currentName) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Update Sub Category',
                            text: 'Do you want to update the name to "' + newName + '"?',
                            icon: 'warning',
                            showCancelButton: true,
                            cancelButtonColor: '#5E6278',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, update it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                @this.call('updateSubCategory', id, newName, newNameAr);
                            }
                        });
                    }
                };
            }
        });

        // Delete Sub Category Buttons
        document.addEventListener('DOMContentLoaded', function() {
            var deleteSubCategoryBtns = document.getElementsByClassName('deleteSubCategoryBtn');
            for (let i = 0; i < deleteSubCategoryBtns.length; i++) {
                deleteSubCategoryBtns[i].onclick = function(e) {
                    var id = $(this).data('id');

                    e.preventDefault();
                    Swal.fire({
                        title: 'Delete Sub Category',
                        text: 'Are you sure you want to delete this sub category? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonColor: '#5E6278',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('deleteSubCategory', id);
                        }
                    });
                };
            }
        });
    </script>
    @endpush
</div>
