<?php

namespace SimpleStatsIo\StatamicAddon\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SimpleStatsIo\StatamicAddon\SimplestatsApiClient;

class DashboardController extends Controller
{
    /**
     * Drilldown filter keys forwarded to the SimpleStats API. Mirrors the SaaS
     * StatsFilterRequest and lets users stack multiple filters at once
     * (e.g. track_referer=54 AND page_entry=12).
     */
    public const DRILLDOWN_KEYS = [
        'track_referer',
        'track_source',
        'track_medium',
        'track_campaign',
        'track_term',
        'track_content',
        'location_country',
        'location_region',
        'location_city',
        'device_type',
        'device_platform',
        'device_browser',
        'page_entry',
        'custom_event_name',
    ];

    public function __construct(protected SimplestatsApiClient $client) {}

    public function index()
    {
        return view('simplestats::dashboard');
    }

    public function all(Request $request): JsonResponse
    {
        return response()->json(
            $this->client->getAll($this->filtersFromRequest($request))
        );
    }

    public function grouped(Request $request, string $type): JsonResponse
    {
        $filters = $this->filtersFromRequest($request);

        $sort = $request->string('sort')->toString();
        if ($sort !== '') {
            $filters['stats_sort'] = $sort;
        }

        return response()->json(
            $this->client->getGroupedStats($type, $filters)
        );
    }

    protected function filtersFromRequest(Request $request): array
    {
        $preset = $request->string('preset')->toString() ?: 'last_7_days';
        $comparison = $request->string('comparison')->toString();

        $filters = array_filter([
            'preset' => $preset,
            'comparison' => $comparison !== '' && $comparison !== '0' ? $comparison : null,
        ]);

        foreach (self::DRILLDOWN_KEYS as $key) {
            $value = $request->input($key);

            if ($value === null || $value === '') {
                continue;
            }

            $filters[$key] = $value;
        }

        return $filters;
    }
}
