<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Status;
use App\Models\Submission;
use App\Models\SubmissionDocumentReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Cached Statuses
        $statuses = \Illuminate\Support\Facades\Cache::remember('statuses_by_code', 3600, function () {
            return Status::all()->keyBy('code');
        });

        $submittedId = $statuses[Status::SUBMITTED]->id ?? 0;
        $inProcessId = $statuses[Status::IN_PROCESS]->id ?? 0;
        $revisionId = $statuses[Status::REVISION_NEEDED]->id ?? 0;
        $approvedId = $statuses[Status::APPROVED]->id ?? 0;
        $rejectedId = $statuses[Status::REJECTED]->id ?? 0;

        // 1. Single Aggregated Query for Submissions Metrics
        $subStats = Submission::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status_id = {$submittedId} THEN 1 ELSE 0 END) as unreviewed,
            SUM(CASE WHEN status_id = {$inProcessId} THEN 1 ELSE 0 END) as in_process,
            SUM(CASE WHEN status_id = {$revisionId} THEN 1 ELSE 0 END) as revision_needed,
            SUM(CASE WHEN status_id = {$approvedId} THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status_id = {$rejectedId} THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as submitted_today,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as submitted_this_week,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as submitted_this_month
        ", [$today, $startOfWeek, $startOfMonth])->first();

        $counts = [
            'total_users' => User::where('role', 'user')->count(),
            'total_orgs' => Organization::count(),
            'total_submissions' => (int) ($subStats->total ?? 0),
            'unreviewed' => (int) ($subStats->unreviewed ?? 0),
            'in_process' => (int) ($subStats->in_process ?? 0),
            'revision_needed' => (int) ($subStats->revision_needed ?? 0),
            'approved' => (int) ($subStats->approved ?? 0),
            'rejected' => (int) ($subStats->rejected ?? 0),
            'submitted_today' => (int) ($subStats->submitted_today ?? 0),
            'submitted_this_week' => (int) ($subStats->submitted_this_week ?? 0),
            'submitted_this_month' => (int) ($subStats->submitted_this_month ?? 0),
        ];

        // Quick Action Badge Counts (Reusing Aggregated Data)
        $quickActionCounts = [
            'berkas_masuk' => $counts['total_submissions'],
            'mulai_pemeriksaan' => $counts['unreviewed'] + $counts['in_process'],
            'lihat_revisi' => $counts['revision_needed'],
        ];

        // 2. "Pekerjaan Saya" (My Tasks) Aggregated Query
        $adminId = Auth::id();
        $myTasksSub = Submission::where(function ($q) use ($adminId) {
                $q->whereNull('assigned_admin_id')->orWhere('assigned_admin_id', $adminId);
            })
            ->selectRaw("
                SUM(CASE WHEN status_id IN ({$submittedId}, {$inProcessId}) THEN 1 ELSE 0 END) as awaiting_review,
                SUM(CASE WHEN revision_count > 0 AND status_id IN ({$revisionId}, {$inProcessId}) THEN 1 ELSE 0 END) as revisions_to_review,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_submissions_today
            ", [$today])->first();

        $docsNeedReverification = SubmissionDocumentReview::whereIn('status', ['pending', 'revision_needed'])
            ->whereHas('submission', function ($q) use ($adminId) {
                $q->whereNull('assigned_admin_id')->orWhere('assigned_admin_id', $adminId);
            })->count();

        $myTasks = [
            'awaiting_review' => (int) ($myTasksSub->awaiting_review ?? 0),
            'revisions_to_review' => (int) ($myTasksSub->revisions_to_review ?? 0),
            'docs_need_reverification' => $docsNeedReverification,
            'new_submissions_today' => (int) ($myTasksSub->new_submissions_today ?? 0),
        ];

        // 3. Priority Submissions (Direct status_id filter avoids subquery JOIN)
        $prioritySubmissions = Submission::with(['user', 'organization', 'status', 'assignedAdmin'])
            ->whereIn('status_id', [$submittedId, $inProcessId])
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        // 4. District Breakdown
        $districtStats = Organization::selectRaw('district, count(*) as count')
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->groupBy('district')
            ->orderBy('count', 'desc')
            ->get();

        return view('admin.dashboard', compact('counts', 'myTasks', 'prioritySubmissions', 'quickActionCounts', 'districtStats'));
    }
}
