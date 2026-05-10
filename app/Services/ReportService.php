<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StationaryRequest;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get total spending grouped by department.
     */
    public function getSpendingByDepartment(?int $collegeId = null)
    {
        $query = StationaryRequest::join('departments', 'stationary_requests.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('SUM(stationary_requests.total_amount) as total_spent'))
            ->whereIn('stationary_requests.status', ['sent_to_provider', 'completed']);

        if ($collegeId) {
            $query->where('departments.college_id', $collegeId);
        }

        return $query->groupBy('departments.name')->get();
    }

    /**
     * Get monthly spending for the current year.
     */
    public function getMonthlySpending(?int $collegeId = null)
    {
        $query = StationaryRequest::select(
                DB::raw('EXTRACT(MONTH FROM stationary_requests.created_at) as month'),
                DB::raw('SUM(stationary_requests.total_amount) as total_spent')
            )
            ->whereIn('stationary_requests.status', ['sent_to_provider', 'completed'])
            ->whereYear('stationary_requests.created_at', date('Y'));

        if ($collegeId) {
            $query->join('departments', 'stationary_requests.department_id', '=', 'departments.id')
                  ->where('departments.college_id', $collegeId);
        }

        return $query->groupBy('month')->orderBy('month')->get();
    }
}
