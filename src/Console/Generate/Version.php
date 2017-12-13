<?php

namespace Core\Console\Generate;

use Illuminate\Console\Command;

class Version extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:version';
    
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;
    
    /**
     * @var Boolean
     */
    protected $generate_all;
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A tool for helping you generate random postfix for your assets versioning.';
    
    /**
     * Create a new command instance.
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
        $path = base_path('.env');

        if (file_exists($path)) {
            $env_content = str_replace(
                'ASSET_VERSION=' . $this->laravel['config']['app.asset_version'], 'ASSET_VERSION=' . current_time('YmdHi'), file_get_contents($path)
            );
            
            file_put_contents($path, $env_content);
        }
        
        $this->call('clear-compiled');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        
        $this->comment(PHP_EOL . "Application is ready to change the world!" . PHP_EOL);
    }
}