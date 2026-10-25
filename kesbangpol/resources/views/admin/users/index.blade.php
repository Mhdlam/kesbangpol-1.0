@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div x-data="{ showResetModal: false, selectedUser: null }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-users text-gov-600 dark:text-gov-500"></i> Manajemen Akun User/Ormas
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Kelola status akun, reset password baru, dan hak akses pengguna sistem Kesbangpol.
            </p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.users') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari Nama User / Login ID (6 digit) / Email / No. WhatsApp / Ormas..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
            </div>
            <div class="flex gap-2">
                <select name="role" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                    <option value="">Semua Role</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User/Ormas</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs shadow-sm transition-colors whitespace-nowrap">
                    Cari User
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table (Requirement M) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 font-bold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nama & Login ID & Password</th>
                        <th class="px-4 py-3.5">Organisasi</th>
                        <th class="px-4 py-3.5">Kontak</th>
                        <th class="px-4 py-3.5">Role</th>
                        <th class="px-4 py-3.5">Status Akun</th>
                        <th class="px-4 py-3.5">Aktivitas Terakhir</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4" x-data="{ showPass: false }">
                                <span class="font-extrabold text-slate-900 dark:text-white block text-sm">{{ $user->name }}</span>
                                <span class="font-mono text-slate-500 font-bold block text-[11px]">ID: {{ $user->login_id }} | {{ $user->email }}</span>
                                <div class="flex items-center gap-1.5 mt-1 font-mono text-[11px]">
                                    <span class="text-slate-400 font-semibold">Password:</span>
                                    <span x-text="showPass ? '{{ $user->plain_password ?: '********' }}' : '••••••••'" class="font-bold text-slate-800 dark:text-slate-200"></span>
                                    <button type="button" @click="showPass = !showPass" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 ms-0.5 focus:outline-none" title="Lihat / Sembunyikan Password">
                                        <i class="fa-solid text-xs" :class="showPass ? 'fa-eye-slash text-amber-500' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $user->organization->name ?? ($user->university ?: '-') }}
                            </td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-400">
                                {{ $user->phone_wa ?? '-' }}
                            </td>
                            <td class="px-4 py-4">
                                <form method="POST" action="{{ route('admin.users.update_role', $user->id) }}">
                                    @csrf
                                    <select name="role" onchange="this.form.submit()" class="px-2 py-1 rounded-lg text-[11px] font-extrabold border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>USER/ORMAS</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>ADMIN</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-4">
                                <form method="POST" action="{{ route('admin.users.toggle_status', $user->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-full text-[11px] font-extrabold transition-colors {{ $user->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-4 text-slate-500">
                                {{ $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'Belum tercatat' }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button @click="selectedUser = { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', login_id: '{{ $user->login_id }}' }; showResetModal = true"
                                        type="button" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold text-xs transition-colors shadow-sm inline-flex items-center gap-1">
                                    <i class="fa-solid fa-key"></i> Reset Password
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <i class="fa-solid fa-users text-4xl text-slate-300 dark:text-slate-700 mb-2 block"></i>
                                Tidak ada data user ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Reset Password Modal (Requirement M) -->
    <div x-show="showResetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showResetModal = false" class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950 text-base font-bold flex items-center justify-center">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white">Reset Password Baru</h3>
                    <p class="text-xs text-slate-500" x-text="selectedUser ? selectedUser.name + ' (ID: ' + selectedUser.login_id + ')' : ''"></p>
                </div>
            </div>

            <form x-bind:action="selectedUser ? '/admin/users/' + selectedUser.id + '/reset-password' : '#'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Masukkan Password Baru (Minimal 6 karakter):
                    </label>
                    <input type="text" name="new_password" required minlength="6" placeholder="Contoh: password123..."
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button @click="showResetModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold text-xs shadow-md">Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
