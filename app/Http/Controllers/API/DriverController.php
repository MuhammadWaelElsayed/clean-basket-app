<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Services\DistanceCalculatorService;
use App\Services\DriverRequestService;
use App\Services\OsrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Driver;
use App\Models\Company;
use App\Models\CompanyNotification;
use App\Models\Order;
use App\Models\DriverNotification;
use App\Models\UserAddress;
use App\Services\FCMService;
use App\Models\Notification;

use App\Models\DriverRequest;
use App\Models\Setting;
use App\Models\OrderTracking;
use App\Services\StatusSmsWhatsappService;
use App\Services\WhatsappBotWebhookService;
use App\Services\ReferralService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{

    protected $driverRequestService;

    public function __construct(DriverRequestService $driverRequestService)
    {
        $this->driverRequestService = $driverRequestService;
    }
    public function login(Request $request)
    {
        $request->validate(["phone" => 'required', 'password' => 'required']);

        $user = Driver::where('phone', $request['phone'])
            ->whereNull('deleted_at')->first();
        if ($user == null) {
            return [
                'status' => false,
                'message' => 'Phone is wronged or not registered!',
                'data' => [],
            ];
        }
        if ($user->status == 0) {
            return [
                'status' => false,
                'message' => 'your account is inactive!',
                'data' => [],
            ];
        }
        $validCredentials = Hash::check($request['password'], $user->password);
        // dd($user->password);
        if ($validCredentials) {
            $user->tokens()->delete();
            if (isset($request->deviceToken) && $request->deviceToken !== null) {
                Driver::find($user->id)->update(['deviceToken' => $request->deviceToken]);
            }
            Driver::find($user->id)->update(['is_online' => 1, "last_online" => now()]);

            return [
                'status' => true,
                'message' => 'Login Success!',
                'data' => [
                    "auth_token" => $user->createToken('tokens')->plainTextToken,
                    "user" => $user
                ],
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Password is wronged!',
                'data' => [],
            ];

        }

    }

    public function signout()
    {
        // dd(auth()->user());
        auth()->user()->tokens()->delete();
        $now = Carbon::now();
        Driver::find(auth()->user()->id)->update(['deviceToken' => null, 'is_online' => 0, 'last_online' => $now]);

        return [
            "status" => true,
            'message' => 'You Logout successfully',
            "data" => []
        ];
    }

    public function getActiveOrders(Request $request)
    {
        $driver = $request->user();

        // Get orders where driver is assigned for pickup OR delivery
        $orders = Order::with(['user', 'deliveryAddress', 'vendor'])
            ->where(function($query) use ($driver) {
                $query->where('pickup_driver_id', $driver->id)
                    ->orWhere('delivery_driver_id', $driver->id);
            })
            ->whereIn('status', [
                'PLACED',
                'PICKED_UP',
                'ON_THE_WAY_FOR_PICKUP',
                'ON_THE_WAY_TO_PARTNER',
                'READY_TO_DELIVER',
                'PICKED_FOR_DELIVER'
            ]);

        if (isset($request->address_type) && $request->address_type != 'All') {
            $orders->whereHas('deliveryAddress', function ($q) use ($request) {
                $q->where('address_type', $request->address_type);
            });
        }

        $orders = $orders->latest('id')->paginate(50);

        // Separate orders by driver role
        $pickupOrders = collect();
        $deliveryOrders = collect();

        // Add driver role information
        $formattedOrders = $orders->getCollection()->map(function($order) use ($driver, &$pickupOrders, &$deliveryOrders) {
            $orderArray = $order->toArray();

            // Determine driver's role in this order
            $driverRole = [];

            // Check if driver is assigned for PICKUP
            if ($order->pickup_driver_id == $driver->id) {
                $driverRole[] = 'PICKUP';
            }

            // Check if driver is assigned for DELIVERY
            if ($order->delivery_driver_id == $driver->id) {
                $driverRole[] = 'DELIVERY';
            }

            $orderArray['driver_role'] = $driverRole;
            $orderArray['current_phase'] = $this->determineOrderPhase($order);

            // Add to pickup_orders if pickup_driver_id matches
            if ($order->pickup_driver_id == $driver->id) {
                $pickupOrders->push($orderArray);
            }

            // Add to delivery_orders if delivery_driver_id matches
            if ($order->delivery_driver_id == $driver->id) {
                $deliveryOrders->push($orderArray);
            }

            return $orderArray;
        });

        if ($formattedOrders->isEmpty()) {
            return [
                'status' => false,
                'message' => "No active orders found!",
            ];
        }

        return [
            'status' => true,
            'message' => "Active orders retrieved successfully!",
            'data' => [
                "orders" => $formattedOrders,
                "pickup_orders" => $pickupOrders,
                "delivery_orders" => $deliveryOrders,
                "pagination" => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total_records' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
                "pickup_pagination" => [
                    'total_records' => $pickupOrders->count(),
                ],
                "delivery_pagination" => [
                    'total_records' => $deliveryOrders->count(),
                ]
            ],
        ];
    }
    public function getCompletedOrders(Request $request)
    {

        $orders = Order::with(['user', 'deliveryAddress', 'vendor'])
            ->where(['driver_id' => auth()->user()->id])->whereIn('status', ['DELIVERED'])->latest('id');

        if (isset($request->address_type) && $request->address_type != 'All') {
            $orders->whereHas('deliveryAddress', function ($q) use ($request) {
                $q->where('address_type', $request->address_type);
            });
        }
        $orders = $orders->paginate(20);

        if (count($orders->items()) < 1) {
            return [
                'status' => false,
                'message' => "No Order Found!",
            ];
        }
        return [
            'status' => true,
            'message' => "Data get successfully!",
            'data' => [
                "orders" => $orders->items(),
                "pagination" => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total_records' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                    'total_pages' => $orders->lastPage(),
                ]
            ],
        ];

    }

    public function getOrderDetails(Request $request)
    {
        $request->validate([
            "order_id" => "required",
        ]);

        $driver = $request->user();

        $order = Order::with([
            'user',
            'deliveryAddress',
            'vendor',
            'orderItems.item',
            'orderItems.serviceType'
        ])
            ->where('id', $request->order_id)
            ->where(function($query) use ($driver) {
                $query->where('pickup_driver_id', $driver->id)
                    ->orWhere('delivery_driver_id', $driver->id);
            })
            ->first();

        if (!$order) {
            return [
                'status' => false,
                'message' => 'Order not found or not assigned to you',
            ];
        }

        // Determine driver's role
        $driverRole = [];
        if ($order->pickup_driver_id == $driver->id) {
            $driverRole[] = 'PICKUP';
        }
        if ($order->delivery_driver_id == $driver->id) {
            $driverRole[] = 'DELIVERY';
        }

        $orderData = $order->toArray();
        $orderData['driver_role'] = $driverRole;
        $orderData['current_phase'] = $this->determineOrderPhase($order);

        return [
            'status' => true,
            'message' => "Order details retrieved successfully!",
            'data' => [
                "order" => $orderData,
            ],
        ];
    }

    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            "order_id" => "required",
            "status" => "required|in:DRAFT,PLACED,PICKED_UP,ON_THE_WAY_FOR_PICKUP,ON_THE_WAY_TO_PARTNER,ARRIVED,PROCESSING,CONFIRMED_PAID,READY_TO_DELIVER,PICKED_FOR_DELIVER,DELIVERED,CANCELLED",
        ]);

        $driver = $request->user();
        $orderIds = explode(',', $request->order_id);

        foreach ($orderIds as $orderId) {
            $order = Order::with(['user', 'vendor', 'deliveryAddress'])->findOrFail($orderId);

            if($request->status == 'READY_TO_DELIVER') {
                $result = (new DriverRequestService())->sendDeliveryRequestToDrivers(
                    $order,
                    $order->vendor_id,
                    $order->deliveryAddress->lat,
                    $order->deliveryAddress->lng
                );
            }
            // Handle trip updates based on status
            $this->updateTripStatus($order, $driver, $request->status, $driver->lat, $driver->lng);

            // Handle notifications based on status
            $this->handleStatusNotifications($order, $request->status);

            if ($request->status != 'DELIVERED') {
                $order->update(['status' => $request->status]);
            }

            // Handle DELIVERED status
            if ($request->status == 'DELIVERED') {
                $this->handleDeliveredOrder($request, $order, $driver);
            }

            // Handle CANCELLED status
            if ($request->status == 'CANCELLED') {
                $this->handleCancelledOrder($order, $driver);
            }

            // Track order status
            OrderTracking::firstOrCreate(
                ['order_id' => $orderId, 'status' => $request->status],
                ['order_id' => $orderId, 'status' => $request->status]
            );

            $webhookService = new WhatsappBotWebhookService();
            $webhookService->sendOrderCompleted($order->user->phone);
            Log::info('Order Completed Webhook Sent: ' . $order->user->phone);
        }

        return [
            'status' => true,
            'message' => "Order Status updated successfully!",
        ];
    }

    /**
     * Handle notifications based on order status
     */
    private function handleStatusNotifications($order, $status)
    {
        $notifications = [
            'PLACED' => [
                'recipient' => 'user',
                'title' => "Your laundry order #$order->order_code has been placed",
                'title_ar' => "تم تقديم طلب الغسيل #$order->order_code",
            ],
            'ON_THE_WAY_FOR_PICKUP' => [
                'recipient' => 'user',
                'title' => "Driver is on the way to pick up your laundry #$order->order_code",
                'title_ar' => "السائق في الطريق لاستلام غسيلك #$order->order_code",
            ],
            'PICKED_UP' => [
                'recipient' => 'user',
                'title' => "Your laundry #$order->order_code has been picked up",
                'title_ar' => "تم استلام غسيلك #$order->order_code",
            ],
            'ON_THE_WAY_TO_PARTNER' => [
                'recipient' => 'vendor',
                'title' => "Driver is on the way with laundry order #$order->order_code",
                'title_ar' => "السائق في الطريق مع طلب الغسيل #$order->order_code",
            ],
            'ARRIVED' => [
                'recipient' => 'vendor',
                'title' => "Driver has arrived with laundry order #$order->order_code",
                'title_ar' => "وصل السائق مع طلب الغسيل #$order->order_code",
            ],
            'PROCESSING' => [
                'recipient' => 'user',
                'title' => "Your laundry #$order->order_code is being processed",
                'title_ar' => "غسيلك #$order->order_code قيد المعالجة",
            ],
            'CONFIRMED_PAID' => [
                'recipient' => 'user',
                'title' => "Your order #$order->order_code payment confirmed",
                'title_ar' => "تم تأكيد دفع طلبك #$order->order_code",
            ],
            'READY_TO_DELIVER' => [
                'recipient' => 'user',
                'title' => "Your clean laundry #$order->order_code is ready for delivery",
                'title_ar' => "غسيلك النظيف #$order->order_code جاهز للتوصيل",
            ],
            'PICKED_FOR_DELIVER' => [
                'recipient' => 'user',
                'title' => "Driver picked up your clean laundry #$order->order_code for delivery",
                'title_ar' => "السائق استلم غسيلك النظيف #$order->order_code للتوصيل",
            ],
            'DELIVERED' => [
                'recipient' => 'user',
                'title' => "Your clean laundry #$order->order_code has been delivered",
                'title_ar' => "تم توصيل غسيلك النظيف #$order->order_code",
            ],
        ];

        if (isset($notifications[$status])) {
            $notif = $notifications[$status];
            $data = [
                "title" => $notif['title'],
                "title_ar" => $notif['title_ar'],
                "message" => $notif['title'],
                "message_ar" => $notif['title_ar'],
                "order" => $order,
                "user" => $notif['recipient'] === 'vendor' ? $order->vendor : $order->user,
            ];
            $this->sendNotifications($data, $notif['recipient']);
        }
    }

    /**
     * Update trip status based on order status
     */
    /**
     * Update trip status based on order status
     */
    private function updateTripStatus($order, $driver, $status, $currentLat = null, $currentLng = null)
    {
        // Pickup-related statuses (customer → vendor)
        $pickupStatuses = ['ON_THE_WAY_FOR_PICKUP', 'PICKED_UP', 'ON_THE_WAY_TO_PARTNER', 'ARRIVED'];

        // Delivery-related statuses (vendor → customer)
        $deliveryStatuses = ['READY_TO_DELIVER', 'PICKED_FOR_DELIVER', 'DELIVERED'];

        // Determine trip type
        $tripType = null;
        if (in_array($status, $pickupStatuses)) {
            $tripType = 'pickup';
        } elseif (in_array($status, $deliveryStatuses)) {
            $tripType = 'delivery';
        }

        if (!$tripType) {
            return; // No trip update needed for this status
        }

        // Find existing trip
        $trip = Trip::where('order_id', $order->id)
            ->where('driver_id', $driver->id)
            ->where('type', $tripType)
            ->first();

        if (!$trip) {
            // Create trip if it doesn't exist (shouldn't happen normally)
            $trip = Trip::create([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'client_id' => $order->user_id,
                'type' => $tripType,
                'status' => 'new',
            ]);
        }

        $distanceCalculator = new DistanceCalculatorService();

        // Update trip based on order status
        switch ($status) {
            case 'ON_THE_WAY_FOR_PICKUP':
                // Driver is heading to customer to pick up laundry
                $updateData = [
                    'status' => 'in-progress',
                ];

                if (!$trip->started_at) {
                    $updateData['started_at'] = now();
                }

                // Record driver's current location as start point
                if ($currentLat && $currentLng && !$trip->start_lat) {
                    $updateData['start_lat'] = $currentLat;
                    $updateData['start_lng'] = $currentLng;
                }

                $trip->update($updateData);
                break;

            case 'PICKED_UP':
                // Driver picked up laundry from customer
                $trip->update([
                    'status' => 'in-progress',
                    'is_picked_up' => true,
                ]);
                break;

            case 'ON_THE_WAY_TO_PARTNER':
                // Driver is heading to vendor (laundry facility) with the package
                $trip->update([
                    'status' => 'in-progress',
                    'is_picked_up' => true,
                ]);
                break;

            case 'ARRIVED':
                // Driver arrived at vendor (laundry facility) - pickup trip complete
                $updateData = [
                    'status' => 'completed',
                    'completed_at' => now(),
                ];

                // Set end location to vendor
                if ($order->vendor) {
                    $updateData['end_lat'] = $order->vendor->latitude;
                    $updateData['end_lng'] = $order->vendor->longitude;
                } elseif ($currentLat && $currentLng) {
                    $updateData['end_lat'] = $currentLat;
                    $updateData['end_lng'] = $currentLng;
                }

                // Set start location if not already set (fallback to customer location)
                if (!$trip->start_lat && $order->latitude && $order->longitude) {
                    $updateData['start_lat'] = $order->latitude;
                    $updateData['start_lng'] = $order->longitude;
                }

                // Calculate pickup trip distance using the service
                $trip->update($updateData);
                $trip->refresh();

                $distance = $distanceCalculator->calculatePickupTripDistance($trip, $order);
                if ($distance > 0) {
                    $trip->update(['distance_km' => $distance]);
                }

                // Create delivery trip if it doesn't exist
                $deliveryTrip = Trip::where('order_id', $order->id)
                    ->where('driver_id', $driver->id)
                    ->where('type', 'delivery')
                    ->first();

                if (!$deliveryTrip) {
                    Trip::create([
                        'order_id' => $order->id,
                        'driver_id' => $driver->id,
                        'client_id' => $order->user_id,
                        'type' => 'delivery',
                        'status' => 'new',
                    ]);
                }
                break;

            case 'READY_TO_DELIVER':
                // Laundry is ready, delivery trip scheduled
                if ($trip->status === 'new') {
                    $trip->update(['status' => 'scheduled']);
                }
                break;

            case 'PICKED_FOR_DELIVER':
                // Driver picked up clean laundry from vendor
                $updateData = [
                    'status' => 'in-progress',
                    'is_picked_up' => true,
                ];

                if (!$trip->started_at) {
                    $updateData['started_at'] = now();
                }

                // Set start location (vendor location or current location)
                if (!$trip->start_lat) {
                    if ($order->vendor && $order->vendor->latitude) {
                        $updateData['start_lat'] = $order->vendor->latitude;
                        $updateData['start_lng'] = $order->vendor->longitude;
                    } elseif ($currentLat && $currentLng) {
                        $updateData['start_lat'] = $currentLat;
                        $updateData['start_lng'] = $currentLng;
                    }
                }

                $trip->update($updateData);
                break;

            case 'DELIVERED':
                // Driver delivered clean laundry to customer - delivery trip complete
                $updateData = [
                    'status' => 'completed',
                    'completed_at' => now(),
                ];

                // Set end location (customer location)
                if ($order->latitude && $order->longitude) {
                    $updateData['end_lat'] = $order->latitude;
                    $updateData['end_lng'] = $order->longitude;
                } elseif ($currentLat && $currentLng) {
                    $updateData['end_lat'] = $currentLat;
                    $updateData['end_lng'] = $currentLng;
                }

                // Set start location if not already set (fallback to vendor location)
                if (!$trip->start_lat && $order->vendor) {
                    $updateData['start_lat'] = $order->vendor->latitude;
                    $updateData['start_lng'] = $order->vendor->longitude;
                }

                // Calculate delivery trip distance using the service
                $trip->update($updateData);
                $trip->refresh();

                $distance = $distanceCalculator->calculateDeliveryTripDistance($trip, $order);
                if ($distance > 0) {
                    $trip->update(['distance_km' => $distance]);
                }
                break;
        }
    }    /**
     * Handle cancelled order
     */
    private function handleCancelledOrder($order, $driver)
    {
        // Cancel all active trips for this order
        Trip::where('order_id', $order->id)
            ->whereIn('status', ['new', 'assigned', 'in-progress', 'scheduled'])
            ->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

        // Free up driver if they were assigned
        if ($driver) {
            Driver::find($driver->id)->update(['is_free' => 1]);
        }

        // Send notifications
        $data = [
            "title" => "Order #$order->order_code has been cancelled",
            "title_ar" => "تم إلغاء الطلب #$order->order_code",
            "message" => "Order #$order->order_code has been cancelled",
            "message_ar" => "تم إلغاء الطلب #$order->order_code",
            "order" => $order,
            "user" => $order->user,
        ];
        $this->sendNotifications($data, 'user');

        // Notify vendor
        $data['user'] = $order->vendor;
        $this->sendNotifications($data, 'vendor');
    }

    /**
     * Handle delivered order logic
     */
    private function handleDeliveredOrder($request, $order, $driver)
    {
        $image = $request->file('deliver_image');
        $deliver_lat = $request->deliver_lat ?: 0;
        $deliver_lng = $request->deliver_lng ?: 0;

        if ($image && $image->isValid()) {
            $path = 'uploads';
            $extension = $image->getClientOriginalExtension();
            $FileName = uniqid() . '_delivery_img.' . $extension;
            $image->move(public_path($path), $FileName);

            Order::find($order->id)->update([
                'deliver_image' => $FileName,
                'deliver_lat' => $deliver_lat,
                'deliver_lng' => $deliver_lng,
                'status' => 'DELIVERED'
            ]);
        } else {
            Order::find($order->id)->update(['status' => 'DELIVERED']);
        }

        // Free up driver
        Driver::find($driver->id)->update(['is_free' => 1]);

        // Send notifications to user
        $data = [
            "title" => "Your order #$order->order_code has been Delivered",
            "title_ar" => "طلبك #$order->order_code تم توصيله",
            "message" => "Your order #$order->order_code has been Delivered",
            "message_ar" => "طلبك #$order->order_code تم توصيله",
            "order" => $order,
            "user" => $order->user,
        ];
        $this->sendNotifications($data, 'user');

        // SMS/WhatsApp notification
        $statusSmsWhatsappService = new StatusSmsWhatsappService();
        $statusSmsWhatsappService->deliverOrder(
            $order->user->first_name . ' ' . $order->user->last_name,
            $order->user->phone
        );

        // Send notification to vendor
        $data = [
            "title" => "Order #$order->order_code has been Delivered to customer",
            "title_ar" => "الطلب #$order->order_code تم توصيله للعميل",
            "message" => "Order #$order->order_code has been Delivered to customer",
            "message_ar" => "الطلب #$order->order_code تم توصيله للعميل",
            "order" => $order,
            "user" => $order->vendor,
        ];
        $this->sendNotifications($data, 'vendor');

        // Send notification to admin
        $data = [
            "title" => "Order #$order->order_code is Delivered",
            "message" => "Order #$order->order_code is Delivered to customer. Check its more details.",
            "link" => "admin/order-details/" . $order->id,
        ];
        $this->sendNotifications($data, 'admin');

        // Process referral reward
        $referralService = new ReferralService();
        $referralService->processReferralReward($order);
    }
    public function getBasketRequests(Request $request)
    {

        $requests = UserAddress::with('user')->where('driver_id', auth()->user()->id)
            ->whereNull('deleted_at')->latest();
        if ($request->status) {
            $requests->where('basket_status', $request->status);
        }
        if (isset($request->address_type) && $request->address_type != 'All') {
            $requests->where('address_type', $request->address_type);
        }

        $requests = $requests->paginate(20);

        if (count($requests->items()) < 1) {
            return [
                'status' => false,
                'message' => "No Basket Request right now!",
            ];
        }
        return [
            'status' => true,
            'message' => "Data get successfully!",
            'data' => [
                "requests" => $requests->items(),
                "pagination" => [
                    'current_page' => $requests->currentPage(),
                    'per_page' => $requests->perPage(),
                    'total_records' => $requests->total(),
                    'last_page' => $requests->lastPage(),
                ]
            ],
        ];
    }

    public function updateBasketStatus(Request $request)
    {
        $request->validate([
            "request_id" => "required",
            "status" => "required|in:Delivered",
            // "basket_no"=>"required| unique:user_address,basket_no,NULL,id,deleted_at,NULL",
            "deliver_image" => "required|mimes:png,jpg,jpeg,gif"
        ]);
        if (isset($request->basket_no) && $request->basket_no != '') {
            $isExist = UserAddress::where(['basket_no' => $request->basket_no, 'deleted_at' => null])->count();
            if ($isExist > 0) {
                return [
                    "status" => false,
                    "message" => __('api')['basket_already'],
                ];
            }
        }

        $address = UserAddress::with('user')->findOrFail($request->request_id);

        $image = $request->file('deliver_image');
        $deliver_lat = $request->deliver_lat ?: 0;
        $deliver_lng = $request->deliver_lng ?: 0;
        $imageName = '';
        if ($image) {
            $imageName = $this->optimizeImage($image);
        }
        $updateData = [
            "basket_status" => "Delivered",
            'deliver_image' => $imageName,
        ];
        if ($address->address_type == "Apartment") {
            $updateData['basket_no'] = $request->basket_no;
        }
        $address->update($updateData);

        $data = [
            "title" => "Your basket is delivered to you",
            "title_ar" => "سلتك تتجهز 👏🏻 قريبا الفريق بيوصلها لك ",
            "message" => "Your basket is delivered to you",
            "message_ar" => "سلتك تتجهز 👏🏻 قريبا الفريق بيوصلها لك ",
            // "mail" => [
            //     "template"=>"basket_delivered"
            // ],
            "user" => $address->user
        ];
        try {
            $this->sendNotifications($data, 'user');
        } catch (\Exception $ex) {
            // dd($ex->getMessage());
        }
        return [
            'status' => true,
            'message' => "Basket Request Status updated successfully!",
        ];
    }


    public function optimizeImage($upImage)
    {
        $maxWidth = 1000;
        $maxHeight = 1000;

        // Get image dimensions
        list($width, $height) = getimagesize($upImage);

        // Calculate the new dimensions while maintaining aspect ratio
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;

        // Create a new image resource
        $image = imagecreatetruecolor($newWidth, $newHeight);

        // Determine the image type (JPEG, PNG, GIF)
        $imageType = exif_imagetype($upImage);

        // Load the original image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($upImage);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($upImage);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($upImage);
                break;
            default:
                dd('Unsupported image type');
        }

        // Resize and save the optimized image
        imagecopyresampled($image, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $imageName = uniqid() . '_delivery_img.jpg';
        $outputPath = public_path('storage/uploads/' . $imageName);
        //$outputPath = 'public/uploads/' . $imageName; // You can choose a different format if needed
        imagejpeg($image, $outputPath, 80); // Adjust the quality (0-100) as needed

        // Clean up resources
        imagedestroy($image);
        imagedestroy($source);

        return $imageName;
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            "lat" => "required",
            "lng" => "required",
        ]);

        $now = Carbon::now();
        $user = Driver::whereId($request->user()->id)->update(
            [
                "lat" => $request->lat,
                "lng" => $request->lng,
                "location" => $request->address ?: '',
                "last_online" => $now,
            ]);
        if ($user) {
            return [
                'status' => true,
                'message' => "Driver Location updated successfully!",
            ];
        }
    }

    public function getProfile(Request $request)
    {

        $user = Driver::whereId($request->user()->id)->first();

        if ($user) {
            return [
                'status' => true,
                'message' => "Profile get successfully!",
                'data' => $user,
            ];
        }
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'picture' => 'required|file|mimes:png,jpg,jpeg,gif',
        ]);

        $data = [
            'last_online' => now(),
        ];
        if ($request->name) {
            $data['name'] = $request->name;
        }
        $image = $request->file('picture');
        if ($image) {
            $imageName = date('YmdHis') . "_driver." . $image->getClientOriginalExtension();
            // $image->move(base_path('/uploads/'), $imageName);
            $path = $image->storeAs('public/uploads', $imageName);

            $data['picture'] = $imageName;
        }
        if ($request->address) {
            $data['location'] = $request->address;
        }
        if ($request->lat) {
            $data['lat'] = $request->lat;
        }
        if ($request->lng) {
            $data['lng'] = $request->lng;
        }
        // else{  $imageName ="null";}

        $driver = Driver::whereId($request->user()->id)->firstOrFail();
        $driver->update($data);
        $driver->refresh();
        return [
            'status' => true,
            'message' => "Profile updated successfully!",
            'data' => $driver,
        ];
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            "fcm_token" => "required",
        ]);

        $driver = Driver::whereId($request->user()->id)->firstOrFail();

        $driver->update([
            "deviceToken" => $request->fcm_token,
        ]);

        return response()->json(['status' => true, 'message' => "FCM Token updated successfully."]);
    }

    public function deleteAccount(Request $request)
    {
        // dd(auth()->user()->id);

        $now = Carbon::now();
        $user = Driver::find(auth()->user()->id)->update(['status' => 0]);

        auth()->user()->tokens()->delete();
        return [
            'status' => true,
            'message' => "Account is deleted successfully!",
        ];


    }

    public function getNotifcations(Request $request)
    {
        $notification = DriverNotification::where('driver_id', $request->user()->id);

        if (isset($request->unread) && $request->unread == 1) {
            $notification->whereNull('read_at');
        }

        $notification = $notification->latest('id')->get();
        if ($notification) {
            return [
                'status' => true,
                'message' => "Notifications get successfully",
                'data' => $notification,
            ];
        }
    }

    public function getNotifcationBadge(Request $request)
    {
        $count = DriverNotification::where('driver_id', $request->user()->id)->whereNull('read_at')->count();

        return [
            'status' => true,
            'message' => "Notification badge get successfully",
            'data' => [
                "badge_count" => $count
            ],
        ];

    }

    public function markasReadNotifcation(Request $request)
    {
        $request->validate([
            "notification_id" => "required",
        ]);

        $now = Carbon::now();
        $notification = DriverNotification::findOrFail($request->notification_id)->update([
            "read_at" => $now
        ]);

        if ($notification) {
            return [
                'status' => true,
                'message' => "Success! Notification is marked as read",
                'data' => [],
            ];
        }

    }

    public function clearAllNotifications(Request $request)
    {

        $now = Carbon::now();
        $notification = DriverNotification::where(['driver_id' => auth()->user()->id])->delete();

        return [
            'status' => true,
            'message' => "All notifications is cleared",
            'data' => [],
        ];

    }

    public function updateOnlineStatus(Request $request)
    {
        $request->validate([
            "is_online" => "required",
        ]);

        $user = Driver::whereId($request->user()->id)->update(
            [
                "is_online" => $request->is_online
            ]);
        return [
            'status' => true,
            'message' => "Driver online status updated successfully!",
        ];
    }

    public function signupFreelance(Request $request)
    {
        $data = $request->validate([
            "name" => "required|max:255",
            "phone" => "required|unique:drivers,phone|regex:/^966\d{7,10}$/",
            'vehicle_type' => 'required|in:MOTORCYCLE,CAR,VAN,TRUCK',
            "password" => "required|min:6|max:255",
            "vehicle_plate" => "required|unique:drivers,vehicle_plate|max:255",
            'picture' => 'required|file|mimes:png,jpg,jpeg,gif',
            'license' => 'required|file|mimes:png,jpg,jpeg,gif,pdf',
            'id_image' => 'required|file|mimes:png,jpg,jpeg,gif,pdf',
            "device_token" => "required|max:255",
        ]);

        $data['password'] = bcrypt($data['password']);
        $data['deviceToken'] = $request->device_token;
        unset($data['device_token']);

        // Handle license file upload
        if ($request->hasFile('license')) {
            $licenseFile = $request->file('license');
            $licenseName = uniqid() . '_license.' . $licenseFile->getClientOriginalExtension();
            $licenseFile->move(public_path('storage/uploads'), $licenseName);
            $data['license'] = $licenseName;
        }
        if ($request->hasFile('id_image')) {
            $file = $request->file('id_image');
            $name = uniqid() . '_id.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/uploads'), $name);
            $data['id_image'] = $name;
        }
        $driver = Driver::create(array_merge($data,
            [
                'picture' => $this->optimizeImage($request->file('picture')),
                'status' => 1,
                'notification_enable' => 1,
                'is_online' => 1,
                'role' => 'FREELANCE',
                "last_online" => now(),
            ]
        ));

        return response()->json([
            'status' => true,
            'message' => "Driver signup successfully!",
            'data' => [
                "auth_token" => $driver->createToken('tokens')->plainTextToken,
                'driver' => $driver->toArray(),
            ],
        ]);
    }

    public function getOptimizedRoute(Request $request)
    {
        // Validate request data
        $request->validate([
            'locations' => 'required|array|min:2',
            'locations.*.lat' => 'required|numeric|between:-90,90',
            'locations.*.lon' => 'required|numeric|between:-180,180',
            'locations.*.name' => 'required|string|max:255',
        ]);

        // Get locations from request
        $locations = collect($request->locations)->map(function ($location) {
            return [
                'lat' => (float)$location['lat'],
                'lon' => (float)$location['lon'],
                'name' => $location['name']
            ];
        })->toArray();

        // Validate minimum number of locations after processing
        if (count($locations) < 2) {
            return response()->json([
                'status' => false,
                'message' => 'At least 2 locations are required for route optimization',
            ], 422);
        }

        try {
            $osrm = new OsrmService();
            $result = $osrm->getOptimizedTrip($locations, roundtrip: true);

            // Map original names to waypoints based on waypoint_index
            $waypoints = collect($result['waypoints'])->map(function ($waypoint) use ($locations) {
                $originalIndex = $waypoint['waypoint_index'];

                // Add original name to waypoint
                if (isset($locations[$originalIndex])) {
                    $waypoint['original_name'] = $locations[$originalIndex]['name'];
                }

                return $waypoint;
            })->toArray();

            $newRs['waypoints'] = $waypoints;
            $optimizedOrderIndices = $osrm->getOptimizedOrder($result);
            $optimizedLocations = collect($locations)->only($optimizedOrderIndices)->values();

            return response()->json([
                'status' => true,
                'total_duration_seconds' => $result['trips'][0]['duration'],
                'total_distance_meters' => $result['trips'][0]['distance'],
                'optimized_order' => $optimizedLocations->pluck('name'),
                'route_geometry' => $osrm->getRouteGeometry($result),
                'full_response' => $newRs,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'error' => $e->getMessage()
                ], 500);
        }
    }

    public function getPendingOrders(Request $request)
    {
        $driver = $request->user();

        // Expire old requests first
        $this->driverRequestService->expireOldRequests();

        // Get pending requests with order details
        $pendingRequests = DriverRequest::with([
            'order.user',
            'order.deliveryAddress',
            'order.vendor',
            'order.orderItems.item',
            'order.orderItems.serviceType'
        ])
            ->where('driver_id', $driver->id)
            ->pending()
            ->orderBy('request_type', 'asc') // DELIVERY first, then PICKUP
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($pendingRequests->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No pending order requests at the moment',
                'data' => [
                    'requests' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 20,
                        'total_records' => 0,
                        'last_page' => 1,
                    ]
                ],
            ]);
        }

        // Format response
        $formattedRequests = $pendingRequests->getCollection()->map(function ($request) use ($driver) {
            $order = $request->order;

            // Calculate distance
            $distance = null;
            if ($driver->lat && $driver->lng && $order->deliveryAddress) {
                $distance = $this->calculateDistance(
                    $driver->lat,
                    $driver->lng,
                    $order->deliveryAddress->lat,
                    $order->deliveryAddress->lng
                );
            }

            // Determine location based on request type
            $targetLocation = null;
            if ($request->request_type === 'PICKUP') {
                $targetLocation = $order->deliveryAddress; // Customer location for pickup
            } else {
                $targetLocation = $order->vendor; // Vendor location for delivery pickup
            }

            return [
                'request_id' => $request->id,
                'request_type' => $request->request_type, // PICKUP or DELIVERY
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'order_status' => $order->status,
                'pickup_date' => $order->pickup_date,
                'pickup_time' => $order->pickup_time,
                'dropoff_date' => $order->dropoff_date,
                'dropoff_time' => $order->dropoff_time,
                'expires_at' => $request->expires_at,
                'time_remaining_minutes' => now()->diffInMinutes($request->expires_at, false),
                'distance_km' => $distance ? round($distance, 2) : null,
                'target_location' => $request->request_type === 'PICKUP' ? [
                    'type' => 'customer',
                    'address_type' => $order->deliveryAddress->address_type ?? null,
                    'area' => $order->deliveryAddress->area ?? null,
                    'street_no' => $order->deliveryAddress->street_no ?? null,
                    'house_no' => $order->deliveryAddress->house_no ?? null,
                    'lat' => $order->deliveryAddress->lat ?? null,
                    'lng' => $order->deliveryAddress->lng ?? null,
                ] : [
                    'type' => 'vendor',
                    'name' => $order->vendor->name ?? null,
                    'name_ar' => $order->vendor->name_ar ?? null,
                    'address' => $order->vendor->address ?? null,
                    'lat' => $order->vendor->lat ?? null,
                    'lng' => $order->vendor->lng ?? null,
                ],
                'destination' => $request->request_type === 'PICKUP' ? [
                    'type' => 'vendor',
                    'name' => $order->vendor->name ?? null,
                    'lat' => $order->vendor->lat ?? null,
                    'lng' => $order->vendor->lng ?? null,
                ] : [
                    'type' => 'customer',
                    'area' => $order->deliveryAddress->area ?? null,
                    'lat' => $order->deliveryAddress->lat ?? null,
                    'lng' => $order->deliveryAddress->lng ?? null,
                ],
                'user' => [
                    'name' => $order->user->name,
                    'phone' => $order->user->phone,
                ],
                'items_count' => $order->orderItems->count(),
                'grand_total' => $order->grand_total,
            ];
        });

        // Separate by type for easier UI handling
        $pickupRequests = $formattedRequests->where('request_type', 'PICKUP')->values();
        $deliveryRequests = $formattedRequests->where('request_type', 'DELIVERY')->values();

        return response()->json([
            'status' => true,
            'message' => 'Pending order requests retrieved successfully',
            'data' => [
                'all_requests' => $formattedRequests,
                'pickup_requests' => $pickupRequests,
                'delivery_requests' => $deliveryRequests,
                'counts' => [
                    'total' => $formattedRequests->count(),
                    'pickup' => $pickupRequests->count(),
                    'delivery' => $deliveryRequests->count(),
                ],
                'pagination' => [
                    'current_page' => $pendingRequests->currentPage(),
                    'per_page' => $pendingRequests->perPage(),
                    'total_records' => $pendingRequests->total(),
                    'last_page' => $pendingRequests->lastPage(),
                ]
            ],
        ]);
    }

    /**
     * Accept a pending order request
     */
    public function acceptPendingOrder(Request $request)
    {
        $request->validate([
            'request_id' => 'required|string',
        ]);

        $driver = $request->user();

        // Parse request_id - support both single ID and comma-separated IDs
        $requestIds = array_filter(array_map('trim', explode(',', $request->request_id)));

        if (empty($requestIds)) {
            return response()->json([
                'status' => false,
                'message' => 'No valid request IDs provided',
            ], 422);
        }

        // Validate all IDs exist in driver_requests table
        $existingIds = DriverRequest::whereIn('id', $requestIds)->pluck('id')->toArray();
        $invalidIds = array_diff($requestIds, $existingIds);

        if (!empty($invalidIds)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid request IDs: ' . implode(', ', $invalidIds),
            ], 422);
        }

        // Fetch all driver requests
        $driverRequests = DriverRequest::with('order')
            ->whereIn('id', $requestIds)
            ->get();

        // Check ownership
        $unauthorizedRequests = $driverRequests->filter(function($dr) use ($driver) {
            return $dr->driver_id !== $driver->id;
        });

        if ($unauthorizedRequests->isNotEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Some requests do not belong to you',
            ], 403);
        }

        $results = [];
        $errors = [];

        // Process each request
        foreach ($driverRequests as $driverRequest) {
            $result = $this->driverRequestService->acceptOrder($driverRequest, $driver);

            if (!$result['success']) {
                $errors[] = [
                    'request_id' => $driverRequest->id,
                    'message' => $result['message'],
                ];
                continue;
            }

            // Create or find trip
            $trip = Trip::firstOrCreate(
                [
                    'order_id' => $driverRequest->order_id,
                    'driver_id' => $driver->id,
                    'type' => strtolower($driverRequest->request_type),
                ],
                [
                    'client_id' => $driverRequest->order->user_id,
                    'status' => 'new',
                ]
            );

            $results[] = [
                'request_id' => $driverRequest->id,
                'order' => $result['order'],
                'request_type' => $driverRequest->request_type,
                'trip' => $trip,
            ];
        }

        // Return response based on results
        if (empty($results) && !empty($errors)) {
            return response()->json([
                'status' => false,
                'message' => 'All requests failed',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => count($results) . ' order(s) accepted successfully',
            'data' => $results,
            'errors' => $errors, // Include any partial failures
        ]);
    }

    /**
     * Reject a pending order request
     */
    public function rejectPendingOrder(Request $request)
    {
        $request->validate([
            'request_id' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $driver = $request->user();

        // Parse request_id - support both single ID and comma-separated IDs
        $requestIds = array_filter(array_map('trim', explode(',', $request->request_id)));

        if (empty($requestIds)) {
            return response()->json([
                'status' => false,
                'message' => 'No valid request IDs provided',
            ], 422);
        }

        // Validate all IDs exist in driver_requests table
        $existingIds = DriverRequest::whereIn('id', $requestIds)->pluck('id')->toArray();
        $invalidIds = array_diff($requestIds, $existingIds);

        if (!empty($invalidIds)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid request IDs: ' . implode(', ', $invalidIds),
            ], 422);
        }

        // Fetch all driver requests
        $driverRequests = DriverRequest::whereIn('id', $requestIds)->get();

        // Check ownership
        $unauthorizedRequests = $driverRequests->filter(function($dr) use ($driver) {
            return $dr->driver_id !== $driver->id;
        });

        if ($unauthorizedRequests->isNotEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Some requests do not belong to you',
            ], 403);
        }

        $results = [];
        $errors = [];

        // Process each request
        foreach ($driverRequests as $driverRequest) {
            $result = $this->driverRequestService->rejectOrder(
                $driverRequest,
                $request->reason
            );

            if (!$result['success']) {
                $errors[] = [
                    'request_id' => $driverRequest->id,
                    'message' => $result['message'],
                ];
                continue;
            }

            $results[] = [
                'request_id' => $driverRequest->id,
                'message' => $result['message'],
            ];
        }

        // Return response based on results
        if (empty($results) && !empty($errors)) {
            return response()->json([
                'status' => false,
                'message' => 'All requests failed to reject',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => count($results) . ' order(s) rejected successfully',
            'data' => $results,
            'errors' => $errors, // Include any partial failures
        ]);
    }


    /**
     * Get details of a pending order request
     */
    public function getPendingOrderDetails(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:driver_requests,id',
        ]);

        $driver = $request->user();

        $driverRequest = DriverRequest::with([
            'order.user',
            'order.deliveryAddress',
            'order.vendor',
            'order.orderItems.item',
            'order.orderItems.serviceType',
            'order.orderItems.addOns'
        ])->findOrFail($request->request_id);

        // Verify this request belongs to the authenticated driver
        if ($driverRequest->driver_id !== $driver->id) {
            return response()->json([
                'status' => false,
                'message' => 'This request does not belong to you',
            ], 403);
        }

        // Check if expired
        if ($driverRequest->is_expired) {
            $driverRequest->markAsExpired();
            return response()->json([
                'status' => false,
                'message' => 'This request has expired',
            ], 410);
        }

        $order = $driverRequest->order;

        // Calculate distance
        $distance = null;
        if ($driver->lat && $driver->lng && $order->deliveryAddress) {
            $distance = $this->calculateDistance(
                $driver->lat,
                $driver->lng,
                $order->deliveryAddress->lat,
                $order->deliveryAddress->lng
            );
        }

        // Format order items
        $orderItems = $order->orderItems->map(function ($item) {
            return [
                'id' => $item->id,
                'item_name' => $item->item->name,
                'item_image' => $item->item->image,
                'service_type_name' => $item->serviceType->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total_price' => $item->total_price,
                'add_ons' => $item->addOns->map(function ($addOn) {
                    return [
                        'name' => $addOn->name,
                        'price' => $addOn->pivot->price,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Order request details retrieved successfully',
            'data' => [
                'request_id' => $driverRequest->id,
                'order_code' => $order->order_code,
                'pickup_date' => $order->pickup_date,
                'pickup_time' => $order->pickup_time,
                'dropoff_date' => $order->dropoff_date,
                'dropoff_time' => $order->dropoff_time,
                'instructions' => $order->instructions,
                'status' => $order->status,
                'expires_at' => $driverRequest->expires_at,
                'time_remaining_minutes' => now()->diffInMinutes($driverRequest->expires_at, false),
                'distance_km' => $distance ? round($distance, 2) : null,
                'delivery_address' => $order->deliveryAddress,
                'vendor' => $order->vendor,
                'user' => [
                    'name' => $order->user->name,
                    'phone' => $order->user->phone,
                ],
                'order_items' => $orderItems,
                'sub_total' => $order->sub_total,
                'delivery_fee' => $order->delivery_fee,
                'service_fee' => $order->service_fee,
                'vat' => $order->vat,
                'grand_total' => $order->grand_total,
            ],
        ]);
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }
    protected function determineOrderPhase($order)
    {
        $pickupPhases = ['PLACED', 'PICKED_UP', 'ON_THE_WAY_FOR_PICKUP', 'ON_THE_WAY_TO_PARTNER', 'ARRIVED'];
        $deliveryPhases = ['READY_TO_DELIVER', 'PICKED_FOR_DELIVER'];

        if (in_array($order->status, $pickupPhases)) {
            return 'PICKUP_PHASE';
        } elseif (in_array($order->status, $deliveryPhases)) {
            return 'DELIVERY_PHASE';
        }

        return 'PROCESSING';
    }
}
