@section('vouchersShow','active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-xxl d-flex flex-stack">
        <h1 class="page-heading text-dark fw-bold fs-3">Edit Voucher</h1>
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
              <label class="form-label">Type</label>
              <input type="text" wire:model.defer="type" class="form-control" readonly>
              @error('type')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Amount</label>
              <input type="number" step="0.01" wire:model.defer="amount" class="form-control">
              @error('amount')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Max Usage</label>
              <input type="number" wire:model.defer="max_usage" class="form-control">
              @error('max_usage')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Expiry Date</label>
              <input type="date" wire:model.defer="expiry_date" class="form-control">
              @error('expiry_date')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="col-12">
              <label class="form-label">Note</label>
              <textarea wire:model.defer="note" class="form-control" rows="2"></textarea>
              @error('note')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
          </div>
          <div class="mt-4">
            <a href="{{ route('admin.vouchers') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
