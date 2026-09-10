<?php

namespace App\Console\Commands;

use App\Ingestion\Parsers\SourceRecordParser;
use App\Ingestion\RecordParsingRunner;
use App\Models\IngestRecord;
use App\Models\Source;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('parse:l1 {source? : source slug, defaults to all} {--retry-failed : requeue failed records before parsing} {--rerun : requeue already-processed records so they are parsed again}')]
#[Description('Run Layer 1 parsing against pending ingest_records')]
final class ParseL1 extends Command
{
    public function handle(): int
    {
        $sources = $this->argument('source')
            ? Source::where('slug', $this->argument('source'))->get()
            : Source::all();

        foreach ($sources as $source) {
            $parser = $this->resolveParser($source->slug);

            if ($parser === null) {
                $this->warn("No parser class found for slug [{$source->slug}], skipping");

                continue;
            }

            if ($this->option('retry-failed')) {
                $requeued = IngestRecord::where('source_id', $source->id)
                    ->where('processing_status', 'failed')
                    ->update(['processing_status' => 'pending', 'processing_error' => null]);

                if ($requeued > 0) {
                    $this->info("Requeued {$requeued} failed records for {$source->slug}");
                }
            }

            if ($this->option('rerun')) {
                $requeued = IngestRecord::where('source_id', $source->id)
                    ->whereIn('processing_status', ['processed', 'skipped'])
                    ->update(['processing_status' => 'pending', 'processed_at' => null, 'processing_error' => null]);

                if ($requeued > 0) {
                    $this->info("Requeued {$requeued} already-processed records for {$source->slug}");
                }
            }

            $pending = IngestRecord::where('source_id', $source->id)
                ->where('processing_status', 'pending')
                ->count();

            if ($pending === 0) {
                $this->info("Nothing pending for {$source->slug}");

                continue;
            }

            $this->info("Parsing {$pending} pending records for {$source->slug}...");
            $bar = $this->output->createProgressBar($pending);
            $bar->start();

            (new RecordParsingRunner($parser))->run($source, fn () => $bar->advance());

            $bar->finish();
            $this->newLine();
        }

        return self::SUCCESS;
    }

    private function resolveParser(?string $slug): ?SourceRecordParser
    {
        if ($slug === null) {
            return null;
        }

        $class = 'App\\Ingestion\\Parsers\\'.strtoupper($slug).'RecordParser';

        if (! class_exists($class)) {
            return null;
        }

        return app($class);
    }
}
