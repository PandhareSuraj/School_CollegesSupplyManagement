<?php

use App\Services\ReportService;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public $departmentSpending = [];
    public $monthlySpending = [];

    public function mount(ReportService $reportService)
    {
        $user = Auth::user();
        
        // Authorization: Only admin, trust_head, and principal
        if (!$user->hasRole(['admin', 'trust_head', 'principal'])) {
            abort(403, 'Unauthorized access to analytics.');
        }

        $collegeId = $user->hasRole('principal') ? $user->department->college_id : null;

        $this->departmentSpending = $reportService->getSpendingByDepartment($collegeId);
        $this->monthlySpending = $reportService->getMonthlySpending($collegeId);
    }

    public function exportCsv(ReportService $reportService)
    {
        $user = Auth::user();
        $collegeId = $user->hasRole('principal') ? $user->department->college_id : null;
        $data = $reportService->getSpendingByDepartment($collegeId);

        $csvData = "Department,Total Spent\n";
        foreach ($data as $row) {
            $csvData .= "{$row->name},{$row->total_spent}\n";
        }

        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, 'spending_report.csv');
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="flex justify-end">
                <button wire:click="exportCsv" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Export to CSV
                </button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Department Spending -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Spending by Department</h3>
                    </div>
                    <div class="p-6">
                        @if(count($departmentSpending) > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="text-right text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($departmentSpending as $stat)
                                <tr>
                                    <td class="py-3 text-sm text-gray-900">{{ $stat->name }}</td>
                                    <td class="py-3 text-sm text-gray-900 text-right">${{ number_format($stat->total_spent, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="text-gray-500 text-sm">No spending data available yet.</p>
                        @endif
                    </div>
                </div>

                <!-- Monthly Spending -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Monthly Spending ({{ date('Y') }})</h3>
                    </div>
                    <div class="p-6">
                        @if(count($monthlySpending) > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                                    <th class="text-right text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($monthlySpending as $stat)
                                <tr>
                                    <td class="py-3 text-sm text-gray-900">{{ date("F", mktime(0, 0, 0, $stat->month, 10)) }}</td>
                                    <td class="py-3 text-sm text-gray-900 text-right">${{ number_format($stat->total_spent, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="text-gray-500 text-sm">No monthly data available yet.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
