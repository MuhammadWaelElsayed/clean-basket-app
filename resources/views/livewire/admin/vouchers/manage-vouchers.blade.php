@section('vouchersShow', 'active')
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
                        Vouchers Management</h1>
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
                            <a href="{{ url('admin/vouchers') }}" class="text-muted text-hover-primary">Vouchers /</a>
                        </li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->

                <div class="d-flex align-orders-center gap-2 gap-lg-3">
                    <!--begin::Add New Voucher-->
                    <div class="app-container container-xxl d-flex flex-stack">
                        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-base btn-sm"> Add New Voucher</a>

                    </div>
                    <!--end::Primary button-->
                </div>

            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->
    <!--end::Content wrapper-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl"
            data-select2-id="select2-data-kt_app_content_container">
            <!--begin::Card-->
            <div class="card">
                <div class="card-body pt-0">
                    <div id="kt_partners_table_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover fs-6 gy-5 " id="kt_partners_table">
                                <!--begin::Table head-->
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Max Usage</th>
                                        <th>Expiry Date</th>
                                        <th>Note</th>
                                        <th># Users</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vouchers as $voucher)
                                        <tr>
                                            <td>{{ $voucher->id }}</td>
                                            <td>{{ $voucher->type }}</td>
                                            <td>{{ $voucher->amount }}</td>
                                            <td>{{ $voucher->max_usage }}</td>
                                            <td>{{ $voucher->expiry_date }}</td>
                                            <td>{{ $voucher->note }}</td>
                                            <td>{{ $voucher->userVouchers()->count() }}</td>
                                            <td>
                                                <a href="{{ route('admin.vouchers.edit', $voucher->id) }}"
                                                    wire:navigate class="btn btn-sm h-30px btn-light-primary"><i
                                                        class="fas fa-pencil"></i></a>
                                                <a href="{{ route('admin.vouchers.view', $voucher->id) }}"
                                                    wire:navigate class="btn btn-sm h-30px btn-light-primary"><i
                                                        class="fas fa-eye"></i></a>

<button type="button" wire:click="setDel({{ $voucher->id }})" class="delBtn btn btn-sm btn-light-danger">
    <i class="fas fa-trash"></i>
</button>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- زر إضافة قسيمة جديد (سنضيفه لاحقًا) --}}
            </div>
        </div>
    </div>
</div>

</div>


@section('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // تعيين الحدث على جميع أزرار الحذف
    document.addEventListener('DOMContentLoaded', function() {
        let delBtns = document.getElementsByClassName('delBtn');
        for (let i = 0; i < delBtns.length; i++) {
            delBtns[i].onclick = function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This voucher will be deleted permanently!",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#5E6278',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('delVoucher'); // استدعاء دالة Livewire للحذف
                    }
                });
            }
        }
    });

    // رسالة نجاح بعد الحذف
    window.addEventListener('success', event => {
        Swal.fire({
            icon: 'success',
            title: event.detail,
            showConfirmButton: false,
            timer: 1200
        });
    });
</script>
@endsection

