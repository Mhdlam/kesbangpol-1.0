@extends('layouts.app')

@section('title', 'Chat & Konsultasi Kesbangpol')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Page Header & Ormas Banner -->
    <div class="mb-4 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gov-600 dark:bg-gov-500 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 shadow-sm">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-sky-300">
                        NAMA ORMAS: {{ strtoupper($organization?->name ?: $user->name) }}
                    </span>
                    @if($latestSubmission)
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            No. Tracking: #{{ $latestSubmission->tracking_code }}
                        </span>
                    @endif
                </div>
                <h1 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                    Layanan Chat & Konsultasi Kesbangpol Kota Palembang
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Pusat Komunikasi & Tanya Jawab Realtime antara Pengurus Ormas dengan Tim Verifikator Kesbangpol.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start md:self-center">
            <a href="{{ route('user.dashboard') }}" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-colors">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Info Auto Delete 30 Days -->
    <div class="mb-4 bg-blue-50/80 dark:bg-slate-800/80 border border-blue-200 dark:border-slate-700 rounded-xl p-3.5 flex items-center gap-3 text-xs text-blue-900 dark:text-blue-200 shadow-sm">
        <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
        <span><strong>Informasi Sistem:</strong> Semua History Chat akan Otomatis terhapus dalam kurun waktu 30 Hari.</span>
    </div>

    <!-- Main Chat Window Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col h-[620px] overflow-hidden">
        
        <!-- Chat Header Bar -->
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-9 h-9 rounded-full bg-gov-600 text-white font-bold flex items-center justify-center text-xs">
                        KB
                    </div>
                    <span id="admin-online-dot" class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full {{ !empty($isAnyAdminOnline) ? 'bg-emerald-500' : 'bg-slate-400' }} ring-2 ring-white dark:ring-slate-950"></span>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        Tim Petugas Verifikasi Bakesbangpol Kota Palembang
                    </h3>
                    <span id="admin-online-text-container" class="text-[11px] font-medium {{ !empty($isAnyAdminOnline) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }} flex items-center gap-1">
                        <i id="admin-online-icon" class="fa-solid fa-circle text-[7px] {{ !empty($isAnyAdminOnline) ? 'text-emerald-500' : 'text-slate-400' }}"></i>
                        <span id="admin-online-status-text">{{ !empty($isAnyAdminOnline) ? 'Layanan Online (Respons Cepat Jam Kerja)' : 'Layanan Offline (Respons Cepat Jam Kerja)' }}</span>
                    </span>
                </div>
            </div>

            <div class="text-right">
                <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    {{ $organization?->name ?: $user->name }}
                </span>
                <span class="block text-[10px] text-slate-500">
                    Kategori: {{ $organization?->category ?: 'Ormas Terdaftar' }}
                </span>
            </div>
        </div>

        <!-- Messages Body Container -->
        <div id="chat-messages-container" class="flex-grow p-4 sm:p-5 overflow-y-auto space-y-4 bg-slate-50/50 dark:bg-slate-950/40">
            @if($messages->isEmpty())
                <div id="chat-empty-state" class="h-full flex flex-col items-center justify-center text-center p-6">
                    <div class="w-14 h-14 bg-gov-50 dark:bg-slate-800 text-gov-600 dark:text-sky-400 rounded-full flex items-center justify-center text-2xl mb-3">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Ada Riwayat Percakapan</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mt-1">
                        Kirimkan pertanyaan atau konsultasikan kendala kelengkapan berkas Anda di sini. Petugas Admin Kesbangpol siap membantu Anda secara realtime.
                    </p>
                    
                    <!-- Quick Question Presets -->
                    <div class="mt-6 flex flex-wrap gap-2 justify-center max-w-md">
                        <button onclick="setPresetMessage('Selamat pagi Admin, izin bertanya mengenai status pemeriksaan berkas pendaftaran Ormas kami.')" 
                                class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-300 hover:border-gov-500 transition-colors">
                            💬 Status Pemeriksaan Berkas
                        </button>
                        <button onclick="setPresetMessage('Mohon arahan Admin terkait dokumen revisi yang harus diperbaiki.')" 
                                class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-300 hover:border-gov-500 transition-colors">
                            📑 Konsultasi Dokumen Revisi
                        </button>
                    </div>
                </div>
            @else
                @foreach($messages as $msg)
                    @php $isMe = ($msg->sender_id === $user->id); @endphp
                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        <!-- Message Metadata Header -->
                        <div class="flex items-center gap-2 mb-1 px-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                            <span class="font-bold {{ $isMe ? 'text-gov-600 dark:text-sky-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $isMe ? 'Pengurus Ormas (' . ($organization?->name ?: $user->name) . ')' : 'Admin Bakesbangpol (' . ($msg->sender?->name ?: 'Admin') . ')' }}
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
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold"><i class="fa-solid fa-check-double"></i> Dibaca {{ $msg->read_at?->format('H:i') }}</span>
                                @else
                                    <span><i class="fa-solid fa-check"></i> Terkirim</span>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Chat Input Form Container -->
        <div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            
            <!-- Attachment Preview Bar -->
            <div id="attachment-preview" class="hidden mb-2 p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300 flex items-center justify-between">
                <div class="flex items-center gap-2 truncate">
                    <i class="fa-solid fa-paperclip text-gov-600"></i>
                    <span id="attachment-filename" class="font-bold truncate"></span>
                </div>
                <button type="button" onclick="clearAttachment()" class="text-rose-500 hover:text-rose-700 font-bold px-2">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="chat-form" action="{{ route('user.chat.store') }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-2">
                @csrf
                
                <!-- File Attachment Input Button -->
                <label for="chat-attachment" class="p-2.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 cursor-pointer transition-colors flex-shrink-0" title="Lampirkan File PDF/Gambar">
                    <i class="fa-solid fa-paperclip text-sm"></i>
                    <input type="file" id="chat-attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="handleFileSelect(this)">
                </label>

                <!-- Textarea Message Input -->
                <div class="flex-grow relative">
                    <textarea id="chat-message-input" name="message" rows="2" placeholder="Tuliskan pesan atau pertanyaan Anda di sini..."
                              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-gov-500 resize-none"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="chat-submit-btn" class="px-5 py-3.5 bg-gov-600 hover:bg-gov-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2 flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="chat-submit-text">Kirim</span>
                    <i id="chat-submit-icon" class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const chatContainer = document.getElementById('chat-messages-container');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('chat-message-input');
    const fileInput = document.getElementById('chat-attachment');
    const attachmentPreview = document.getElementById('attachment-preview');
    const attachmentFilename = document.getElementById('attachment-filename');
    const submitBtn = document.getElementById('chat-submit-btn');
    const submitText = document.getElementById('chat-submit-text');
    const submitIcon = document.getElementById('chat-submit-icon');

    let isUserSubmitting = false;

    function scrollToBottom() {
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    function setPresetMessage(txt) {
        messageInput.value = txt;
        messageInput.focus();
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            attachmentFilename.innerText = input.files[0].name;
            attachmentPreview.classList.remove('hidden');
        }
    }

    function clearAttachment() {
        fileInput.value = '';
        attachmentPreview.classList.add('hidden');
        attachmentFilename.innerText = '';
    }

    function updateAdminOnlineStatus(isOnline) {
        const dot = document.getElementById('admin-online-dot');
        const icon = document.getElementById('admin-online-icon');
        const container = document.getElementById('admin-online-text-container');
        const statusText = document.getElementById('admin-online-status-text');

        if (isOnline) {
            if (dot) dot.className = 'absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-950';
            if (icon) icon.className = 'fa-solid fa-circle text-[7px] text-emerald-500';
            if (container) container.className = 'text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1';
            if (statusText) statusText.innerText = 'Layanan Online (Respons Cepat Jam Kerja)';
        } else {
            if (dot) dot.className = 'absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-slate-400 ring-2 ring-white dark:ring-slate-950';
            if (icon) icon.className = 'fa-solid fa-circle text-[7px] text-slate-400';
            if (container) container.className = 'text-[11px] font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1';
            if (statusText) statusText.innerText = 'Layanan Offline (Respons Cepat Jam Kerja)';
        }
    }

    // Enter Key Handler to Send Message (Enter = Kirim, Shift+Enter = Baris Baru)
    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (chatForm) {
                    chatForm.requestSubmit();
                }
            }
        });
    }

    // Scroll to bottom on initial load
    document.addEventListener('DOMContentLoaded', scrollToBottom);

    // Form submit via AJAX for instant response
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (isUserSubmitting) return;

        const msg = messageInput.value.trim();
        const hasFile = fileInput.files && fileInput.files.length > 0;

        if (!msg && !hasFile) {
            alert('Pesan atau berkas lampiran wajib diisi.');
            return;
        }

        isUserSubmitting = true;
        if (submitBtn) {
            submitBtn.disabled = true;
            if (submitText) submitText.innerText = 'Mengirim...';
            if (submitIcon) submitIcon.className = 'fa-solid fa-spinner fa-spin';
        }

        const formData = new FormData(chatForm);

        fetch("{{ route('user.chat.store') }}", {
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
                messageInput.value = '';
                clearAttachment();
                fetchMessages(); // Refresh messages immediately
            } else {
                alert(data.message || 'Gagal mengirim pesan.');
            }
        })
        .catch(err => {
            console.error('Chat error:', err);
            alert('Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.');
        })
        .finally(() => {
            isUserSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                if (submitText) submitText.innerText = 'Kirim';
                if (submitIcon) submitIcon.className = 'fa-solid fa-paper-plane';
            }
        });
    });

    // Real-Time Polling every 3 seconds
    function fetchMessages() {
        fetch("{{ route('user.chat.fetch_messages') }}", {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.is_admin_online !== undefined) {
                    updateAdminOnlineStatus(data.is_admin_online);
                }

                if (data.messages && data.messages.length > 0) {
                    const emptyState = document.getElementById('chat-empty-state');
                    if (emptyState) emptyState.remove();

                    let html = '';
                    data.messages.forEach(msg => {
                        const isMe = msg.is_me;
                        html += `
                            <div class="flex flex-col ${isMe ? 'items-end' : 'items-start'} mb-4">
                                <div class="flex items-center gap-2 mb-1 px-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                    <span class="font-bold ${isMe ? 'text-gov-600 dark:text-sky-400' : 'text-amber-600 dark:text-amber-400'}">
                                        ${isMe ? 'Pengurus Ormas (' + msg.organization_name + ')' : msg.sender_role + ' (' + msg.sender_name + ')'}
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
                                    ${isMe ? (msg.is_read ? '<span class="text-emerald-600 dark:text-emerald-400 font-semibold"><i class="fa-solid fa-check-double"></i> Dibaca</span>' : '<span><i class="fa-solid fa-check"></i> Terkirim</span>') : ''}
                                </div>
                            </div>
                        `;
                    });

                    const isAtBottom = (chatContainer.scrollHeight - chatContainer.scrollTop - chatContainer.clientHeight) < 100;
                    chatContainer.innerHTML = html;
                    if (isAtBottom) scrollToBottom();
                }
            })
            .catch(() => {});
    }

    const chatInterval = setInterval(fetchMessages, 3000);
    document.addEventListener('turbo:before-cache', () => clearInterval(chatInterval));
    window.addEventListener('beforeunload', () => clearInterval(chatInterval));
</script>
@endpush

