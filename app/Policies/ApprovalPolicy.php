<?php

namespace App\Policies;

use App\Models\Approval;
use App\Models\StationaryRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApprovalPolicy
{
    use HandlesAuthorization;

    public function create(User $user, StationaryRequest $stationaryRequest): bool
    {
        if ($user->id === $stationaryRequest->requested_by) {
            return false; // Prevent self-approval
        }

        if ($user->hasRole('hod') && $stationaryRequest->status === 'pending') {
            return $user->department_id === $stationaryRequest->department_id;
        }

        if ($user->hasRole('principal') && $stationaryRequest->status === 'hod_approved') {
            return $user->department->college_id === $stationaryRequest->department->college_id;
        }

        if ($user->hasRole('trust_head') && $stationaryRequest->status === 'principal_approved') {
            return true;
        }

        if ($user->hasRole('admin') && $stationaryRequest->status === 'trust_approved') {
            return true;
        }

        return false;
    }
}
