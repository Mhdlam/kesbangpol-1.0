@extends('layouts.app')

@section('title', 'Notifikasi System')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Title & Header -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('user.dashboard') }}" class="text-xs font-bold text-gov-600 dark:text-sky-400 hover:underline">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2 mt-1">
                <i class="fa-solid fa-bell text-amber-500"></i> Notifikasi System Ormas
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Riwayat pemberitahuan, instruksi revisi, dan status pengajuan dari Badan Kesbangpol Kota Palembang.
            </p>
        </div>

        @if($notifications->count() > 0)
            <form action="{{ route('user.notifications.read_all') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-check-double text-gov-600"></i> Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    <!-- Info Auto Clean 30 Days -->
    <div class="mb-4 bg-blue-50/80 dark:bg-slate-800/80 border border-blue-200 dark:border-slate-700 rounded-xl p-3.5 flex items-center gap-3 text-xs text-blue-900 dark:text-blue-200 shadow-sm">
        <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
        <span><strong>Informasi Sistem:</strong> Semua history notifikasi akan terhapus otomatis dalam jangka waktu maksimal 30hari.</span>
    </div>

    <!-- Web Push Desktop Notification Card -->
    <div class="mb-6">
        @include('partials.webpush_card')
    </div>

    <!-- Notifications List Container -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        @if($notifications->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-amber-50 dark:bg-amber-950/40 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-regular fa-bell-slash"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Belum Ada Notifikasi</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    Semua pemberitahuan dan status pengajuan berkas Ormas Anda akan muncul secara realtime di halaman ini.
                </p>
            </div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($notifications as $notif)
                    <div class="p-5 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $notif->is_read ? 'bg-white dark:bg-slate-900' : 'bg-amber-50/50 dark:bg-amber-950/20' }}">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center text-base {{ $notif->is_read ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' : 'bg-amber-500 text-white shadow-sm' }}">
                                @if(str_contains($notif->type, 'approved'))
                                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                                @elseif(str_contains($notif->type, 'revision'))
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400"></i>
                                @elseif(str_contains($notif->type, 'rejected'))
                                    <i class="fa-solid fa-circle-xmark text-rose-600 dark:text-rose-400"></i>
                                @elseif(str_contains($notif->type, 'chat'))
                                    <i class="fa-solid fa-comment-dots text-sky-600 dark:text-sky-400"></i>
                                @else
                                    <i class="fa-solid fa-bell"></i>
                                @endif
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ $notif->title }}
                                    </h4>
                                    @if(!$notif->is_read)
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-amber-500 text-white rounded-full">Baru</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                    {{ $notif->message }}
                                </p>
                                <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-3">
                                    <span><i class="fa-regular fa-clock me-1"></i> {{ $notif->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                                    <span>&bull; {{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                            @if($notif->action_url)
                                <form action="{{ route('user.notifications.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 bg-gov-600 hover:bg-gov-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm">
                                        <span>Buka Detail</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </button>
                                </form>
                            @elseif(!$notif->is_read)
                                <form action="{{ route('user.notifications.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg hover:bg-slate-200 transition-colors">
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
