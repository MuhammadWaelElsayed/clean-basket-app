@section('voucherReport', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">

        <!-- ✅ Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Voucher Report
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Home /</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Reports /</li>
                        <li class="breadcrumb-item text-muted">Voucher Report</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ✅ Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!-- ✅ Report Table -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Voucher Statistics per Package</h4>

                        {{-- زر التصدير إن أردت مستقبلاً --}}
                        {{-- <button wire:click="export" class="btn btn-sm btn-light-primary">Export CSV</button> --}}
                    </div>

                    <div class="card-body">
                        <table class="table table-striped fs-6 align-middle">
                            <thead class="fw-bold">
                                <tr>
                                    <th>Package</th>
                                    <th>Granted</th>
                                    <th>Used</th>
                                    <th>Remaining</th>
                                    <th>Usage %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['granted'] }}</td>
                                        <td>{{ $row['used'] }}</td>
                                        <td>{{ $row['remaining'] }}</td>
                                        <td>{{ number_format($row['usage_rate'], 2) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center">No data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
