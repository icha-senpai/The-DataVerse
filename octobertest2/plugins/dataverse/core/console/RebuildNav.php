<?php namespace Dataverse\Core\Console;

use Illuminate\Console\Command;
use Dataverse\Core\Classes\NavBuilder;

class RebuildNav extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'dataverse:rebuild-nav';

    /**
     * The console command description.
     */
    protected $description = 'Rebuilds navigation.yaml from CMS pages.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = NavBuilder::rebuild();
        $this->info("Navigation rebuilt with {$count} pages.");
    }
}
