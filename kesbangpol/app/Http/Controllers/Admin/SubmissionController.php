<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Status;
use App\Models\Submission;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::with(['user', 'organization', 'status', 'assignedAdmin']);

        $statuses = \Illuminate\Support\Facades\Cache::remember('statuses_all', 3600, function () {
            return Status::all();
        });

        // Priority Category Filters (Section V)
        $category = $request->input('category', 'all');
        if ($category === 'action_needed') {
            // Submissions that require admin action (submitted or in_process)
            $query->whereHas('status', function ($q) {
                $q->whereIn('code', [Status::SUBMITTED, Status::IN_PROCESS]);
            });
        } elseif ($category === 'waiting_user') {
            // Submissions waiting for user revision
            $query->whereHas('status', function ($q) {
                $q->where('code', Status::REVISION_NEEDED);
            });
        } elseif ($category === 'in_review') {
            // Submissions currently under active review
            $query->whereHas('status', function ($q) {
                $q->where('code', Status::IN_PROCESS);
            });
        } elseif ($category === 'completed') {
            // Finished submissions (approved or rejected)
            $query->whereHas('status', function ($q) {
                $q->whereIn('code', [Status::APPROVED, Status::REJECTED]);
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $statusFilter = $request->input('status');
            $query->whereHas('status', function ($q) use ($statusFilter) {
                $q->where('code', $statusFilter);
            });
        }

        // Date Range Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // District Filter (Kecamatan)
        if ($request->filled('district')) {
            $district = $request->input('district');
            $query->where(function ($q) use ($district) {
                $q->where('district', 'like', "%{$district}%")
                    ->orWhereHas('organization', function ($oq) use ($district) {
                        $oq->where('district', 'like', "%{$district}%");
                    });
            });
        }

        // Admin Examiner Filter (Requirement J)
        if ($request->filled('assigned_admin_id')) {
            if ($request->input('assigned_admin_id') === 'unassigned') {
                $query->whereNull('assigned_admin_id');
            } else {
                $query->where('assigned_admin_id', $request->input('assigned_admin_id'));
            }
        }

        // Unified Search Query (Requirement Z)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('login_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('organization', function ($oq) use ($search) {
                        $oq->where('name', 'like', "%{$search}%")
                            ->orWhere('abbreviation', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%")
                            ->orWhere('leader_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting Options (Requirements A & B)
        $sort = $request->input('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->orderBy('created_at', 'asc');
                break;
            case 'belum_diperiksa':
                $submittedId = $statuses->firstWhere('code', Status::SUBMITTED)?->id ?? 0;
                $query->orderByRaw("FIELD(status_id, {$submittedId}) DESC")
                    ->orderBy('created_at', 'desc');
                break;
            case 'terakhir_diperbarui':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'terbaru':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $submissions = $query->paginate(15)->withQueryString();
        $admins = User::where('role', 'admin')->get();
        $districts = \Illuminate\Support\Facades\Cache::remember('org_districts', 600, function () {
            return Organization::whereNotNull('district')->where('district', '!=', '')->distinct()->pluck('district');
        });

        return view('admin.submissions.index', compact('submissions', 'statuses', 'admins', 'districts', 'category', 'sort'));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'string', 'in:assign_admin,export_csv'],
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:submissions,id'],
            'admin_id' => ['nullable', 'exists:users,id'],
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        if ($action === 'assign_admin') {
            $adminId = $request->input('admin_id');
            Submission::whereIn('id', $ids)->update([
                'assigned_admin_id' => $adminId,
                'assigned_at' => now(),
            ]);

            ActivityLogger::log('bulk_assign', "Mengalihkan " . count($ids) . " pengajuan ke Admin ID: " . $adminId, 'Submission');

            return redirect()->back()->with('success', count($ids) . ' pengajuan berhasil ditugaskan ke Admin.');
        }

        if ($action === 'export_csv') {
            $submissions = Submission::with(['user', 'organization', 'status', 'assignedAdmin'])
                ->whereIn('id', $ids)
                ->get();

            $csvData = [];
            $csvData[] = ['No. Pengajuan', 'Nama Ormas', 'Pemohon', 'Email', 'Tanggal Pengajuan', 'Status', 'Pemeriksa'];

            foreach ($submissions as $s) {
                $csvData[] = [
                    $s->tracking_code,
                    $s->organization->name ?? '-',
                    $s->user->name ?? '-',
                    $s->user->email ?? '-',
                    $s->created_at->format('d/m/Y H:i'),
                    $s->status->name ?? '-',
                    $s->assignedAdmin->name ?? 'Belum Ditugaskan',
                ];
            }

            $output = fopen('php://temp', 'r+');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            rewind($output);
            $csvString = stream_get_contents($output);
            fclose($output);

            return Response::make($csvString, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="Laporan_Berkas_Masuk_' . date('Ymd_His') . '.csv"',
            ]);
        }

        return redirect()->back();
    }
}
