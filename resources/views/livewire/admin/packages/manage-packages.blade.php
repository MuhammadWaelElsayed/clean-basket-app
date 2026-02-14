@section('reportsShow', 'here')
@section('managePackages', 'active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main" data-select2-id="select2-data-kt_app_main">
    <div class="d-flex flex-column flex-column-fluid" data-select2-id="select2-data-129-xgx3">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Packages Management</h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Home /</a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item ">
                                <a href="{{ url('admin/packages') }}" class="text-muted text-hover-primary">Packages
                                    /</a>
                            </li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->

                    <div class="d-flex align-orders-center gap-2 gap-lg-3">
                        <!--begin::Add New Package-->
                        <!--end::Add New Package-->
                        <!--end::Primary button-->
                    </div>
                </div>
                <!--end::Toolbar container-->
            </div>
            <!--end::Toolbar-->
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
            <div class="app-container container-xxl" data-select2-id="select2-data-kt_app_content_container">

                <div class="card">
                    <div class="card-body pt-0">
                        <div id="kt_partners_table_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table align-middle table-hover fs-6 gy-5 " id="kt_partners_table">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name </th>
                                            <th>Name (English)</th>
                                            <th>Price (SAR)</th>
                                            <th>VAT (%)</th>
                                            <th>Total Price (SAR)</th>
                                            <th>Cashback (SAR)</th>
                                            <th>Delivery Fee</th>
                                            <th>Duration (days)</th>
                                            <th>Priority</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($packages as $pkg)
                                            <tr>
                                                <td>{{ $pkg->id }}</td>
                                                <td>{{ $pkg->name }}</td>
                                                <td>{{ $pkg->name_en }}</td>
                                                <td>{{ number_format($pkg->price, 2) }}</td>
                                                <td>{{ number_format($pkg->vat, 2) }}</td>
                                                <td>{{ number_format($pkg->total_price, 2) }}</td>
                                                <td>{{ number_format($pkg->cashback_amount, 2) }}</td>
                                                <td>{{ number_format($pkg->delivery_fee, 2) }}</td>
                                                <td>{{ $pkg->duration_days ?? 'Until the full balance is consumed' }}</td>
                                                <td>{{ $pkg->has_priority ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.packages.edit', $pkg->id) }}"
                                                        class="btn btn-sm btn-primary">Edit</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
