@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <main class="main dashboard">
        <header class="dashboard-head">
            <div>
                <div class="eyebrow">{{ auth()->user()->isAdmin() ? 'Panel administrasi' : 'Area organisasi' }}</div>
                <h1>Selamat datang, {{ auth()->user()->name }}</h1>
                <p>{{ auth()->user()->isAdmin() ? 'Anda masuk sebagai administrator.' : 'Anda masuk ke Sistem Informasi Kesbangpol.' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="button secondary" type="submit">Keluar</button></form>
        </header>
        @if (session('success'))<div class="notice">{{ session('success') }}</div>@endif
        <section class="welcome-panel">
            <h2>{{ auth()->user()->isAdmin() ? 'Ringkasan administrasi' : 'Profil organisasi' }}</h2>
            @if (auth()->user()->isAdmin())
                <p>Panel pengelolaan pengajuan dan organisasi telah siap. Data pengajuan akan muncul setelah modul administrasi diaktifkan.</p>
            @else
                <p><strong>{{ auth()->user()->organization?->name ?? 'Organisasi belum diatur' }}</strong></p>
                <p>Gunakan akun ini untuk melengkapi data dan mengajukan layanan organisasi.</p>
            @endif
        </section>
    </main>
@endsection
