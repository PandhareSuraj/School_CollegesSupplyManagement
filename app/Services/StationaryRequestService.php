<?php

namespace App\Services;

use App\Models\StationaryRequest;
use App\Models\RequestItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StationaryRequestService
{
    /**
     * Create a new stationary request along with its items.
     */
    public function createRequest(array $items, User $user, ?string $notes = null): StationaryRequest
    {
        return DB::transaction(function () use ($items, $user, $notes) {
            $totalAmount = 0;

            // Create the main request
            $request = StationaryRequest::create([
                'department_id' => $user->department_id,
                'requested_by' => $user->id,
                'status' => 'pending',
                'total_amount' => 0, // Will update after items
                'notes' => $notes,
            ]);

            // Add items
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];
                $itemTotal = $product->price * $quantity;

                RequestItem::create([
                    'stationary_request_id' => $request->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                ]);

                $totalAmount += $itemTotal;
            }

            // Update total amount
            $request->update(['total_amount' => $totalAmount]);

            // Dispatch Event
            event(new \App\Events\RequestStatusChanged($request, $user, 'created'));

            return $request;
        });
    }

    /**
     * Delete a pending stationary request.
     */
    public function deleteRequest(StationaryRequest $request): bool
    {
        if ($request->status !== 'pending') {
            throw new \Exception('Only pending requests can be deleted.');
        }

        return DB::transaction(function () use ($request) {
            $request->requestItems()->delete();
            return $request->delete();
        });
    }
}
