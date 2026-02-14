
@section('pricingTiersActive', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Add New Pricing Tier
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('pricing-tiers.index') }}" class="text-muted text-hover-primary">Pricing Tiers</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Create</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body pt-10">
                        <form wire:submit.prevent="store" class="form">
                            <div class="row g-9">
                                <!--begin::Name (English)-->
                                <div class="col-md-6 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Tier Name (English)</label>
                                    <input type="text" wire:model="name" class="form-control" placeholder="Silver Tier">
                                    @error('name') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <!--begin::Name (Arabic)-->
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Tier Name (Arabic)</label>
                                    <input type="text" wire:model="name_ar" class="form-control" placeholder="مستوى فضي" dir="rtl">
                                    @error('name_ar') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <!--begin::Description (English)-->
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Description (English)</label>
                                    <textarea wire:model="description" class="form-control" rows="3" placeholder="Better pricing for regular clients"></textarea>
                                    @error('description') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <!--begin::Description (Arabic)-->
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Description (Arabic)</label>
                                    <textarea wire:model="description_ar" class="form-control" rows="3" placeholder="تسعير أفضل للعملاء المنتظمين" dir="rtl"></textarea>
                                    @error('description_ar') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <!--begin::Discount Percentage-->
                                <div class="col-md-4 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Default Discount (%)</label>
                                    <input type="number" step="0.01" wire:model="discount_percentage" class="form-control" placeholder="10.00">
                                    <div class="form-text">This is the default discount applied to all items</div>
                                    @error('discount_percentage') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Type</label>
                                    <select class="form-control" wire:model.live="type">
                                        <option value="dynamic">Dynamic</option>
                                        <option value="fixed">Fixed</option>
                                    </select>
                                    @error('type') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                @if($type == 'dynamic')
                                <div class="col-md-4 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Min</label>
                                    <input type="number" wire:model="min" class="form-control" placeholder="1">
                                    <div class="form-text">Min items in tier</div>
                                    @error('min"') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-4 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Max</label>
                                    <input type="number" wire:model="max" class="form-control" placeholder="2500">
                                    <div class="form-text">Max items in tier</div>
                                    @error('max"') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                <!--begin::Priority-->
                                <div class="col-md-4 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Priority</label>
                                    <input type="number" wire:model="priority" class="form-control" placeholder="1">
                                    <div class="form-text">Higher priority tiers are applied first</div>
                                    @error('priority') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>

                                <!--begin::Status-->
                                <div class="col-md-4 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Status</label>
                                    <div class="form-check form-switch form-check-custom form-check-solid mt-3">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    @error('is_active') <span class="text-danger fs-7">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-10">
                                <a href="{{ route('pricing-tiers.index') }}" class="btn btn-light" wire:navigate>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-base" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Create Tier</span>
                                    <span wire:loading>
                                        Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
