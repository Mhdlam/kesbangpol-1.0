<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['organization', 'submissions.status']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('login_id', 'like', "%{$search}%")
                    ->orWhere('phone_wa', 'like', "%{$search}%")
                    ->orWhere('university', 'like', "%{$search}%")
                    ->orWhereHas('organization', function ($oq) use ($search) {
                        $oq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function toggleStatus(User $user)
    {
        $newStatus = !$user->is_active;
        $user->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLogger::log('toggle_user_status', "Akun user {$user->name} ({$user->login_id}) {$statusText}", 'User', $user->id);

        return redirect()->back()->with('success', "Akun {$user->name} berhasil {$statusText}.");
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:user,admin'],
        ]);

        $user->update(['role' => $request->input('role')]);

        ActivityLogger::log('update_user_role', "Mengubah role {$user->name} menjadi " . strtoupper($user->role), 'User', $user->id);

        return redirect()->back()->with('success', "Role akun {$user->name} berhasil diubah menjadi " . strtoupper($user->role));
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ], [
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
        ]);

        $newPassword = $request->input('new_password');

        $user->update([
            'password' => Hash::make($newPassword),
            'plain_password' => $newPassword,
        ]);

        ActivityLogger::log('reset_user_password', "Mereset password untuk akun {$user->name} ({$user->login_id})", 'User', $user->id);

        return redirect()->back()->with('success', "Password akun {$user->name} berhasil direset menjadi: {$newPassword}");
    }
}
