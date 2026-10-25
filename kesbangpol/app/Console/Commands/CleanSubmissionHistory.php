<?php

namespace App\Console\Commands;

use App\Models\Submission;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanSubmissionHistory extends Command
{
    protected $signature = 'submission:clean-history {--days=30 : Number of days of history to retain}';

    protected $description = 'Otomatis menghapus riwayat pengajuan berkas yang berusia lebih dari 30 hari tanpa menghapus akun user.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $oldSubmissions = Submission::where('created_at', '<', $cutoffDate)->get();
        $count = $oldSubmissions->count();

        foreach ($oldSubmissions as $submission) {
            // Delete documents attached
            foreach ($submission->documents as $doc) {
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($doc->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($doc->file_path);
                }
                $doc->delete();
            }

            // Delete histories and submission record
            $submission->histories()->delete();
            $submission->delete();
        }

        $this->info("Pembersihan selesai: {$count} riwayat pengajuan berkas yang berusia > {$days} hari telah berhasil dihapus.");

        return Command::SUCCESS;
    }
}
