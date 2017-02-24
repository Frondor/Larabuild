<?php

namespace Frondor\Larabuild\Commands;

use Illuminate\Console\Command;
use Frondor\Larabuild\Drivers\SharedWebhostingDriver;

class Build extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabuild 
                            {driver? : Handler to define the type of build} 
                            {--ftp-upload : Whether or not to upload the project via ftp after building} 
                            {--o|optimize : Optimize composer autoload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the build proccess with a specific driver';

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
     * @return mixed
     */
    public function handle()
    {
        $driver = $this->argument('driver');

        if (!$driver) {
            $driver = $this->choice('Which type of build are you looking for?', ['shared', 'cloud']);
        }

        switch ($driver) {
            case 'shared':
                $driver = new SharedWebhostingDriver($this);
                $driver->run();
                break; 
            case 'cloud':
                $this->error('Not implemented');
                break;
            default:
                $this->error('Please, specify a valid driver');
                break;
        }
    }
}
