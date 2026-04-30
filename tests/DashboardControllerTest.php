<?php

use Illuminate\Http\Request;
use SimpleStatsIo\StatamicAddon\Http\Controllers\DashboardController;

function callFiltersFromRequest(array $query): array
{
    $controller = new class extends DashboardController
    {
        public function __construct() {}

        public function exposeFilters(Request $request): array
        {
            return $this->filtersFromRequest($request);
        }
    };

    return $controller->exposeFilters(Request::create('/', 'GET', $query));
}

it('returns default filters when nothing is set', function () {
    expect(callFiltersFromRequest([]))->toBe([
        'preset' => 'last_7_days',
    ]);
});

it('forwards preset and comparison', function () {
    expect(callFiltersFromRequest([
        'preset' => 'last_30_days',
        'comparison' => 'period',
    ]))->toBe([
        'preset' => 'last_30_days',
        'comparison' => 'period',
    ]);
});

it('drops the disabled "0" comparison value', function () {
    expect(callFiltersFromRequest([
        'preset' => 'today',
        'comparison' => '0',
    ]))->toBe([
        'preset' => 'today',
    ]);
});

it('forwards a single drilldown filter', function () {
    expect(callFiltersFromRequest([
        'preset' => 'last_7_days',
        'track_referer' => 54,
    ]))->toBe([
        'preset' => 'last_7_days',
        'track_referer' => 54,
    ]);
});

it('forwards multiple stacked drilldown filters', function () {
    expect(callFiltersFromRequest([
        'preset' => 'last_7_days',
        'track_referer' => 54,
        'page_entry' => 12,
        'location_country' => 7,
    ]))->toBe([
        'preset' => 'last_7_days',
        'track_referer' => 54,
        'location_country' => 7,
        'page_entry' => 12,
    ]);
});

it('ignores unknown filter keys', function () {
    expect(callFiltersFromRequest([
        'preset' => 'last_7_days',
        'not_a_filter' => 99,
    ]))->toBe([
        'preset' => 'last_7_days',
    ]);
});

it('ignores empty drilldown values', function () {
    expect(callFiltersFromRequest([
        'preset' => 'last_7_days',
        'track_referer' => '',
        'page_entry' => null,
    ]))->toBe([
        'preset' => 'last_7_days',
    ]);
});
