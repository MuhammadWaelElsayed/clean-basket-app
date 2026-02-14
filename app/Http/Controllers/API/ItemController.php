<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ServiceType;
use App\Models\AddOn;
use App\Models\ItemCategory;


class ItemController extends Controller
{
    /**
     * GET /api/items
     * Get all items with categories, services, and prices
     */
    public function index(Request $request)
    {

        $query = Item::with(['category', 'serviceCategories', 'serviceTypes'])->where('status', 1)->where('id', '>=', 1000);

        if ($request->has('category')) {
            $query->where('service_id', $request->query('category'));
        }

        $items = $query->get()->map(function (Item $item) {
            $order_priority_id = null;
            if ($item->serviceTypes->count() > 0 && isset($item->serviceTypes[0]->pivot->order_priority_id)) {
                $order_priority_id = $item->serviceTypes[0]->pivot->order_priority_id;
            }
            $order_priority = $order_priority_id ? \App\Models\OrderPriority::find($order_priority_id) : null;
            return [
                'id'             => $item->id,
                'name'           => $item->name,
                'name_ar'        => $item->name_ar,
                'importance'     => $item->importance,
                'image'          => $item->image,
                'description'    => $item->description,
                'description_ar' => $item->description_ar,
                'category'       => $this->getItemCategory($item),
                'categories'     => $item->serviceCategories->map(function ($category) {
                    return [
                        'id'         => $category->id,
                        'name'       => $category->name,
                        'name_ar'    => $category->name_ar,
                        'image'      => $category->image ?? null,
                        'status'     => $category->status,
                    ];
                }),
                'services'       => $item->serviceTypes->map(function ($srv) {
                    return [
                        'id'             => $srv->id,
                        'name'           => $srv->name,
                        'name_ar'        => $srv->name_ar,
                        'price'          => $srv->pivot->price,
                        'discount_price' => $srv->pivot->discount_price,
                        'order_priority_id' => $srv->pivot->order_priority_id,
                    ];
                }),
                'order_priority_id' => $order_priority_id,
                'order_priority'    => $order_priority,
                'created_at'     => $item->created_at,
                'updated_at'     => $item->updated_at,
            ];
        });

        return response()->json([
            'data' => $items,
        ]);
    }


    public function assignServices(Request $request)
    {
        $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.item_id'           => 'required|exists:items,id',
            'items.*.order_priority_id' => 'required|exists:order_priorities,id',
            'items.*.services'          => 'required|array|min:1',
            'items.*.services.*.service_type_id' => 'required|exists:service_types,id',
            'items.*.services.*.price'            => 'required|numeric|min:0',
            'items.*.services.*.discount_price'   => 'nullable|numeric|min:0',
        ]);

        $updated = [];
        foreach ($request->items as $entry) {
            $item = Item::find($entry['item_id']);

            $item->services()->detach();

            foreach ($entry['services'] as $svc) {
                $item->services()->attach($svc['service_type_id'], [
                    'price'            => $svc['price'],
                    'discount_price'   => $svc['discount_price'] ?? null,
                    'order_priority_id' => $entry['order_priority_id'],
                ]);
            }

            $priority = \App\Models\OrderPriority::find($entry['order_priority_id']);

            $updated[] = [
                'item_id'  => $item->id,
                'order_priority_id' => $entry['order_priority_id'],
                'order_priority'    => $priority,
                'services' => $item->services->map(function ($s) {
                    return [
                        'service_type_id' => $s->id,
                        'price'           => $s->pivot->price,
                        'discount_price'  => $s->pivot->discount_price,
                        'order_priority_id' => $s->pivot->order_priority_id,
                    ];
                })
            ];
        }

        return response()->json([
            'updated_count' => count($updated),
            'items'         => $updated
        ], 200);
    }


    /**
     * Get the category for the item from the new table or the old table
     */
    private function getItemCategory(Item $item)
    {
        if ($item->serviceCategories->count() > 0) {
            $category = $item->serviceCategories->first();
            return [
                'id'         => $category->id,
                'name'       => $category->name,
                'name_ar'    => $category->name_ar,
                'image'      => $category->image ?? null,
                'status'     => $category->status,
            ];
        }

        if ($item->category) {
            return [
                'id'         => $item->category->id,
                'name'       => $item->category->name,
                'name_ar'    => $item->category->name_ar,
                'image'      => $item->category->image ?? null,
                'status'     => $item->category->status,
            ];
        }

        return [
            'id'         => 0,
            'name'       => 'Uncategorized',
            'name_ar'    => 'غير مصنف',
            'image'      => null,
            'status'     => 1,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
