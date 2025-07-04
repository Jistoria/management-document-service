<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $this->createService($name);
    }

    protected function createService($name)
    {
        $parts = explode('/', $name);
        $useNamespace = '';
        if (count($parts) > 1) {
            $directory = $parts[0];
            $className = ucwords($parts[1]);
            $useNamespace = '\\' . $directory;
            $path = app_path('Services/' . $directory);
        } else {
            $directory = '';
            $className = ucwords($name);
            $path = app_path('Services');
        }

        if (File::exists($path . '/' . $className . '.php')) {
            $this->error('Service already exists!');
            return;
        }

        $stub = File::get(app_path('Console/Commands/stubs/service.stub'));
        $stub = str_replace('{{class}}', $className, $stub);
        $stub = str_replace('{{namespace}}', $useNamespace, $stub);
        // Ensure the directory exists
        File::ensureDirectoryExists($path);

        $filePath = $path . '/' . $className . '.php';

        // Create the file with the content
        File::put($filePath, $stub);

        $this->info('Service created successfully.');
    }
}
