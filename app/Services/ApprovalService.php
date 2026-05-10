<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\StationaryRequest;
use App\Models\User;
use App\Events\RequestStatusChanged;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    /**
     * Approve a stationary request based on the user's role.
     */
    public function approve(StationaryRequest $request, User $user, ?string $comments = null): StationaryRequest
    {
        return DB::transaction(function () use ($request, $user, $comments) {
            $role = $user->roles->first()->name;

            Approval::create([
                'stationary_request_id' => $request->id,
                'user_id' => $user->id,
                'role' => $role,
                'status' => 'approved',
                'comments' => $comments,
            ]);

            $newStatus = match ($role) {
                'hod' => 'hod_approved',
                'principal' => 'principal_approved',
                'trust_head' => 'trust_approved',
                'admin' => 'sent_to_provider',
                'provider' => 'completed',
                default => $request->status,
            };

            $request->update(['status' => $newStatus]);

            // Dispatch Event
            event(new RequestStatusChanged($request, $user, 'approved'));

            return $request;
        });
    }

    /**
     * Reject a stationary request.
     */
    public function reject(StationaryRequest $request, User $user, string $comments): StationaryRequest
    {
        return DB::transaction(function () use ($request, $user, $comments) {
            $role = $user->roles->first()->name;

            Approval::create([
                'stationary_request_id' => $request->id,
                'user_id' => $user->id,
                'role' => $role,
                'status' => 'rejected',
                'comments' => $comments,
            ]);

            $request->update(['status' => 'rejected']);

            // Dispatch Event
            event(new RequestStatusChanged($request, $user, 'rejected'));

            return $request;
        });
    }
}
