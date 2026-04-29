<?php

use SimpleStatsIo\StatamicAddon\SimplestatsApiClient;

it('registers the api client as a singleton', function () {
    $first = app(SimplestatsApiClient::class);
    $second = app(SimplestatsApiClient::class);

    expect($first)->toBeInstanceOf(SimplestatsApiClient::class)
        ->and($first)->toBe($second);
});

it('merges the default config', function () {
    expect(config('simplestats.api_url'))->toBe('https://simplestats.io/api/v1')
        ->and(config('simplestats.cache_ttl'))->toBe(60);
});
