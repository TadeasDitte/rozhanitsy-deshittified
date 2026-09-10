<?php

namespace App\Ingestion;

final readonly class VersionRangeData
{
    /**
     * @param  'a'|'h'|'o'|'u'  $type  CPE part: application / hardware / os / unknown
     */
    public function __construct(
        public string $type,
        public ?string $ecosystem,
        public ?string $vendor,
        public ?string $product,
        public ?string $versionInclStart,
        public ?string $versionExclStart,
        public ?string $versionInclEnd,
        public ?string $versionExclEnd,
        public ?string $plugsInto,
        public ?string $raw,
    ) {}
}
