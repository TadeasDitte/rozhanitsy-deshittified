<?php

namespace App\Ingestion\Parsers;

use App\Ingestion\VersionRangeData;

interface RangeParser
{
    /**
     * Expand one ParsedRecord::raw_ranges value into normalized version ranges.
     *
     * @param  array<int, array<string, mixed>>  $rawRanges
     * @return list<VersionRangeData> empty means "nothing to expand", not a failure
     */
    public function parse(array $rawRanges): array;
}
