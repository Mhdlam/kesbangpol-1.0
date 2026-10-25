<!-- Web Push Notification Card Component -->
<div x-data="webPushManager()" x-init="init()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 transition-colors"
                 :class="{
                     'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400': isSubscribed,
                     'bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400': !isSubscribed && permission !== 'denied',
                     'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400': permission === 'denied' || !isSupported
                 }">
                <i :class="{
                    'fa-solid fa-bell': isSubscribed,
                    'fa-regular fa-bell': !isSubscribed && permission !== 'denied',
                    'fa-solid fa-bell-slash': permission === 'denied' || !isSupported
                }"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">
                        Notifikasi Desktop (Web Push)
                    </h3>
                    
                    <!-- Dynamic Status Badge -->
                    <template x-if="!isSupported">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                            Tidak Didukung
                        </span>
                    </template>
                    <template x-if="isSupported && permission === 'denied'">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800 flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> Izin Ditolak
                        </span>
                    </template>
                    <template x-if="isSupported && permission !== 'denied' && isSubscribed">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i> Aktif di Browser Ini
                        </span>
                    </template>
                    <template x-if="isSupported && permission !== 'denied' && !isSubscribed">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                            Belum Aktif
                        </span>
                    </template>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Dapatkan pop-up pemberitahuan langsung di layar Windows 11 Anda untuk status pengajuan, revisi berkas, dan pesan penting secara real-time.
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
            <!-- Test Notification Button (visible when subscribed) -->
            <button x-show="isSubscribed"
                    @click="testNotification()"
                    :disabled="isTesting"
                    type="button"
                    class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                <i class="fa-solid fa-paper-plane text-gov-600 dark:text-sky-400" :class="{ 'fa-spin': isTesting }"></i>
                <span x-text="isTesting ? 'Mengirim...' : 'Uji Coba Notifikasi'"></span>
            </button>

            <!-- Toggle Button -->
            <button @click="toggleSubscription()"
                    :disabled="isLoading || !isSupported"
                    type="button"
                    class="px-4 py-2 text-xs font-black rounded-xl transition-all shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="{
                        'bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/80 dark:text-rose-300 border border-rose-200 dark:border-rose-800': isSubscribed,
                        'bg-gov-600 hover:bg-gov-700 text-white': !isSubscribed
                    }">
                <i class="fa-solid" :class="{
                    'fa-spinner fa-spin': isLoading,
                    'fa-bell-slash': !isLoading && isSubscribed,
                    'fa-bell': !isLoading && !isSubscribed
                }"></i>
                <span x-text="isLoading ? 'Memproses...' : (isSubscribed ? 'Nonaktifkan Notifikasi' : 'Aktifkan Notifikasi Desktop')"></span>
            </button>
        </div>
    </div>

    <!-- Feedback Status Alert Box -->
    <div x-show="statusMessage" x-cloak class="p-3.5 rounded-xl text-xs flex items-start gap-2.5 transition-all"
         :class="{
             'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800': statusType === 'success',
             'bg-amber-50 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200 border border-amber-200 dark:border-amber-800': statusType === 'warning',
             'bg-rose-50 text-rose-900 dark:bg-rose-950/50 dark:text-rose-200 border border-rose-200 dark:border-rose-800': statusType === 'error',
             'bg-blue-50 text-blue-900 dark:bg-blue-950/50 dark:text-blue-200 border border-blue-200 dark:border-blue-800': statusType === 'info'
         }">
        <i class="fa-solid mt-0.5"
           :class="{
               'fa-circle-check text-emerald-600': statusType === 'success',
               'fa-triangle-exclamation text-amber-600': statusType === 'warning',
               'fa-circle-xmark text-rose-600': statusType === 'error',
               'fa-circle-info text-blue-600': statusType === 'info'
           }"></i>
        <div class="flex-1" x-text="statusMessage"></div>
        <button @click="statusMessage = ''" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Microsoft Edge & Windows 11 Guide Dropdown / Info -->
    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-500 dark:text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-1.5">
            <i class="fa-brands fa-edge text-sky-600 dark:text-sky-400"></i>
            <span>Didukung di Microsoft Edge & Google Chrome pada Windows 11 (Localhost & HTTPS).</span>
        </div>
        <div class="text-[10px] text-slate-400 dark:text-slate-500 italic">
            *Pastikan opsi background apps di Edge aktif jika ingin notifikasi tetap diterima saat browser ditutup.
        </div>
    </div>
</div>
