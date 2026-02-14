<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // أو ضع منطق الصلاحية إذا لزم
    }


    public function rules()
    {
        return [
            // أولويات الطلب
            'order_priority_id' => 'required|exists:order_priorities,id',

            // التوجيهات (اختياري)
            'instructions'      => 'nullable|string',

            // بنود الفرز (واجب وجود عنصر واحد على الأقل)
            'items'             => 'required|array|min:1',
            'items.*.item_id'         => 'required|exists:items,id',
            'items.*.service_type_id' => 'required|exists:service_types,id',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.add_on_ids'      => 'array',
            'items.*.add_on_ids.*'    => 'exists:add_ons,id',
        ];
    }

    public function messages()
    {
        return [
            'items.required'     => 'You must include at least one item in the order.',
            'items.*.item_id.required' => 'Each order item must specify an item_id.',
            // يمكنك إضافة باقِي الرسائل حسب الحاجة
        ];
    }

}
