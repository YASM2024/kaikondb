<?php

namespace Kaikon2\Kaikondb\Database\Seeders;

use Illuminate\Database\Seeder;

class KaikonSeeder extends Seeder
{
    public function run()
    {
        $this->call(KaikonRoleSeeder::class);
        $this->call(KaikonOrderSeeder::class);
        $this->call(KaikonFamilySeeder::class);
        $this->call(KaikonSpeciesSeeder::class);
        $this->call(KaikonJournalSeeder::class);
        $this->call(KaikonMunicipalitySeeder::class);
        $this->call(KaikonExpandedPageSeeder::class);
    }
}

