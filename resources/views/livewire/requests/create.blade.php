<?php

use App\Models\Product;
use App\Services\StationaryRequestService;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public $items = [];
    public $notes = '';
    
    public function mount()
    {
        $this->addItem();
    }

    public function with(): array
    {
        return [
            'products' => Product::where('status', 'active')->where('stock', '>', 0)->get(),
        ];
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'quantity' => 1];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(StationaryRequestService $service)
    {
        $this->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $request = $service->createRequest($this->items, Auth::user(), $this->notes);

        session()->flash('status', 'Stationary request created successfully.');
        return redirect()->route('requests.show', $request);
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Create New Request</h1>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form wire:submit="save">
                    <div class="space-y-4">
                        @foreach($items as $index => $item)
                        <div class="flex items-end space-x-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Product</label>
                                <select wire:model="items.{{ $index }}.product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select a Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} (In Stock: {{ $product->stock }})</option>
                                    @endforeach
                                </select>
                                @error("items.{$index}.product_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="w-32">
                                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input type="number" wire:model="items.{{ $index }}.quantity" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error("items.{$index}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            @if(count($items) > 1)
                            <div>
                                <button type="button" wire:click="removeItem({{ $index }})" class="bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600">Remove</button>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <button type="button" wire:click="addItem" class="text-indigo-600 font-medium hover:text-indigo-900">+ Add Another Item</button>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea wire:model="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 flex items-center">
                            <span wire:loading wire:target="save" class="mr-2">...</span>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
