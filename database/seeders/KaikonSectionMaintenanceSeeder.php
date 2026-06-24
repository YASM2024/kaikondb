<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Database\Seeder;
use Kaikon2\Kaikondb\Models\SectionMaintenance;
use Kaikon2\Kaikondb\Support\SectionMaintenanceGate;

class KaikonSectionMaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SectionMaintenanceGate::SECTIONS as $section) {
            SectionMaintenance::query()->firstOrCreate(
                ['section' => $section],
                ['enabled' => false]
            );
        }

        SectionMaintenanceGate::clearCache();
    }
}
