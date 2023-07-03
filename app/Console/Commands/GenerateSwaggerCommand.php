<?php

namespace App\Console\Commands;

use \App\Services\Generator;
use Illuminate\Console\Command;

class GenerateSwaggerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'swagger:generate {--ver=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate docs';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Regenerating docs');
        $version = $this->option('ver');
        Generator::generateDocs($version);
    }
}
