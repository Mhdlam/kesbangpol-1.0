<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminInternalNote;
use App\Models\Organization;
use App\Services\ActivityLogger;
use App\Services\DuplicateDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::with(['user', 'submissions.status']);

        // Status Archive Filter (Section X)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            // Default to non-archived unless specified
            $query->where('status', '!=', 'archived');
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('abbreviation', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('leader_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $organizations = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $rawCounts = Organization::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');
        $counts = [
            'total' => $rawCounts->sum(),
            'active' => $rawCounts['active'] ?? 0,
            'inactive' => $rawCounts['inactive'] ?? 0,
            'archived' => $rawCounts['archived'] ?? 0,
        ];

        return view('admin.organizations.index', compact('organizations', 'counts'));
    }

    public function show(Organization $organization, Request $request)
    {
        $organization->load([
            'user',
            'submissions.status',
            'submissions.documents',
            'submissions.histories.changedBy',
            'submissions.histories.status',
            'internalNotes.admin'
        ]);

        $activeTab = $request->input('tab', 'profil');

        // Check for duplicates (Requirement L)
        $duplicateWarnings = DuplicateDetector::detectDuplicates($organization);

        return view('admin.organizations.show', compact('organization', 'activeTab', 'duplicateWarnings'));
    }

    public function updateStatus(Request $request, Organization $organization)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,archived'],
        ]);

        $newStatus = $request->input('status');
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'archived') {
            $updateData['archived_at'] = now();
        } else {
            $updateData['archived_at'] = null;
        }

        $organization->update($updateData);

        ActivityLogger::log('update_org_status', "Mengubah status organisasi {$organization->name} menjadi: " . strtoupper($newStatus), 'Organization', $organization->id);

        return redirect()->back()->with('success', "Status organisasi {$organization->name} berhasil diperbarui menjadi " . strtoupper($newStatus));
    }

    public function storeInternalNote(Request $request, Organization $organization)
    {
        $request->validate([
            'note' => ['required', 'string', 'min:3'],
        ]);

        AdminInternalNote::create([
            'target_type' => 'organization',
            'target_id' => $organization->id,
            'admin_id' => Auth::id(),
            'note' => $request->input('note'),
        ]);

        ActivityLogger::log('internal_note', "Menambahkan catatan internal pada organisasi {$organization->name}", 'Organization', $organization->id);

        return redirect()->back()->with('success', 'Catatan internal organisasi berhasil disimpan.');
    }
}
