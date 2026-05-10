<?php

use App\Models\StationaryRequest;
use App\Models\Approval;
use App\Models\Vendor;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.app')] class extends Component {
    public StationaryRequest $request;
    public $comments = '';
    public $vendor_id = '';
    public $vendors = [];

    public function mount(StationaryRequest $request)
    {
        $this->request = $request->load(['requestItems.product', 'approvals.user', 'requestedBy', 'department']);
        $this->authorize('view', $this->request);

        if (Auth::user()->hasRole('admin') && $this->request->status === 'trust_approved') {
            $this->vendors = Vendor::where('status', 'active')->get();
        }
    }

    public function approve(\App\Services\ApprovalService $service, \App\Services\OrderService $orderService)
    {
        $this->authorize('create', [Approval::class, $this->request]);
        
        $rules = ['comments' => 'nullable|string'];
        if (Auth::user()->hasRole('admin') && $this->request->status === 'trust_approved') {
            $rules['vendor_id'] = 'required|exists:vendors,id';
        }
        $this->validate($rules);

        DB::transaction(function() use ($service, $orderService) {
            $service->approve($this->request, Auth::user(), $this->comments);

            if (Auth::user()->hasRole('admin') && $this->vendor_id) {
                $vendor = Vendor::find($this->vendor_id);
                $orderService->createOrderFromRequest($this->request, $vendor);
            }
        });

        session()->flash('status', 'Request approved successfully.');
        $this->request->refresh();
    }

    public function reject(\App\Services\ApprovalService $service)
    {
        $this->authorize('create', [Approval::class, $this->request]);
        $this->validate(['comments' => 'required|string']);

        $service->reject($this->request, Auth::user(), $this->comments);

        session()->flash('status', 'Request rejected.');
        $this->request->refresh();
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Request #{{ $request->id }} Details</h1>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto space-y-6">
            
            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Request Information</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Details and items.</p>
                    </div>
                    <div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ str_replace('_', ' ', Str::title($request->status)) }}
                        </span>
                    </div>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Requested By</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $request->requestedBy->name }} ({{ $request->department->name }})</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">${{ number_format($request->total_amount, 2) }}</dd>
                        </div>
                        @if($request->notes)
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $request->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                
                <div class="p-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Items</h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($request->requestItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Approvals Timeline -->
            @if($request->approvals->count() > 0)
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Approval History</h4>
                <ul class="space-y-4">
                    @foreach($request->approvals as $approval)
                    <li class="bg-gray-50 p-4 rounded-md">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-900">{{ $approval->user->name }} ({{ Str::title($approval->role) }})</span>
                            <span class="text-sm text-gray-500">{{ $approval->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-700">
                            Action: <strong class="{{ $approval->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($approval->status) }}</strong>
                        </p>
                        @if($approval->comments)
                        <p class="mt-2 text-sm text-gray-600">"{{ $approval->comments }}"</p>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Action Area -->
            @can('create', [\App\Models\Approval::class, $request])
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Take Action</h4>
                <div class="space-y-4">
                    @if(Auth::user()->hasRole('admin') && $request->status === 'trust_approved')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Assign Vendor</label>
                        <select wire:model="vendor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a Vendor</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                        @error('vendor_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Comments (Required for Rejection)</label>
                        <textarea wire:model="comments" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        @error('comments') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex space-x-4">
                        <button wire:click="approve" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Approve Request</button>
                        <button wire:click="reject" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Reject Request</button>
                    </div>
                </div>
            </div>
            @endcan

        </div>
    </div>
</div>
