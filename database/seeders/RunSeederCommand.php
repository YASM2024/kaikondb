<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Console\Command;

class RunSeederCommand extends Command
{
    protected $signature = 'kaikondb:seed';
    protected $description = 'Run Kaikondb package seeders';

    public function handle()
    {
        $this->call(\Kaikon2\KaikondbSeeders\KaikonSeeder::class);
    }
}
