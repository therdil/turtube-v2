<?php

namespace App\Console\Commands;

use App\Models\UploadBatch;
use App\Models\UploadSession;
use App\Models\Video;
use App\Services\R2UploadService;
use Illuminate\Console\Command;

class ReportExpiredUploadObjects extends Command
{
    protected $signature = 'uploads:cleanup
        {--dry-run : Report eligible objects without deleting anything}
        {--grace=120 : Grace period in seconds after expiration}';

    protected $description = 'Reports expired or failed direct-upload objects that may be cleaned up later.';

    public function handle(R2UploadService $uploads): int
    {
        if (! $this->option('dry-run')) {
            $this->error('Bu komut yalnızca --dry-run ile çalışır; hiçbir silme modu sunulmaz.');

            return self::INVALID;
        }

        $grace = max(0, (int) $this->option('grace'));
        $cutoff = now()->subSeconds($grace);

        $sessions = UploadSession::query()
            ->where(function ($query) use ($cutoff): void {
                $query->whereIn('status', [UploadSession::STATUS_EXPIRED, UploadSession::STATUS_FAILED])
                    ->orWhere(function ($expired) use ($cutoff): void {
                        $expired->where('status', UploadSession::STATUS_PENDING)
                            ->where('expires_at', '<=', $cutoff);
                    })
                    ->orWhereHas('batch', function ($batch) use ($cutoff): void {
                        $batch->whereIn('status', [UploadBatch::STATUS_EXPIRED, UploadBatch::STATUS_FAILED])
                            ->orWhere(function ($expired) use ($cutoff): void {
                                $expired->where('status', UploadBatch::STATUS_PENDING)
                                    ->where('expires_at', '<=', $cutoff);
                            });
                    });
            })
            ->orderBy('id')
            ->get();

        $reported = 0;
        $skipped = 0;

        foreach ($sessions as $session) {
            if ($session->status === UploadSession::STATUS_COMPLETED || $this->belongsToCompletedVideo($session->object_key)) {
                $skipped++;
                continue;
            }

            $exists = $uploads->exists($session->object_key);
            $state = $exists ? 'present' : 'missing';
            $this->line(sprintf('[%s] %s (session:%d)', $state, $session->object_key, $session->id));
            $reported++;
        }

        $this->info("Dry-run tamamlandı: {$reported} aday, {$skipped} atlandı. Hiçbir R2 nesnesi silinmedi.");

        return self::SUCCESS;
    }

    private function belongsToCompletedVideo(string $key): bool
    {
        return Video::query()
            ->where('video_path', $key)
            ->orWhere('thumbnail', $key)
            ->exists();
    }
}
