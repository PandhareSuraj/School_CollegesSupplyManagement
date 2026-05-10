<?php

use App\Models\Order;
use App\Services\OrderService;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public Order $order;
    public $statusOptions = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    public function mount(Order $order)
    {
        $this->order = $order->load(['orderItems.product', 'vendor', 'stationaryRequest']);
    }

    public function updateStatus($newStatus, \App\Services\OrderService $orderService)
    {
        if (!in_array($newStatus, $this->statusOptions)) return;

        // Authorize (Providers or Admins can update orders)
        if (!Auth::user()->hasRole(['provider', 'admin'])) {
            abort(403);
        }

        $orderService->updateOrderStatus($this->order, $newStatus);
        
        session()->flash('status', 'Order status updated successfully.');
        $this->order->refresh();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->id }} Details</h1>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto space-y-6">
            
            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Order Fulfillment Status</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Current Status:</span>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>

                @if(Auth::user()->hasRole(['provider', 'admin']) && $order->status !== 'delivered' && $order->status !== 'cancelled')
                <div class="flex space-x-2 border-t pt-4">
                    @foreach(['processing', 'shipped', 'delivered'] as $statusOption)
                        @if($order->status !== $statusOption)
                        <button wire:click="updateStatus('{{ $statusOption }}')" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50">
                            Mark as {{ ucfirst($statusOption) }}
                        </button>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>

            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Order Information</h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Vendor</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $order->vendor->name }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Original Request</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <a href="{{ route('requests.show', $order->stationaryRequest) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Request #{{ $order->stationary_request_id }}
                                </a>
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">${{ number_format($order->total_amount, 2) }}</dd>
                        </div>
                    </dl>
                </div>
                
                <div class="p-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Items to Fulfill</h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->orderItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
