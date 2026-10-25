@extends('layouts.app')

@section('title', 'Notification Center Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-bell text-amber-500"></i> Notification Center Admin
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Pemberitahuan real-time aktivitas pengajuan, unggahan revisi Ormas, dan event sistem.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.notifications.read_all') }}">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 text-slate-800 dark:text-slate-200 font-bold text-xs transition-colors">
                <i class="fa-solid fa-check-double me-1"></i> Tandai Semua Dibaca
            </button>
        </form>
    </div>

    <!-- Info Auto Clean 30 Days -->
    <div class="bg-blue-50/80 dark:bg-slate-800/80 border border-blue-200 dark:border-slate-700 rounded-xl p-3.5 flex items-center gap-3 text-xs text-blue-900 dark:text-blue-200 shadow-sm">
        <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
        <span><strong>Informasi Sistem:</strong> Semua history notifikasi akan terhapus otomatis dalam jangka waktu maksimal 30hari.</span>
    </div>

    <!-- Web Push Desktop Notification Card -->
    <div>
        @include('partials.webpush_card')
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($notifications as $n)
            <div class="p-5 flex items-start justify-between gap-4 transition-colors {{ !$n->is_read ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400 font-bold flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-sm text-slate-900 dark:text-white block">{{ $n->title }}</span>
                        <p class="text-xs text-slate-700 dark:text-slate-300 mt-1">{{ $n->message }}</p>
                        <span class="text-[10px] text-slate-400 mt-1 block">{{ $n->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($n->action_url)
                        <form method="POST" action="{{ route('admin.notifications.read', $n->id) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs transition-colors shadow-sm">
                                Buka Halaman &raquo;
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-slate-400">
                <i class="fa-solid fa-bell-slash text-4xl mb-2 block"></i>
                Belum ada notifikasi baru.
            </div>
        @endforelse
    </div>

    <div class="p-2">
        {{ $notifications->links() }}
    </div>

</div>
@endsection
