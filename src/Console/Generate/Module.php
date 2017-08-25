<?php

namespace Core\Console\Generate;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Module extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:module {module-name}';
    
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
    protected $description = 'A wizard for helping you generate new module quickly and instantly.';
    
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
        $this->files = new Filesystem();
        
        $this->info('');
        $this->warn('The folder or file exists will be replaced!');
        if($this->ask('Are you sure? (y|n)') == 'y'){
            $this->warn('Follow the question and answer with y or n!');
            $this->generate_all = $this->ask('Generate all (controller, route, view, provider)?') == 'y';
    
            $this->generateController();
            $this->generateRoute();
            $this->generateProvider();
            $this->generateView();
        }
        
        $this->info('');
        $this->info('Done, see ya!');
    }
    
    /**
     * Generate controller template from its stub
     *
     * @return $this
     */
    protected function generateController()
    {
        if ($this->generate_all || $this->ask('Generate controller?') == 'y') {
            $stub     = $this->processStub(__DIR__ . '/Stubs/controller.stub');
            $file_dir = base_path() . '/app/' . $this->argument('module-name') . '/Controllers/' . $this->argument('module-name') . 'Controller.php';
            
            $this->makeDirectory($file_dir);
            $this->files->put($file_dir, $stub);
            
            $this->warn('Controller has been created!');
            
            return $this;
        }
    }
    
    private function processStub($stubFiles)
    {
        $stub = $this->files->get($stubFiles);
        $stub = $this->replaceStub($stub);
        $stub = $this->replaceStubLc($stub);
        
        return $stub;
    }
    
    private function replaceStub($stub)
    {
        return str_replace(
            "{{MODULE_NAME}}", $this->argument('module-name'), $stub
        );
    }
    
    private function replaceStubLc($stub)
    {
        return str_replace(
            "{{MODULE_NAME_LC}}", strtolower($this->argument('module-name')), $stub
        );
    }
    
    /**
     * Build the directory for the class if necessary.
     *
     * @param  string $path
     * @return void
     */
    private function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true, true);
        }
    }
    
    /**
     * Generate route template from its stub
     *
     * @return $this
     */
    protected function generateRoute()
    {
        if ($this->generate_all || $this->ask('Generate route?') == 'y') {
            $stub     = $this->processStub(__DIR__ . '/Stubs/routes.stub');
            $file_dir = base_path() . '/app/' . $this->argument('module-name') . '/routes.php';
            
            $this->makeDirectory($file_dir);
            $this->files->put($file_dir, $stub);
            
            $this->warn('Route has been created!');
            
            return $this;
        }
    }
    
    /**
     * Generate provider template from its stub
     *
     * @return $this
     */
    protected function generateProvider()
    {
        if ($this->generate_all || $this->ask('Generate provider?') == 'y') {
            $stub     = $this->processStub(__DIR__ . '/Stubs/provider.stub');
            $file_dir = base_path() . '/app/' . $this->argument('module-name') . '/Providers/' . $this->argument('module-name') . 'ServiceProvider.php';
            
            $this->makeDirectory($file_dir);
            $this->files->put($file_dir, $stub);
            
            $this->warn('Provider has been created!');
            
            return $this;
        }
    }
    
    /**
     * Generate view template from its stub
     *
     * @return $this
     */
    protected function generateView()
    {
        if ($this->generate_all || $this->ask('Generate view?') == 'y') {
            $stub     = $this->processStub(__DIR__ . '/Stubs/view.stub');
            $file_dir = base_path() . '/app/' . $this->argument('module-name') . '/Resources/views/index.blade.php';
            
            $this->makeDirectory($file_dir);
            $this->files->put($file_dir, $stub);
            
            $this->warn('View has been created!');
            
            return $this;
        }
    }
}