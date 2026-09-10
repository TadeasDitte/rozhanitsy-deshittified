<?php

use App\Ingestion\Support\Cpe23;

test('parses every component of a well-formed cpe 2.3 string', function () {
    $cpe = Cpe23::parse('cpe:2.3:a:wordpress:wordpress:6.9:beta:edi:en:se:drupal:x64:oth');

    expect($cpe->part)->toBe('a')
        ->and($cpe->vendor)->toBe('wordpress')
        ->and($cpe->product)->toBe('wordpress')
        ->and($cpe->version)->toBe('6.9')
        ->and($cpe->update)->toBe('beta')
        ->and($cpe->edition)->toBe('edi')
        ->and($cpe->language)->toBe('en')
        ->and($cpe->swEdition)->toBe('se')
        ->and($cpe->targetSw)->toBe('drupal')
        ->and($cpe->targetHw)->toBe('x64')
        ->and($cpe->other)->toBe('oth');
});

test('unescapes escaped colons and backslashes in components', function () {
    $cpe = Cpe23::parse('cpe:2.3:a:foo\:bar:baz\\\\qux:1.0:*:*:*:*:*:*:*');

    expect($cpe->vendor)->toBe('foo:bar')
        ->and($cpe->product)->toBe('baz\\qux');
});

test('preserves the wildcard and NA markers verbatim', function () {
    $cpe = Cpe23::parse('cpe:2.3:a:vendor:product:*:-:*:*:*:*:*:*');

    expect($cpe->version)->toBe('*')
        ->and($cpe->update)->toBe('-');
});

test('rejects a string that is not a cpe 2.3 formatted string', function () {
    Cpe23::parse('cpe:/a:vendor:product');
})->throws(InvalidArgumentException::class);

test('rejects a string with too few components', function () {
    Cpe23::parse('cpe:2.3:a:vendor:product');
})->throws(InvalidArgumentException::class);
