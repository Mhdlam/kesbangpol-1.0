<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Services\RequirementChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Fetch current submission if exists
        $activeSubmission = Submission::with(['status', 'histories.status', 'documentReviews'])
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $status = RequirementChecker::checkUserStatus($user, $activeSubmission);

        $isRevisionMode = false;
        $latestRevisionHistory = null;

        if ($activeSubmission && $activeSubmission->status && $activeSubmission->status->code === \App\Models\Status::REVISION_NEEDED) {
            $isRevisionMode = true;
            $revStatus = \App\Models\Status::where('code', \App\Models\Status::REVISION_NEEDED)->first();
            $latestRevisionHistory = $activeSubmission->histories
                ->where('status_id', $revStatus?->id)
                ->first();
        }

        return view('user.dashboard', array_merge($status, [
            'user' => $user,
            'active_submission' => $activeSubmission,
            'is_revision_mode' => $isRevisionMode,
            'latest_revision_history' => $latestRevisionHistory,
        ]));
    }

    public function updateReligion(Request $request)
    {
        $request->validate([
            'is_religious' => ['required', 'boolean'],
        ]);

        $user = Auth::user();
        $org = $user->organization;

        if ($org) {
            $org->update(['is_religious' => $request->boolean('is_religious')]);
        }

        return redirect()->route('user.dashboard')->with('success', 'Status bidang keagamaan berhasil diperbarui.');
    }
}
