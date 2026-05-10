<?php

namespace App\Policies;

use App\Models\StationaryRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StationaryRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->status === 'active';
    }

    public function view(User $user, StationaryRequest $stationaryRequest): bool
    {
        if ($user->hasRole(['admin', 'trust_head', 'provider'])) {
            return true;
        }

        if ($user->hasRole(['principal', 'hod'])) {
            return $user->department->college_id === $stationaryRequest->department->college_id;
        }

        return $user->id === $stationaryRequest->requested_by;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['teacher', 'hod']);
    }

    public function update(User $user, StationaryRequest $stationaryRequest): bool
    {
        return $user->id === $stationaryRequest->requested_by && $stationaryRequest->status === 'pending';
    }

    public function delete(User $user, StationaryRequest $stationaryRequest): bool
    {
        return $user->id === $stationaryRequest->requested_by && $stationaryRequest->status === 'pending';
    }
}
