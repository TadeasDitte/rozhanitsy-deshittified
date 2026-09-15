<?php

namespace App\Ingestion\Support;

use InvalidArgumentException;

final readonly class Cpe23
{
    public function __construct(
        public string $part,
        public string $vendor,
        public string $product,
        public string $version,
        public string $update,
        public string $edition,
        public string $language,
        public string $swEdition,
        public string $targetSw,
        public string $targetHw,
        public string $other,
    ) {}

    public static function parse(string $criteria): self
    {
        $parts = preg_split('/(?<!\\\\):/', $criteria) ?: [];

        if (count($parts) < 13 || strtolower($parts[0]) !== 'cpe' || $parts[1] !== '2.3') {
            throw new InvalidArgumentException("Malformed CPE 2.3 string: [{$criteria}]");
        }

        $unescape = static fn (string $value): string => preg_replace('/\\\\(.)/', '$1', $value) ?? $value;

        return new self(
            part: $unescape($parts[2]),
            vendor: $unescape($parts[3]),
            product: $unescape($parts[4]),
            version: $unescape($parts[5]),
            update: $unescape($parts[6]),
            edition: $unescape($parts[7]),
            language: $unescape($parts[8]),
            swEdition: $unescape($parts[9]),
            targetSw: $unescape($parts[10]),
            targetHw: $unescape($parts[11]),
            other: $unescape($parts[12]),
        );
    }
}
