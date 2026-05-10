<?php

namespace App\Listeners;

use App\Events\RequestStatusChanged;
use App\Notifications\RequestRequiresApprovalNotification;
use App\Notifications\RequestStatusUpdatedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRequestNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RequestStatusChanged $event): void
    {
        $request = $event->request;
        $action = $event->action;

        // Notify the requester about the status change (if it wasn't just created by them)
        if ($action !== 'created') {
            $request->requestedBy->notify(new RequestStatusUpdatedNotification($request, $action));
        }

        // Determine who needs to approve next
        if ($request->status === 'pending') {
            // Notify HODs in the department
            $hods = User::role('hod')->where('department_id', $request->department_id)->get();
            foreach ($hods as $hod) {
                $hod->notify(new RequestRequiresApprovalNotification($request));
            }
        } elseif ($request->status === 'hod_approved') {
            // Notify Principals in the college
            $collegeId = $request->department->college_id;
            $principals = User::role('principal')->whereHas('department', function ($q) use ($collegeId) {
                $q->where('college_id', $collegeId);
            })->get();
            foreach ($principals as $principal) {
                $principal->notify(new RequestRequiresApprovalNotification($request));
            }
        } elseif ($request->status === 'principal_approved') {
            // Notify Trust Heads
            $trustHeads = User::role('trust_head')->get();
            foreach ($trustHeads as $head) {
                $head->notify(new RequestRequiresApprovalNotification($request));
            }
        } elseif ($request->status === 'trust_approved') {
            // Notify Admins
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new RequestRequiresApprovalNotification($request));
            }
        } elseif ($request->status === 'sent_to_provider') {
            // Notify Providers
            $providers = User::role('provider')->get();
            foreach ($providers as $provider) {
                $provider->notify(new RequestRequiresApprovalNotification($request));
            }
        }
    }
}
