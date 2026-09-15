<?php

namespace App\Ingestion\Parsers;

use App\Ingestion\Support\Cpe23;
use App\Ingestion\VersionRangeData;

/**
 * Expands NVD `cve.configurations` (CPE match nodes) into version ranges.
 *
 * v1 flattens `configurations[].nodes[].cpeMatch[]` and ignores node-level
 * `operator` (AND/OR) and `negate` beyond deriving `plugs_into` from an AND
 * node's non-vulnerable sibling.
 */
final class NVDRangeParser implements RangeParser
{
    public function parse(array $rawRanges): array
    {
        $ranges = [];

        foreach ($rawRanges as $configuration) {
            foreach ($configuration['nodes'] ?? [] as $node) {
                $plugsInto = $this->resolvePlugsInto($node);

                foreach ($node['cpeMatch'] ?? [] as $match) {
                    if (($match['vulnerable'] ?? false) !== true) {
                        continue;
                    }

                    $range = $this->buildRange($match, $plugsInto);

                    if ($range !== null) {
                        $ranges[] = $range;
                    }
                }
            }
        }

        return $ranges;
    }

    /**
     * @param  array<string, mixed>  $match
     */
    private function buildRange(array $match, ?string $plugsInto): ?VersionRangeData
    {
        $criteria = $match['criteria'] ?? null;

        if (! is_string($criteria)) {
            return null;
        }

        $cpe = Cpe23::parse($criteria);

        $startIncl = $this->bound($match['versionStartIncluding'] ?? null);
        $startExcl = $this->bound($match['versionStartExcluding'] ?? null);
        $endIncl = $this->bound($match['versionEndIncluding'] ?? null);
        $endExcl = $this->bound($match['versionEndExcluding'] ?? null);

        $hasBound = $startIncl !== null || $startExcl !== null || $endIncl !== null || $endExcl !== null;

        if (! $hasBound && $this->isConcrete($cpe->version)) {
            $startIncl = $cpe->version;
            $endIncl = $cpe->version;
        }

        return new VersionRangeData(
            type: $this->mapPart($cpe->part),
            ecosystem: null,
            vendor: $this->attribute($cpe->vendor),
            product: $this->attribute($cpe->product),
            versionInclStart: $startIncl,
            versionExclStart: $startExcl,
            versionInclEnd: $endIncl,
            versionExclEnd: $endExcl,
            plugsInto: $plugsInto ?? $this->attribute($cpe->targetSw),
            raw: $criteria,
        );
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function resolvePlugsInto(array $node): ?string
    {
        if (($node['operator'] ?? null) !== 'AND') {
            return null;
        }

        foreach ($node['cpeMatch'] ?? [] as $match) {
            if (($match['vulnerable'] ?? false) === true) {
                continue;
            }

            $criteria = $match['criteria'] ?? null;

            if (! is_string($criteria)) {
                continue;
            }

            $product = $this->attribute(Cpe23::parse($criteria)->product);

            if ($product !== null) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return 'a'|'h'|'o'|'u'
     */
    private function mapPart(string $part): string
    {
        return match ($part) {
            'a', 'h', 'o' => $part,
            default => 'u',
        };
    }

    private function attribute(string $value): ?string
    {
        return ($value === '*' || $value === '-' || $value === '') ? null : $value;
    }

    private function bound(mixed $value): ?string
    {
        return ($value === null || $value === '') ? null : (string) $value;
    }

    private function isConcrete(string $version): bool
    {
        return $version !== '*' && $version !== '-' && $version !== '';
    }
}
