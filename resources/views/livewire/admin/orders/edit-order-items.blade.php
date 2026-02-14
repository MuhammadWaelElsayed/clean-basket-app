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
                            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Order Details</h1>
                            <!--end::Title-->
                            <!--begin::Breadcrumb-->
                            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                <!--begin::Item-->
                                <li class="breadcrumb-item text-muted">
                                    <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Home</a>
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="breadcrumb-item">
                                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="breadcrumb-item text-muted">Orders</li>
                                <!--end::Item-->
                            </ul>
                            <!--end::Breadcrumb-->
                        </div>
                        <!--end::Page title-->
                        <div class="back-home-btn">
                            <button type="button" class="btn btn-primary" wire:click="addNewItem" wire:key="add-new-item-btn"
                            wire:loading.attr="disabled">
                            <i class="fas fa-plus"></i> Add New Item
                        </button>
                            <a href="{{ url($order->type == 'b2b' ? 'admin/b2b-orders' : 'admin/orders') }}" class="btn btn-primary">Back to Orders</a>
                        </div>
                    </div>
                    <!--end::Toolbar container-->
                </div>
                <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl" data-select2-id="select2-data-kt_app_content_container">
                <!--begin::Card-->

        <div class="card">

            <div class="card-body">
                <!-- جدول العناصر الحالية -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Service Type</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Add-ons</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderItems as $index => $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $item['item']['image'] ?? asset('storage/uploads/blank.png') }}"
                                                class="w-50px rounded me-3" alt="item">
                                            <span>{{ $item['item']['name'] ?? 'Not specified' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $item['service_type']['name'] ?? 'Not specified' }}</td>
                                    <td>{{ env('CURRENCY') }} {{ number_format($item['price'], 2) }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td>
                                        @if (!empty($item['add_ons']))
                                            @foreach ($item['add_ons'] as $addOn)
                                                <span class="badge badge-light-primary me-1">
                                                    {{ $addOn['name'] }} ({{ env('CURRENCY') }}
                                                    {{ number_format($addOn['price'], 2) }})
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No add-ons</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ env('CURRENCY') }}
                                        {{ number_format($item['total_price'], 2) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning me-2"
                                            wire:click="editItem({{ $index }})"
                                            wire:key="edit-item-{{ $index }}" wire:loading.attr="disabled">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            wire:click="removeItem({{ $index }})"
                                            wire:key="remove-item-{{ $index }}" wire:loading.attr="disabled"
                                            onclick="return confirm('Are you sure you want to delete this item?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No items in the order</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ملخص الإجماليات -->
                <div class="row mt-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Items Total:</th>
                                    <td class="text-end">{{ env('CURRENCY') }} {{ number_format($this->subTotal, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivery Fee:</th>
                                    <td class="text-end">{{ env('CURRENCY') }}
                                        {{ number_format($order->delivery_fee, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>VAT:</th>
                                    <td class="text-end">{{ env('CURRENCY') }}
                                        {{ number_format($this->vatAmount, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <th class="fw-bold">Grand Total:</th>
                                    <td class="text-end fw-bold">{{ env('CURRENCY') }}
                                        {{ number_format($this->grandTotal, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- أزرار الحفظ -->
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-success me-2" wire:click="saveOrderItems"
                        wire:loading.attr="disabled">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.order.details', $order->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- نموذج إضافة/تعديل العنصر -->
@if ($showAddItemForm || $editingItemIndex !== null)
    <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5); z-index: 1050;" tabindex="-1"
        wire:key="modal-{{ $showAddItemForm ? 'add' : 'edit' }}-{{ $editingItemIndex }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editingItemIndex !== null ? 'Edit Item' : 'Add New Item' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="cancelEdit"></button>
                </div>

                <div class="modal-body">
                    <form wire:submit.prevent="saveItem"
                        wire:key="item-form-{{ $showAddItemForm ? 'add' : 'edit' }}-{{ $editingItemIndex }}">
                        <div class="row">
                            <!-- اختيار الصنف -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="newItem.item_id" required>
                                    <option value="">Select Item</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- اختيار نوع الخدمة -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Type <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="newItem.service_type_id" required>
                                    <option value="">Select Service Type</option>
                                    @foreach ($serviceTypes as $serviceType)
                                        <option value="{{ $serviceType->id }}">{{ $serviceType->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- الكمية -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" wire:model="newItem.quantity" min="1"
                                    required>
                            </div>

                            <!-- أولوية الطلب -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order Priority <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="selectedPriority" required disabled>
                                    @foreach ($orderPriorities as $priority)
                                        <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- الإضافات -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Add-ons (Optional)</label>
                                <div class="row">
                                    @foreach ($addOns as $addOn)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    value="{{ $addOn->id }}" wire:model="newItem.add_on_ids"
                                                    id="addon_{{ $addOn->id }}">
                                                <label class="form-check-label" for="addon_{{ $addOn->id }}">
                                                    {{ $addOn->name }}
                                                    <span class="text-muted">({{ env('CURRENCY') }}
                                                        {{ number_format($addOn->price, 2) }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelEdit" wire:key="cancel-btn"
                        wire:loading.attr="disabled">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveItem"
                        wire:key="save-btn-{{ $editingItemIndex }}" wire:loading.attr="disabled">
                        {{ $editingItemIndex !== null ? 'Update' : 'Add' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
