<?php

namespace App\Ingestion;

use App\Ingestion\Parsers\SourceRecordParser;
use App\Models\IngestRecord;
use App\Models\ParsedRecord;
use App\Models\Source;
use Throwable;

final class RecordParsingRunner
{
    public function __construct(private readonly SourceRecordParser $parser) {}

    public function run(Source $source): void
    {
        IngestRecord::query()
            ->where('source_id', $source->id)
            ->where('processing_status', 'pending')
            ->chunkById(500, function ($records) {
                foreach ($records as $record) {
                    $this->processOne($record);
                }
            });
    }

    private function processOne(IngestRecord $ingestRecord): void
    {
        try {
            $parsed = $this->parser->parseOne($ingestRecord->raw_payload);

            if ($parsed === null) {
                $ingestRecord->update(['processing_status' => 'skipped', 'processed_at' => now()]);

                return;
            }

            ParsedRecord::updateOrCreate(
                ['ingest_record_id' => $ingestRecord->id],
                [
                    'source_id' => $ingestRecord->source_id,
                    'external_id' => $parsed->externalId,
                    'aliases' => $parsed->aliases,
                    'cvss_score' => $parsed->cvssScore,
                    'cvss_vector' => $parsed->cvssVector,
                    'cvss_version' => $parsed->cvssVersion,
                    'cvss_severity' => $parsed->cvssSeverity,
                    'description' => $parsed->description,
                    'published_at' => $parsed->publishedAt,
                    'last_modified_at' => $parsed->lastModifiedAt,
                    'weaknesses' => $parsed->weaknesses,
                    'references' => $parsed->references,
                    'status' => $parsed->status,
                    'known_exploited' => $parsed->knownExploited,
                    'raw_ranges' => $parsed->rawRanges,
                    'resolved_at' => null,
                ]
            );

            $ingestRecord->update(['processing_status' => 'processed', 'processed_at' => now()]);
        } catch (Throwable $e) {
            $ingestRecord->update([
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage(),
                'processed_at' => now(),
            ]);
        }
    }
}