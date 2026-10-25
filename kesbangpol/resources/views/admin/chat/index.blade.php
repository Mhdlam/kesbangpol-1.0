@extends('layouts.app')

@section('title', 'Admin Chat Center & Konsultasi Ormas')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Header Banner & Action Modal Trigger -->
    <div class="mb-4 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-gov-600 dark:text-sky-400 hover:underline">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard Admin
                </a>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2">
                <i class="fa-solid fa-comments text-gov-600 dark:text-sky-400"></i> Admin Chat Center & Konsultasi Ormas
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Pusat pesan langsung, bantuan konsultasi berkas, dan pengiriman notifikasi pengumuman ke Organisasi Masyarakat.
            </p>
        </div>

        <div class="flex items-center gap-2" x-data="{ openBroadcastModal: false, targetType: 'all' }">
            <!-- Broadcast Notification Modal Trigger -->
            <button @click="openBroadcastModal = true" type="button" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                <i class="fa-solid fa-bullhorn"></i> Kirim Pengumuman Notifikasi
            </button>

            <!-- Broadcast Modal -->
            <div x-show="openBroadcastModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div @click.away="openBroadcastModal = false" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-bullhorn text-amber-500"></i> Kirim Notifikasi Pengumuman Langsung
                        </h3>
                        <button @click="openBroadcastModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.notifications.broadcast') }}" method="POST" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Target Penerima</label>
                            <select name="target_type" x-model="targetType" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl font-medium focus:ring-2 focus:ring-gov-500">
                                <option value="all">📢 Seluruh Pemohon Ormas (Broadcast)</option>
                                <option value="specific">👤 Khusus Ormas Aktif Saat Ini ({{ $activeOrganization?->name ?: 'Pilih Ormas' }})</option>
                                <option value="multiple">👥 Pilih Beberapa Ormas Spesifik (Pilih beberapa)</option>
                            </select>
                            <input type="hidden" name="target_user_id" value="{{ $activeOrganization?->user_id }}">

                            <!-- Multi-Ormas Checkbox Selection List -->
                            <div x-show="targetType === 'multiple'" class="mt-2 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2 max-h-48 overflow-y-auto">
                                <span class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Centang Ormas Penerima:</span>
                                @foreach($organizations as $orgItem)
                                    @if($orgItem->user_id)
                                        <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-900 p-1.5 rounded-lg transition-colors">
                                            <input type="checkbox" name="target_user_ids[]" value="{{ $orgItem->user_id }}" class="w-4 h-4 rounded text-gov-600 border-slate-300 focus:ring-gov-500">
                                            <span class="truncate font-semibold">{{ $orgItem->name }}</span>
                                            <span class="text-[10px] text-slate-400">({{ $orgItem->user?->name ?: 'Pengurus' }})</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>


                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Judul Notifikasi</label>
                            <input type="text" name="title" placeholder="Contoh: Pemberitahuan Jadwal Verifikasi Lapangan" required
                                   class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl font-medium focus:ring-2 focus:ring-gov-500">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Isi Pesan Notifikasi</label>
                            <textarea name="message" rows="3" placeholder="Tuliskan isi pengumuman penting di sini..." required
                                      class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl font-medium focus:ring-2 focus:ring-gov-500 resize-none"></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">URL Aksi (Opsional)</label>
                            <input type="text" name="action_url" placeholder="Contoh: {{ route('user.history') }}"
                                   class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl font-medium focus:ring-2 focus:ring-gov-500">
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="openBroadcastModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-sm">
                                Kirim Notifikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Auto Delete 30 Days -->
    <div class="mb-4 bg-blue-50/80 dark:bg-slate-800/80 border border-blue-200 dark:border-slate-700 rounded-xl p-3.5 flex items-center gap-3 text-xs text-blue-900 dark:text-blue-200 shadow-sm">
        <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
        <span><strong>Informasi Sistem:</strong> Semua History Chat akan Otomatis terhapus dalam kurun waktu 30 Hari.</span>
    </div>

    <!-- Main Grid: Sidebar List Ormas + Chat Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[640px]">
        
        <!-- Left Sidebar: Ormas Thread List (4 Columns) -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden">
            
            <!-- Search & Filter Bar -->
            <div class="p-3.5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800">
                <form action="{{ route('admin.chat.index') }}" method="GET" class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari Nama Ormas / Pengurus..."
                           class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-gov-500 focus:outline-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </form>
            </div>

            <!-- List of Ormas Conversations -->
            <div class="flex-grow overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                @if($organizations->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-inbox text-2xl mb-2 block"></i>
                        <span>Belum ada Ormas terdaftar.</span>
                    </div>
                @else
                    @foreach($organizations as $org)
                        @php
                            $isActive = ($activeOrganization && $activeOrganization->id === $org->id);
                            $lastMsg = \App\Models\ChatMessage::where('organization_id', $org->id)->latest()->first();
                        @endphp
                        <a href="{{ route('admin.chat.index', ['org' => $org->id]) }}" data-turbo="false"
                           class="p-3.5 block transition-colors {{ $isActive ? 'bg-gov-50/80 dark:bg-slate-800/80 border-l-4 border-gov-600 dark:border-sky-400' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-xs font-extrabold text-slate-900 dark:text-white truncate">
                                    {{ $org->name }}
                                </span>
                                @if($org->unread_count > 0)
                                    <span class="px-2 py-0.5 text-[10px] font-black bg-emerald-600 text-white rounded-full animate-pulse">
                                        {{ $org->unread_count }} baru
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="truncate max-w-[180px]">
                                    @if($lastMsg)
                                        {{ Str::limit($lastMsg->message, 35) }}
                                    @else
                                        Belum ada pesan
                                    @endif
                                </span>
                                <span class="text-[10px] text-slate-400 flex-shrink-0">
                                    {{ $lastMsg ? $lastMsg->created_at->diffForHumans(null, true) : '' }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Right Main Panel: Active Chat Room (8 Columns) -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden">
            @if($activeOrganization)
                <!-- Active Room Header -->
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                                Ormas: {{ $activeOrganization->name }}
                            </h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $activeOrganization->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-700' }}">
                                {{ strtoupper($activeOrganization->status ?: 'aktif') }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Pengurus: <strong class="text-slate-700 dark:text-slate-200">{{ $activeOrganization->user?->name }}</strong> &bull; No. Reg: {{ $activeOrganization->registration_number ?: '-' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($latestSubmission)
                            <a href="{{ route('admin.submissions.show', $latestSubmission->id) }}" class="px-3 py-1.5 bg-gov-600 hover:bg-gov-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                                <i class="fa-solid fa-folder-open"></i> Berkas (#{{ $latestSubmission->tracking_code }})
                            </a>
                        @endif
                        <a href="{{ route('admin.organizations.show', $activeOrganization->id) }}" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg hover:bg-slate-300 transition-colors">
                            Profil Ormas
                        </a>
                    </div>
                </div>

                <!-- Messages Stream Container -->
                <div id="admin-chat-messages-container" class="flex-grow p-4 sm:p-5 overflow-y-auto space-y-4 bg-slate-50/50 dark:bg-slate-950/40">
                    @if($messages->isEmpty())
                        <div id="admin-chat-empty" class="h-full flex flex-col items-center justify-center text-center p-6">
                            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-full flex items-center justify-center text-xl mb-3">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Belum Ada Pesan Dengan Ormas Ini</h4>
                            <p class="text-[11px] text-slate-500 max-w-xs mt-1">
                                Kirimkan instruksi, teguran, atau jawaban atas konsultasi Ormas {{ $activeOrganization->name }}.
                            </p>
                        </div>
                    @else
                        @foreach($messages as $msg)
                            @php $isMe = ($msg->sender_id === auth()->id()); @endphp
                            <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                                <!-- Message Metadata Header -->
                                <div class="flex items-center gap-2 mb-1 px-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                    <span class="font-bold {{ $isMe ? 'text-gov-600 dark:text-sky-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $isMe ? 'Admin Bakesbangpol (' . auth()->user()->name . ')' : 'Pengurus Ormas: ' . $activeOrganization->name }}
                                    </span>
                                    <span>&bull;</span>
                                    <span><i class="fa-regular fa-clock me-0.5"></i> {{ $msg->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                </div>

                                <!-- Message Content Bubble -->
                                <div class="max-w-lg p-3.5 rounded-2xl text-xs leading-relaxed shadow-sm {{ $isMe ? 'bg-gov-600 text-white rounded-tr-none' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-tl-none' }}">
                                    <p class="whitespace-pre-line">{{ $msg->message }}</p>

                                    @if($msg->attachment_path)
                                        <div class="mt-2.5 pt-2 border-t {{ $isMe ? 'border-gov-500' : 'border-slate-200 dark:border-slate-700' }}">
                                            <a href="{{ route('chat.download_attachment', $msg->id) }}" target="_blank" class="inline-flex items-center gap-2 font-semibold hover:underline {{ $isMe ? 'text-sky-200' : 'text-gov-600 dark:text-sky-400' }}">
                                                <i class="fa-solid fa-paperclip"></i>
                                                <span class="truncate max-w-[200px]">{{ $msg->attachment_original_name ?: 'Download Lampiran' }}</span>
                                                <i class="fa-solid fa-download text-[10px]"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Read Status Indicator -->
                                <div class="mt-1 px-1 text-[10px] text-slate-400">
                                    @if($isMe)
                                        @if($msg->is_read)
                                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold"><i class="fa-solid fa-check-double"></i> Dibaca oleh Ormas</span>
                                        @else
                                            <span><i class="fa-solid fa-check"></i> Terkirim</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Admin Message Input Form -->
                <div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                    <div id="admin-attachment-preview" class="hidden mb-2 p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <div class="flex items-center gap-2 truncate">
                            <i class="fa-solid fa-paperclip text-gov-600"></i>
                            <span id="admin-attachment-filename" class="font-bold truncate"></span>
                        </div>
                        <button type="button" onclick="clearAdminAttachment()" class="text-rose-500 hover:text-rose-700 font-bold px-2">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form id="admin-chat-form" action="{{ route('admin.chat.store', $activeOrganization->id) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-2">
                        @csrf
                        <label for="admin-chat-attachment" class="p-2.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 cursor-pointer transition-colors flex-shrink-0" title="Lampirkan Dokumen PDF/Gambar">
                            <i class="fa-solid fa-paperclip text-sm"></i>
                            <input type="file" id="admin-chat-attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleAdminFileSelect(this)">
                        </label>

                        <div class="flex-grow relative">
                            <textarea id="admin-message-input" name="message" rows="2" placeholder="Tuliskan balasan pesan atau petunjuk untuk Ormas {{ $activeOrganization->name }}..."
                                      class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-gov-500 resize-none"></textarea>
                        </div>

                        <button type="submit" id="admin-chat-submit-btn" class="px-5 py-3.5 bg-gov-600 hover:bg-gov-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2 flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="admin-chat-submit-text">Balas</span>
                            <i id="admin-chat-submit-icon" class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            @else
                <div class="h-full flex flex-col items-center justify-center text-center p-8">
                    <div class="w-16 h-16 bg-gov-50 dark:bg-slate-800 text-gov-600 dark:text-sky-400 rounded-full flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Pilih Ormas Untuk Memulai Chat</h3>
                    <p class="text-xs text-slate-500 max-w-sm mt-1">
                        Pilih nama Ormas pada daftar panel sebelah kiri untuk melihat riwayat pesan dan memberikan konsultasi.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($activeOrganization)
<script>
    const adminChatContainer = document.getElementById('admin-chat-messages-container');
    const adminChatForm = document.getElementById('admin-chat-form');
    const adminMessageInput = document.getElementById('admin-message-input');
    const adminFileInput = document.getElementById('admin-chat-attachment');
    const adminAttachmentPreview = document.getElementById('admin-attachment-preview');
    const adminAttachmentFilename = document.getElementById('admin-attachment-filename');
    const adminSubmitBtn = document.getElementById('admin-chat-submit-btn');
    const adminSubmitText = document.getElementById('admin-chat-submit-text');
    const adminSubmitIcon = document.getElementById('admin-chat-submit-icon');

    let isAdminSubmitting = false;

    function scrollAdminToBottom() {
        if (adminChatContainer) {
            adminChatContainer.scrollTop = adminChatContainer.scrollHeight;
        }
    }

    function handleAdminFileSelect(input) {
        if (input.files && input.files[0]) {
            adminAttachmentFilename.innerText = input.files[0].name;
            adminAttachmentPreview.classList.remove('hidden');
        }
    }

    function clearAdminAttachment() {
        adminFileInput.value = '';
        adminAttachmentPreview.classList.add('hidden');
        adminAttachmentFilename.innerText = '';
    }

    // Enter Key Handler to Send Message (Enter = Kirim, Shift+Enter = Baris Baru)
    if (adminMessageInput) {
        adminMessageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (adminChatForm) {
                    adminChatForm.requestSubmit();
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', scrollAdminToBottom);

    adminChatForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (isAdminSubmitting) return;

        const msg = adminMessageInput.value.trim();
        const hasFile = adminFileInput.files && adminFileInput.files.length > 0;

        if (!msg && !hasFile) {
            alert('Pesan atau berkas lampiran wajib diisi.');
            return;
        }

        isAdminSubmitting = true;
        if (adminSubmitBtn) {
            adminSubmitBtn.disabled = true;
            if (adminSubmitText) adminSubmitText.innerText = 'Mengirim...';
            if (adminSubmitIcon) adminSubmitIcon.className = 'fa-solid fa-spinner fa-spin';
        }

        const formData = new FormData(adminChatForm);

        fetch("{{ route('admin.chat.store', $activeOrganization->id) }}", {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                adminMessageInput.value = '';
                clearAdminAttachment();
                fetchAdminMessages();
            } else {
                alert(data.message || 'Gagal mengirim pesan.');
            }
        })
        .catch(err => {
            console.error('Chat error:', err);
            alert('Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.');
        })
        .finally(() => {
            isAdminSubmitting = false;
            if (adminSubmitBtn) {
                adminSubmitBtn.disabled = false;
                if (adminSubmitText) adminSubmitText.innerText = 'Balas';
                if (adminSubmitIcon) adminSubmitIcon.className = 'fa-solid fa-paper-plane';
            }
        });
    });

    function fetchAdminMessages() {
        fetch("{{ route('admin.chat.fetch_messages', $activeOrganization->id) }}", {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    const emptyState = document.getElementById('admin-chat-empty');
                    if (emptyState) emptyState.remove();

                    let html = '';
                    data.messages.forEach(msg => {
                        const isMe = msg.is_me;
                        html += `
                            <div class="flex flex-col ${isMe ? 'items-end' : 'items-start'} mb-4">
                                <div class="flex items-center gap-2 mb-1 px-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                    <span class="font-bold ${isMe ? 'text-gov-600 dark:text-sky-400' : 'text-amber-600 dark:text-amber-400'}">
                                        ${isMe ? msg.sender_name : msg.sender_role}
                                    </span>
                                    <span>&bull;</span>
                                    <span><i class="fa-regular fa-clock me-0.5"></i> ${msg.created_at_formatted}</span>
                                </div>

                                <div class="max-w-lg p-3.5 rounded-2xl text-xs leading-relaxed shadow-sm ${isMe ? 'bg-gov-600 text-white rounded-tr-none' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-tl-none'}">
                                    <p class="whitespace-pre-line">${msg.message}</p>
                                    ${msg.attachment_url ? `
                                        <div class="mt-2.5 pt-2 border-t ${isMe ? 'border-gov-500' : 'border-slate-200 dark:border-slate-700'}">
                                            <a href="${msg.attachment_url}" target="_blank" class="inline-flex items-center gap-2 font-semibold hover:underline ${isMe ? 'text-sky-200' : 'text-gov-600 dark:text-sky-400'}">
                                                <i class="fa-solid fa-paperclip"></i>
                                                <span class="truncate max-w-[200px]">${msg.attachment_name || 'Download Lampiran'}</span>
                                                <i class="fa-solid fa-download text-[10px]"></i>
                                            </a>
                                        </div>
                                    ` : ''}
                                </div>

                                <div class="mt-1 px-1 text-[10px] text-slate-400">
                                    ${isMe ? (msg.is_read ? '<span class="text-emerald-600 dark:text-emerald-400 font-semibold"><i class="fa-solid fa-check-double"></i> Dibaca oleh Ormas</span>' : '<span><i class="fa-solid fa-check"></i> Terkirim</span>') : ''}
                                </div>
                            </div>
                        `;
                    });

                    const isAtBottom = (adminChatContainer.scrollHeight - adminChatContainer.scrollTop - adminChatContainer.clientHeight) < 100;
                    adminChatContainer.innerHTML = html;
                    if (isAtBottom) scrollAdminToBottom();
                }
            })
            .catch(() => {});
    }

    const adminChatInterval = setInterval(fetchAdminMessages, 3000);
    document.addEventListener('turbo:before-cache', () => clearInterval(adminChatInterval));
    window.addEventListener('beforeunload', () => clearInterval(adminChatInterval));
</script>
@endif
@endpush

