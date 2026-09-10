<?php

use App\Ingestion\Support\Cvss;

test('computes CVSS v3.1 base score and severity from a vector', function (string $vector, float $score, string $severity) {
    $cvss = Cvss::fromVector($vector);

    expect($cvss)->not->toBeNull();
    expect($cvss->version)->toBe('3.1');
    expect($cvss->vector)->toBe($vector);
    expect($cvss->baseScore)->toBe($score);
    expect($cvss->baseSeverity)->toBe($severity);
})->with([
    'critical, scope unchanged' => ['CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H', 9.8, 'Critical'],
    'high, confidentiality only' => ['CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:N/A:N', 7.5, 'High'],
    'medium, scope changed' => ['CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:L/A:N', 6.1, 'Medium'],
    'none' => ['CVSS:3.1/AV:N/AC:H/PR:H/UI:R/S:U/C:N/I:N/A:N', 0.0, 'None'],
]);

test('computes CVSS v3.0 scores and keeps the 3.0 version', function () {
    $cvss = Cvss::fromVector('CVSS:3.0/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H');

    expect($cvss->version)->toBe('3.0');
    expect($cvss->baseScore)->toBe(9.8);
    expect($cvss->baseSeverity)->toBe('Critical');
});

test('computes CVSS v2.0 base score from an unprefixed vector', function () {
    $cvss = Cvss::fromVector('AV:N/AC:L/Au:N/C:P/I:P/A:P');

    expect($cvss->version)->toBe('2.0');
    expect($cvss->baseScore)->toBe(7.5);
    expect($cvss->baseSeverity)->toBe('High');
});

test('accepts a CVSS:2.0 prefixed vector', function () {
    expect(Cvss::fromVector('CVSS:2.0/AV:N/AC:L/Au:N/C:N/I:N/A:P')->version)->toBe('2.0');
});

test('recognises a v4.0 vector but does not score it', function () {
    $cvss = Cvss::fromVector('CVSS:4.0/AV:N/AC:L/AT:N/PR:N/UI:N/VC:H/VI:H/VA:H/SC:N/SI:N/SA:N');

    expect($cvss->version)->toBe('4.0');
    expect($cvss->vector)->toContain('CVSS:4.0');
    expect($cvss->baseScore)->toBeNull();
    expect($cvss->baseSeverity)->toBeNull();
});

test('keeps version and vector but no score for an incomplete v3 vector', function () {
    $cvss = Cvss::fromVector('CVSS:3.1/AV:N/AC:L');

    expect($cvss->version)->toBe('3.1');
    expect($cvss->baseScore)->toBeNull();
    expect($cvss->baseSeverity)->toBeNull();
});

test('returns null for a string that is not a CVSS vector', function () {
    expect(Cvss::fromVector('HIGH'))->toBeNull();
    expect(Cvss::fromVector('not/a/vector'))->toBeNull();
});
