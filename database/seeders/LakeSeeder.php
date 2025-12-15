<?php

namespace Database\Seeders;

use App\Models\Lake;
use Illuminate\Database\Seeder;

class LakeSeeder extends Seeder
{
    public function run(): void
    {
        Lake::firstOrCreate(
            ['slug' => 'test-lake'],
            [
                'name' => 'Test Lake',
                'lat' => 46.5432100,
                'lng' => -87.3950000,
                'region' => 'Test County, MI',
                'is_active' => true,
            ]
        );

        Lake::firstOrCreate(
  ['slug' => 'witch-lake-republic-mi'],
  [
    'name'   => 'Witch Lake',
    'lat'    => 46.2755,
    'lng'    => -88.0190,
    'region' => 'Republic Township, Marquette County, MI',
    'is_active' => true,
  ]
);
    }
}
