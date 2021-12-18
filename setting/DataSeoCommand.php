<?php

namespace App\Console\Commands;

use Beatom\DataSeo\DataSeo;
use Illuminate\Console\Command;

class DataSeoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataseo:domain_intersection {targets} {exclude_targets?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This endpoint will provide you with the list of domains pointing to the specified websites.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domains = $this->argument('targets');
        $excludeTargets = $this->argument('exclude_targets');
        $dataSeo = new DataSeo($domains, $excludeTargets);
        $dataSeo->getDataSeo();
        return 0;
    }
}
