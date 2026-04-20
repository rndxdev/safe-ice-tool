<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ReverseGeoCodeService
{
    public function usCensus(float $lat, float $lng): ?array
    {
        $key = 'revgeo:census:' . round($lat, 5) . ':' . round($lng, 5);

        return Cache::remember($key, now()->addDays(180), fn () => $this->fetchUsCensus($lat, $lng));
    }

    private function fetchUsCensus(float $lat, float $lng): ?array
    {
        $resp = Http::timeout(8)->get('https://geocoding.geo.census.gov/geocoder/geographies/coordinates', [
            'x' => $lng,
            'y' => $lat,
            'benchmark' => 'Public_AR_Current',
            'vintage' => 'Current_Current',
            'format' => 'json',
        ]);

        if (!$resp->ok()) {
            return null;
        }

        $geos = $resp->json()['result']['geographies'] ?? null;
        if (!$geos) {
            return null;
        }

        $stateName = $geos['States'][0]['NAME'] ?? null;
        $countyName = $geos['Counties'][0]['NAME'] ?? null;

        if (!$stateName || !$countyName) {
            return null;
        }

        return [
            'state' => $stateName,
            'county' => $countyName,
        ];
    }
}
