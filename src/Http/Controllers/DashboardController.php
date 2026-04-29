<?php

namespace SimpleStatsIo\StatamicAddon\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SimpleStatsIo\StatamicAddon\SimplestatsApiClient;

class DashboardController extends Controller
{
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

        return array_filter([
            'preset' => $preset,
            'comparison' => $comparison !== '' && $comparison !== '0' ? $comparison : null,
        ]);
    }
}
