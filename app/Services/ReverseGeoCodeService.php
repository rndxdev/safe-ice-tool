<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ReverseGeoCodeService
{
    public function usCensus(float $lat, float $lng): ?array
    {
        $key = 'revgeo:census:' . round($lat, 5) . ':' . round($lng, 5);

        return Cache::remember($key, now()->addDays(180), function () use ($lat, $lng) {
            $url = 'https://geocoding.geo.census.gov/geocoder/geographies/coordinates';

            $resp = Http::timeout(8)->get($url, [
                'x' => $lng,
                'y' => $lat,
                'benchmark' => 'Public_AR_Current',
                'vintage' => 'Current_Current',
                'format' => 'json',
            ]);

            if (!$resp->ok()) {
                return null;
            }

            $json = $resp->json();

            $geos = $json['result']['geographies'] ?? null;
            if (!$geos) {
                return null;
            }

            $states = $geos['States'] ?? [];
            $counties = $geos['Counties'] ?? [];

            $stateName = $states[0]['NAME'] ?? null;
            $countyName = $counties[0]['NAME'] ?? null;

            if (!$stateName || !$countyName) {
                return null;
            }

            return [
                'state' => $stateName,
                'county' => $countyName,
            ];
        });
    }
}
