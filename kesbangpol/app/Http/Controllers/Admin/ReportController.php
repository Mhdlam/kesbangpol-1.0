<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Status;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'this_month');
        $startDate = null;
        $endDate = Carbon::now()->endOfDay();

        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'custom':
                if ($request->filled('date_from')) {
                    $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
                }
                if ($request->filled('date_to')) {
                    $endDate = Carbon::parse($request->input('date_to'))->endOfDay();
                }
                break;
            case 'this_month':
            default:
                $startDate = Carbon::now()->startOfMonth();
                break;
        }

        $submissionQuery = Submission::query();
        if ($startDate) {
            $submissionQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Summary Counts (Cached Status Mapping)
        $statuses = \Illuminate\Support\Facades\Cache::remember('statuses_by_code', 3600, function () {
            return Status::all()->keyBy('code');
        });

        $approvedId = $statuses[Status::APPROVED]->id ?? 0;
        $rejectedId = $statuses[Status::REJECTED]->id ?? 0;
        $revisionId = $statuses[Status::REVISION_NEEDED]->id ?? 0;
        $submittedId = $statuses[Status::SUBMITTED]->id ?? 0;
        $inProcessId = $statuses[Status::IN_PROCESS]->id ?? 0;

        $reportStats = (clone $submissionQuery)->selectRaw("
            COUNT(*) as total_submissions,
            SUM(CASE WHEN status_id = {$approvedId} THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status_id = {$rejectedId} THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status_id = {$revisionId} THEN 1 ELSE 0 END) as revision_needed,
            SUM(CASE WHEN status_id IN ({$submittedId}, {$inProcessId}) THEN 1 ELSE 0 END) as pending_review
        ")->first();

        $stats = [
            'total_orgs' => Organization::count(),
            'total_submissions' => (int) ($reportStats->total_submissions ?? 0),
            'approved' => (int) ($reportStats->approved ?? 0),
            'rejected' => (int) ($reportStats->rejected ?? 0),
            'revision_needed' => (int) ($reportStats->revision_needed ?? 0),
            'pending_review' => (int) ($reportStats->pending_review ?? 0),
        ];

        // District Breakdown
        $districtStats = Organization::select('district', DB::raw('count(*) as count'))
            ->groupBy('district')
            ->orderBy('count', 'desc')
            ->get();

        // Duration Monitoring Stats (Direct SQL calculation avoids memory overhead)
        $avgSeconds = Submission::whereNotNull('completed_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, completed_at)) as avg_sec')
            ->value('avg_sec');

        $avgProcessingHours = $avgSeconds ? round($avgSeconds / 3600, 1) : 0;

        return view('admin.reports.index', compact('stats', 'period', 'districtStats', 'avgProcessingHours', 'startDate', 'endDate'));
    }
}
