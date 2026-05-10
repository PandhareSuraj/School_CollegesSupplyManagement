<?php

use App\Models\StationaryRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public $role;
    public $pendingCount = 0;
    public $totalCount = 0;
    public $recentRequests = [];
    public $actionableRequests = [];

    public function mount()
    {
        $user = Auth::user();
        $this->role = $user->roles->first()->name ?? 'teacher';

        // Base query for their own requests
        $myRequests = StationaryRequest::where('requested_by', $user->id);
        $this->totalCount = $myRequests->count();
        $this->recentRequests = $myRequests->latest()->take(5)->get();

        // Compute metrics and actionable items based on role
        if ($this->role === 'hod') {
            $departmentRequests = StationaryRequest::where('department_id', $user->department_id)
                ->where('status', 'pending');
            $this->pendingCount = $departmentRequests->count();
            $this->actionableRequests = $departmentRequests->latest()->take(5)->get();
        } elseif ($this->role === 'principal') {
            $collegeRequests = StationaryRequest::whereHas('department', function ($q) use ($user) {
                $q->where('college_id', $user->department->college_id);
            })->where('status', 'hod_approved');
            $this->pendingCount = $collegeRequests->count();
            $this->actionableRequests = $collegeRequests->latest()->take(5)->get();
        } elseif ($this->role === 'trust_head') {
            $trustRequests = StationaryRequest::where('status', 'principal_approved');
            $this->pendingCount = $trustRequests->count();
            $this->actionableRequests = $trustRequests->latest()->take(5)->get();
        } elseif ($this->role === 'admin') {
            $adminRequests = StationaryRequest::where('status', 'trust_approved');
            $this->pendingCount = $adminRequests->count();
            $this->actionableRequests = $adminRequests->latest()->take(5)->get();
        } elseif ($this->role === 'provider') {
            $providerRequests = StationaryRequest::where('status', 'sent_to_provider');
            $this->pendingCount = $providerRequests->count();
            $this->actionableRequests = $providerRequests->latest()->take(5)->get();
        }
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}</h1>
        <p class="text-gray-500">You are logged in as <span class="font-semibold capitalize">{{ str_replace('_', ' ', $role) }}</span></p>
    </div>

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        @if($role !== 'teacher')
        <div class="bg-white p-6 rounded-xl border border-neutral-200 shadow-sm flex flex-col justify-center">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Action Required</h3>
            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $pendingCount }}</p>
            <p class="text-xs text-gray-400 mt-1">Pending approvals</p>
        </div>
        @endif
        
        <div class="bg-white p-6 rounded-xl border border-neutral-200 shadow-sm flex flex-col justify-center">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">My Requests</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalCount }}</p>
            <p class="text-xs text-gray-400 mt-1">Total requests made</p>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2 flex-1">
        <!-- My Recent Requests -->
        <div class="bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">My Recent Requests</h3>
                <a href="{{ route('requests.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse($recentRequests as $req)
                <li class="px-6 py-4 flex justify-between items-center hover:bg-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Request #{{ $req->id }}</p>
                        <p class="text-xs text-gray-500">{{ $req->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ str_replace('_', ' ', Str::title($req->status)) }}
                        </span>
                        <a href="{{ route('requests.show', $req) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                    </div>
                </li>
                @empty
                <li class="px-6 py-4 text-sm text-gray-500 text-center">No recent requests.</li>
                @endforelse
            </ul>
        </div>

        <!-- Actionable Requests (for Approvers) -->
        @if($role !== 'teacher')
        <div class="bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Requires Your Attention</h3>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse($actionableRequests as $req)
                <li class="px-6 py-4 flex justify-between items-center hover:bg-gray-50 border-l-4 border-indigo-500">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Request #{{ $req->id }} by {{ $req->requestedBy->name }}</p>
                        <p class="text-xs text-gray-500">{{ $req->department->name }} - ${{ number_format($req->total_amount, 2) }}</p>
                    </div>
                    <a href="{{ route('requests.show', $req) }}" class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded text-sm hover:bg-indigo-100 font-medium">Review</a>
                </li>
                @empty
                <li class="px-6 py-4 text-sm text-gray-500 text-center">You're all caught up!</li>
                @endforelse
            </ul>
        </div>
        @endif
    </div>
</div>
