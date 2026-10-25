@extends('layouts.app')

@section('title', 'Data User & Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-users text-gov-600"></i> Data Akun Pengguna & Akses Role
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Kelola hak akses role (User/Admin), ID Login (6 Digit), dan informasi password pengguna.
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-gov-600 dark:text-slate-300 hover:underline">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-700 dark:text-slate-300 uppercase tracking-wider font-extrabold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">ID Login (6 Digit)</th>
                        <th class="px-6 py-4">Nama Lengkap & Email</th>
                        <th class="px-6 py-4">Kontak WhatsApp</th>
                        <th class="px-6 py-4">Organisasi / Instansi</th>
                        <th class="px-6 py-4">Password Aktif</th>
                        <th class="px-6 py-4">Role Saat Ini</th>
                        <th class="px-6 py-4 text-center">Ubah Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-6 py-4 font-mono font-extrabold text-gov-600 dark:text-slate-200 text-sm">
                                {{ $u->login_id }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 dark:text-white block">{{ $u->name }}</span>
                                <span class="text-[11px] text-slate-500">{{ $u->email }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($u->phone_wa)
                                    @php
                                        $waNum = preg_replace('/[^0-9]/', '', $u->phone_wa);
                                        if (str_starts_with($waNum, '0')) {
                                            $waNum = '62' . substr($waNum, 1);
                                        }
                                    @endphp
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $u->phone_wa }}</span>
                                        <a href="https://wa.me/{{ $waNum }}?text=Halo%20{{ urlencode($u->name) }}%2C%20kami%20dari%20Kesbangpol%20Kota%20Palembang" target="_blank" class="px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] rounded transition-colors inline-flex items-center gap-1">
                                            <i class="fa-brands fa-whatsapp text-xs"></i>
                                            <span>WA</span>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-slate-400 font-medium">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $u->organization->name ?? ($u->position ?: '-') }}</span>
                                <span class="text-[11px] text-slate-500">{{ $u->university ?: '-' }}</span>
                            </td>
                            <!-- Password Column with Toggle Eye -->
                            <td class="px-6 py-4 font-mono" x-data="{ showPass: false }">
                                <div class="flex items-center gap-2">
                                    <span x-text="showPass ? '{{ $u->plain_password ?: '********' }}' : '••••••••'" class="font-bold text-slate-800 dark:text-slate-200"></span>
                                    <button type="button" @click="showPass = !showPass" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1" title="Lihat/Sembunyikan Password">
                                        <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($u->role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 text-[10px] font-extrabold uppercase border border-sky-300">
                                        ADMIN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 text-[10px] font-extrabold uppercase border border-slate-300">
                                        USER / ORMAS
                                    </span>
                                @endif
                            </td>
                            <!-- Role Update Form -->
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('admin.users.update_role', $u->id) }}" method="POST" class="flex items-center justify-center gap-2">
                                    @csrf
                                    <select name="role" class="px-2.5 py-1 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-800 dark:text-slate-200">
                                        <option value="user" {{ $u->role === 'user' ? 'selected' : '' }}>User</option>
                                        <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1 bg-gov-600 hover:bg-gov-700 text-white rounded text-xs font-bold transition-colors">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
