<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StationaryRequest;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order from an approved stationary request.
     */
    public function createOrderFromRequest(StationaryRequest $request, Vendor $vendor): Order
    {
        return DB::transaction(function () use ($request, $vendor) {
            $order = Order::create([
                'stationary_request_id' => $request->id,
                'vendor_id' => $vendor->id,
                'status' => 'pending',
                'total_amount' => $request->total_amount,
            ]);

            foreach ($request->requestItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);
            }

            return $order;
        });
    }

    /**
     * Update the status of an order and sync the request status if completed.
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $order->update(['status' => $status]);

            if ($status === 'delivered') {
                // Also complete the original request
                $order->stationaryRequest->update(['status' => 'completed']);
                
                // Dispatch event for completion
                event(new \App\Events\RequestStatusChanged($order->stationaryRequest, auth()->user(), 'completed'));
            }

            return $order;
        });
    }
}
