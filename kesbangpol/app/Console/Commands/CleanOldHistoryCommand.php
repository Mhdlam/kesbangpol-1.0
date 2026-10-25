<?php

namespace App\Console\Commands;

use App\Services\HistoryCleaner;
use Illuminate\Console\Command;

class CleanOldHistoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus otomatis riwayat Chat dan Notifikasi yang berumur lebih dari 30 hari';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pembersihan riwayat Chat dan Notifikasi (>30 hari)...');

        HistoryCleaner::cleanOldHistory();

        $this->info('Pembersihan riwayat selesai dilakukan.');

        return Command::SUCCESS;
    }
}
