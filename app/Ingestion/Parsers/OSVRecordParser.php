<?php

namespace App\Ingestion\Parsers;

use App\Ingestion\ParsedRecordData;
use Carbon\Carbon;

final class OSVRecordParser implements SourceRecordParser
{
    public function parseOne(array $rawPayload): ?ParsedRecordData
    {
        if (empty($rawPayload['id'])) {
            return null;
        }

        [$vector, $version, $severity] = $this->extractCvss($rawPayload['severity'] ?? []);

        return new ParsedRecordData(
            externalId: $rawPayload['id'],
            aliases: $rawPayload['aliases'] ?? [],
            cvssScore: null,
            cvssVector: $vector,
            cvssVersion: $version,
            cvssSeverity: $severity,
            description: $rawPayload['details'] ?? $rawPayload['summary'] ?? null,
            publishedAt: $this->parseDate($rawPayload['published'] ?? null),
            lastModifiedAt: $this->parseDate($rawPayload['modified'] ?? null),
            weaknesses: $rawPayload['database_specific']['cwe_ids'] ?? [],
            references: $this->extractReferences($rawPayload['references'] ?? []),
            status: isset($rawPayload['withdrawn']) ? 'withdrawn' : null,
            knownExploited: false,
            rawRanges: $rawPayload['affected'] ?? [],
        );
    }

    private function extractCvss(array $severityBlocks): array
    {
        foreach ($severityBlocks as $block) {
            $type = $block['type'] ?? null;

            if ($type === 'CVSS_V3' || $type === 'CVSS_V2') {
                return [$block['score'] ?? null, $type === 'CVSS_V3' ? '3.x' : '2.0', null];
            }
        }

        return [null, null, null];
    }

    private function extractReferences(array $references): array
    {
        return array_map(
            fn (array $ref) => ['url' => $ref['url'] ?? '', 'tags' => array_filter([$ref['type'] ?? null])],
            $references
        );
    }

    private function parseDate(?string $value): ?Carbon
    {
        return $value !== null ? Carbon::parse($value) : null;
    }
}