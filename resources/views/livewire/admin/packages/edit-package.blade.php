@section('reportsShow','here')
@section('managePackages','active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-xxl d-flex flex-stack">
        <h1 class="page-heading text-dark fw-bold fs-3">Edit Package</h1>
      </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-xxl">
        @if (session()->has('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form wire:submit.prevent="update">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input type="text" wire:model.defer="name" class="form-control">
              @error('name')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Name (English)</label>
              <input type="text" wire:model.defer="name_en" class="form-control" disabled>
              @error('name_en')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Price</label>
              <input type="number" step="0.01" wire:model.defer="price" class="form-control">
              @error('price')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">VAT</label>
              <input type="number" step="0.01" wire:model.defer="vat" class="form-control" value="0.15">
              @error('vat')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Cashback</label>
              <input type="number" step="0.01" wire:model.defer="cashback_amount" class="form-control">
              @error('cashback_amount')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Delivery Fee</label>
              <input type="number" step="0.01" wire:model.defer="delivery_fee" class="form-control">
              @error('delivery_fee')<span class="text-danger">{{ $message }}</span>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Duration (days)</label>
              <input type="number" class="form-control bg-light" value="-"  placeholder="Until the full balance is consumed" disabled>
              {{-- @if(strtolower($name_en) === 'basic')
                <input type="number" wire:model.defer="duration_days" class="form-control" min="1">
                <small class="text-muted">Required for Basic only</small>
              @else
                <input type="number" class="form-control bg-light" value="-"  placeholder="Until the full balance is consumed" disabled>
                <small class="text-muted">Not applicable for this package</small>
              @endif
              @error('duration_days')<span class="text-danger">{{ $message }}</span>@enderror --}}
            </div>

            <div class="col-md-6 d-flex align-items-center">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" wire:model.defer="has_priority" id="has_priority">
                <label class="form-check-label" for="has_priority">Has Priority</label>
              </div>
            </div>
          </div>
          <div class="mt-4">
            <a href="{{ route('admin.packages') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
