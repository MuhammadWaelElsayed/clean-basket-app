<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-4">Import Items from Excel</h2>
        <a href="{{ asset('samples/items-import-sample.csv') }}" class="btn btn-outline-info mb-3" download>
            📥 Download Excel Template
        </a>

    </div>

    <form wire:submit.prevent="import" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Select Excel File</label>
            <input type="file" class="form-control" wire:model="file" accept=".xlsx,.xls,.csv">
            @error('file')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            Import
        </button>
        <a href="{{ route('admin.items') }}" class="btn btn-secondary">Back</a>
    </form>

    <div class="mt-4">
        {{-- <p><strong>Excel Columns:</strong></p> --}}
        <div class="card">
             <div class="card-header">
                <h3 class="card-title">Excel Columns With Sample Data</h3>
            </div>
            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer table-bordered table-striped table-hover" id="kt_items_table">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-75px sorting">name</th>
                            <th  class="min-w-75px sorting">name_ar</th>
                            <th  class="min-w-75px sorting">description</th>
                            <th  class="min-w-75px sorting">description_ar</th>
                            <th  class="min-w-75px sorting">price (pressing price)</th>
                            <th  class="min-w-75px sorting">services</th>
                            <th>service_types (comma-separated)</th>
                            <th>service_prices</th>
                            <th>service_discount_prices</th>
                            <th>order_priority_id</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600 fs-6">
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <td>T-Shirt</td>
                            <td>تيشيرت</td>
                            <td>Cotton T-Shirt</td>
                            <td>Cotton T-Shirt</td>
                            <td>25</td>
                            <td>19</td>
                            <td>3,4</td>
                            <td>15,20</td>
                            <td>12,18</td>
                            <td>1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
