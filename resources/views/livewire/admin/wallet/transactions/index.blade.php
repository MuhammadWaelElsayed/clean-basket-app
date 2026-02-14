@section('walletShow', 'hover show')
@section('transactionsActive', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Wallet
                        Transactions</h1>

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
                        <li class="breadcrumb-item text-muted">Wallet</li>
                        <!--end::Item-->
                    </ul>
                </div>

                {{-- <div class="d-flex align-orders-center gap-2 gap-lg-3">
                    <button wire:click="exportData" type="button" class="btn btn-sm btn-light-primary">
                        Export
                    </button>
                </div> --}}
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="w-100">
                            <form>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" wire:model.live="search"
                                            class="form-control form-control-solid"
                                            placeholder="Search by user name or email..." />
                                    </div>

                                    <div class="col-md-3">
                                        <select wire:model.live="type" class="form-control form-control-solid">
                                            <option value="">All Types</option>
                                            <option value="credit">Credit</option>
                                            <option value="debit">Debit</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <input class="form-control form-control-solid"
                                            wire:model.live.debounce.500ms="daterange" id="daterange" autocomplete="off"
                                            placeholder="Select Date Range" />
                                    </div>

                                    <div class="col-md-2 d-flex align-items-center">
                                        <button type="button" wire:click="clearFilter"
                                            class="btn btn-light-primary">Clear Filters</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <div id="kt_Orders_table_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">

                            <div class="table-responsive">
                                <table class="table align-middle table-hover fs-6 gy-5 dataTable no-footer"
                                    id="kt_Orders_table">
                                    <thead>
                                        <tr class="text-start text-gray-600 bg-light fw-bold fs-7 text-uppercase gs-0">
                                            <th>User</th>
                                            <th>Transaction ID</th>
                                            <th>VAT Amount</th>
                                            <th>Amount</th>
                                            <th>Total Amount</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                            <th>Description</th>
                                            <th>Related Order</th>
                                            <th>Package</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600 fs-6">
                                        @foreach ($transactions as $item)
                                            <tr>
                                                <td class="odd">
                                                    {{ $item->wallet->user ?? null ? $item->wallet->user->first_name . ' ' . $item->wallet->user->last_name : '-' }}
                                                </td>

                                                <td class="odd">{{ $item->transaction_id ?? '-' }}</td>
                                                <td class="odd">{{ number_format($item->vat_amount, 2) }} SAR</td>
                                                <td class="odd">{{ number_format($item->amount, 2) }} SAR</td>
                                                <td class="odd">{{ number_format($item->total_amount ?? $item->amount + $item->vat_amount, 2) }} SAR</td>
                                                <td class="odd">
                                                    @if ($item->type == 'credit')
                                                        <span class="badge badge-success">Credit</span>
                                                    @else
                                                        <span class="badge badge-danger">Debit</span>
                                                    @endif
                                                </td>
                                                <td class="odd">{{ ucfirst($item->source) }}</td>
                                                <td class="odd">{{ $item->description ?? '-' }}</td>
                                                <td class="odd">{{ $item->related_order_id ?? '-' }}</td>
                                                <td class="odd">{{ $item->userPackage?->package?->name ?? '-' }}</td>
                                                <td class="odd">{{ $item->created_at->format('d M, Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                {!! $transactions->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        flatpickr("#daterange", {
            mode: "range",
            dateFormat: "Y-m-d",
        });
    </script>
@endsection
