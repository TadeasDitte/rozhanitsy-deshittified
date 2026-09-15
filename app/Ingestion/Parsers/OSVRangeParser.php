<?php

namespace App\Ingestion\Parsers;

use App\Ingestion\VersionRangeData;

/**
 * Expands OSV `affected[]` entries (PURL packages + version events) into version ranges.
 *
 * v1 handles `SEMVER` and `ECOSYSTEM` ranges; `GIT` ranges and `affected`
 * entries that carry only a `versions[]` list (no `ranges`) are skipped.
 */
final class OSVRangeParser implements RangeParser
{
    public function parse(array $rawRanges): array
    {
        $ranges = [];

        foreach ($rawRanges as $affected) {
            $package = $affected['package'] ?? [];
            $ecosystem = $this->stringOrNull($package['ecosystem'] ?? null);
            $product = $this->stringOrNull($package['name'] ?? null);
            $purl = $this->stringOrNull($package['purl'] ?? null);
            $vendor = $purl !== null ? $this->vendorFromPurl($purl) : null;

            foreach ($affected['ranges'] ?? [] as $range) {
                $rangeType = $range['type'] ?? null;

                if ($rangeType !== 'SEMVER' && $rangeType !== 'ECOSYSTEM') {
                    continue;
                }

                $raw = trim(($purl ?? $product ?? '').' '.(json_encode($range, JSON_UNESCAPED_SLASHES) ?: ''));

                foreach ($this->intervals($range['events'] ?? []) as $interval) {
                    $ranges[] = new VersionRangeData(
                        type: 'a',
                        ecosystem: $ecosystem,
                        vendor: $vendor,
                        product: $product,
                        versionInclStart: $interval['introduced'],
                        versionExclStart: null,
                        versionInclEnd: $interval['last_affected'],
                        versionExclEnd: $interval['fixed'],
                        plugsInto: null,
                        raw: $raw !== '' ? $raw : null,
                    );
                }
            }
        }

        return $ranges;
    }

    /**
     * Collapse an OSV `events` list into closed/open intervals.
     *
     * @param  array<int, array<string, mixed>>  $events
     * @return list<array{introduced: ?string, fixed: ?string, last_affected: ?string}>
     */
    private function intervals(array $events): array
    {
        $intervals = [];
        $open = null;

        foreach ($events as $event) {
            if (array_key_exists('introduced', $event)) {
                if ($open !== null) {
                    $intervals[] = $open;
                }

                $introduced = (string) $event['introduced'];

                $open = [
                    'introduced' => $introduced === '0' ? null : $introduced,
                    'fixed' => null,
                    'last_affected' => null,
                ];

                continue;
            }

            if ($open === null) {
                continue;
            }

            if (array_key_exists('fixed', $event)) {
                $open['fixed'] = (string) $event['fixed'];
                $intervals[] = $open;
                $open = null;

                continue;
            }

            if (array_key_exists('last_affected', $event)) {
                $open['last_affected'] = (string) $event['last_affected'];
                $intervals[] = $open;
                $open = null;
            }
        }

        if ($open !== null) {
            $intervals[] = $open;
        }

        return $intervals;
    }

    private function vendorFromPurl(string $purl): ?string
    {
        $body = preg_replace('#^pkg:#', '', $purl) ?? $purl;
        $body = preg_split('/[@?#]/', $body)[0] ?? $body;
        $segments = array_values(array_filter(explode('/', $body), static fn (string $s): bool => $s !== ''));

        if (count($segments) < 3) {
            return null;
        }

        $namespace = implode('/', array_slice($segments, 1, count($segments) - 2));

        return rawurldecode($namespace);
    }

    private function stringOrNull(mixed $value): ?string
    {
        return (is_string($value) && $value !== '') ? $value : null;
    }
}
