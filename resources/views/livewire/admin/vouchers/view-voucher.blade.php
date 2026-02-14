@section('vouchersShow','active')

<style>
    th{
        font-weight: bold !important;
    }
</style>
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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Voucher Details</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{url('admin/dashboard') }}" class="text-muted text-hover-primary">Home /</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item ">
                            <a href="{{url('admin/vouchers') }}" class="text-muted text-hover-primary">Vouchers /</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">Details</li>
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
        <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl" data-select2-id="select2-data-kt_app_content_container">
                <!--begin::Card-->

            <div class="card mb-4">
                <div class="card-body">
                    <table class="table table-striped fs-6">
                        <tr>
                            <th>Type</th>
                            <td> {{$voucher->type}}</td>
                        </tr>
                        <tr>
                            <th>Amount</th>
                            <td> {{$voucher->amount}}</td>
                        </tr>
                        <tr>
                            <th>Max Usage</th>
                            <td> {{$voucher->max_usage}} </td>
                        </tr>
                        <tr>
                            <th>Expiry Date</th>
                            <td> {{$voucher->expiry_date}} </td>
                        </tr>

                        <tr>
                            <th>Note</th>
                            <td>{{$voucher->note}}</td>
                        </tr>
                        <tr>
                            <th>Users</th>
                            <td>{{$voucher->userVouchers()->count()}}</td>
                        </tr>


                    </table>

                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Users Who Received This Voucher</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Used At</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($userVouchers as $uv)
                            <tr>
                                <td>{{ $uv->user->first_name ?? '' }} {{ $uv->user->last_name ?? '' }}</td>
                                <td>{{ $uv->user->email ?? '' }}</td>
                                <td>
                                    @if($uv->is_used)
                                        <span class="badge bg-success">Used</span>
                                    @else
                                        <span class="badge bg-secondary">Not Used</span>
                                    @endif
                                </td>
                                <td>{{ $uv->used_at }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if($userVouchers->isEmpty())
                        <div class="text-muted">No users found for this voucher.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
