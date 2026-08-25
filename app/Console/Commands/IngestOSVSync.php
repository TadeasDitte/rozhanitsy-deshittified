<?php

namespace App\Console\Commands;

use App\Models\Source;
use App\Models\SyncState;
use App\Services\Ingestion\IngestRecordWriter;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('ingest:osv-sync {--full : Ignore sync_states and reprocess the entire modified_id.csv}')]
#[Description('Incrementally sync OSV vulnerabilities via modified_id.csv, without re-downloading all.zip')]
class IngestOsvSync extends Command
{
    public function __construct(private IngestRecordWriter $writer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $source = Source::where('slug', 'osv')->firstOrFail();
        $baseUrl = rtrim($source->ingest_base_url, '/');
        $syncState = SyncState::firstOrCreate(['source_id' => $source->id]);
        $full = (bool) $this->option('full');

        $lastCursor = $full ? null : ($syncState->cursor['last_modified'] ?? null);
        $lastCursorTime = $lastCursor ? Carbon::parse($lastCursor) : null;

        if (!$lastCursorTime) {
            $this->warn('No cursor found — this will process the ENTIRE modified_id.csv (one HTTP request per record). Are you sure? If not, Ctrl+C now and set a cursor first.');
        }

        $csvUrl = "{$baseUrl}/modified_id.csv";
        $this->info("Streaming {$csvUrl}" . ($lastCursorTime ? " (since {$lastCursorTime})" : ' (full)'));

        $csvPath = storage_path('app/tmp/osv-modified_id.csv');
        @mkdir(dirname($csvPath), recursive: true);

        Http::timeout(120)
            ->withOptions(['sink' => $csvPath])
            ->get($csvUrl)
            ->throw();

        $totalLines = 0;
        $handle = fopen($csvPath, 'r');
        while (fgets($handle) !== false) {
            $totalLines++;
        }
        rewind($handle);

        $bar = $this->output->createProgressBar($totalLines);
        $newestSeen = null;
        $processed = 0;
        $skipped = 0;

        while (($line = fgets($handle)) !== false) {
            $bar->advance();
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            [$timestamp, $path] = explode(',', $line, 2);
            $rowTime = Carbon::parse($timestamp);

            $newestSeen ??= $timestamp;

            if ($lastCursorTime && $rowTime->lessThanOrEqualTo($lastCursorTime)) {
                break;
            }

            [$ecosystem, $id] = explode('/', $path, 2);
            $recordUrl = "{$baseUrl}/{$ecosystem}/{$id}.json";

            $response = Http::get($recordUrl);
            if ($response->status() === 404) {
                $skipped++;
                continue;
            }
            $response->throw();

            $payload = $response->json();
            if (isset($payload['id'])) {
                $this->writer->upsert($source->id, $payload['id'], $payload);
                $processed++;
            }
        }
        $bar->finish();
        $this->newLine();
        fclose($handle);
        unlink($csvPath);

        if ($newestSeen) {
            $syncState->update([
                'cursor' => ['last_modified' => $newestSeen],
                'last_synced_at' => now(),
            ]);
        }

        $this->info("Done, {$processed} records updated, {$skipped} skipped (not found).");
        return self::SUCCESS;
    }
}