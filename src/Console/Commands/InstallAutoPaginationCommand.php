<?php

namespace Companue\AutoPaginate\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallAutoPaginationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-paginate:install 
                            {--base-controller : Update the base Controller.php to use PaginatesQueries trait}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install auto-pagination into your Laravel application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('base-controller')) {
            return $this->installIntoBaseController();
        }

        $this->info('Auto-Paginate Installation');
        $this->newLine();

        $choice = $this->choice(
            'How would you like to use auto-pagination?',
            [
                '1' => 'Publish AutoPaginatedController (Recommended - extends from it)',
                '2' => 'Update base Controller.php (Direct trait injection)',
            ],
            '1'
        );

        if ($choice === '1' || str_starts_with($choice, '1')) {
            return $this->publishController();
        } else {
            return $this->installIntoBaseController();
        }
    }

    /**
     * Publish the AutoPaginatedController
     *
     * @return int
     */
    protected function publishController()
    {
        $this->call('vendor:publish', [
            '--tag' => 'auto-paginate-controller',
            '--force' => true,
        ]);

        $this->newLine();
        $this->info('✓ AutoPaginatedController published successfully!');
        $this->newLine();
        $this->comment('Next steps:');
        $this->line('1. Make your API controllers extend AutoPaginatedController:');
        $this->line('   use App\Http\Controllers\API\AutoPaginatedController;');
        $this->line('   class YourController extends AutoPaginatedController { ... }');
        $this->newLine();
        $this->line('2. Use indexResponse() in your index methods:');
        $this->line('   return response()->json($this->indexResponse($query, YourResource::class, $request));');

        return Command::SUCCESS;
    }

    /**
     * Install pagination trait directly into base Controller.php
     *
     * @return int
     */
    protected function installIntoBaseController()
    {
        $controllerPath = app_path('Http/Controllers/Controller.php');

        if (!File::exists($controllerPath)) {
            $this->error("Controller.php not found at: {$controllerPath}");
            return Command::FAILURE;
        }

        $content = File::get($controllerPath);

        // Check if already installed
        if (str_contains($content, 'use Companue\AutoPaginate\Traits\PaginatesQueries')) {
            $this->warn('PaginatesQueries trait is already imported in Controller.php');
            
            if (!$this->confirm('Do you want to continue anyway?', false)) {
                return Command::SUCCESS;
            }
        }

        // Backup the original file
        $backupPath = app_path('Http/Controllers/Controller.php.backup');
        File::copy($controllerPath, $backupPath);
        $this->info("✓ Backup created at: {$backupPath}");

        // Parse and modify the file
        $modified = $this->injectTraitIntoController($content);

        if ($modified === false) {
            $this->error('Failed to modify Controller.php. The file structure may be unexpected.');
            $this->line('Please add the trait manually or use the AutoPaginatedController approach.');
            return Command::FAILURE;
        }

        // Write the modified content
        File::put($controllerPath, $modified);

        $this->newLine();
        $this->info('✓ Controller.php updated successfully!');
        $this->newLine();
        $this->comment('The PaginatesQueries trait has been added to your base Controller.');
        $this->comment('All controllers now have access to pagination methods:');
        $this->line('- indexResponse()');
        $this->line('- applyPagination()');
        $this->line('- paginatedResponse()');
        $this->line('- shouldPaginate()');
        $this->newLine();
        $this->line('Usage in your controllers:');
        $this->line('return response()->json($this->indexResponse($query, YourResource::class, $request));');

        return Command::SUCCESS;
    }

    /**
     * Inject the PaginatesQueries trait into Controller class
     *
     * @param string $content
     * @return string|false
     */
    protected function injectTraitIntoController(string $content)
    {
        // Add the use statement after other use statements
        $useStatement = "use Companue\\AutoPaginate\\Traits\\PaginatesQueries;";
        
        // Find the namespace and use statements section
        if (preg_match('/(namespace\s+[^;]+;\s*)((?:use\s+[^;]+;\s*)+)/s', $content, $matches)) {
            $beforeUses = $matches[1];
            $existingUses = $matches[2];
            
            // Check if already added
            if (str_contains($existingUses, $useStatement)) {
                // Already exists, continue to trait injection
            } else {
                // Add our use statement
                $content = str_replace(
                    $beforeUses . $existingUses,
                    $beforeUses . $existingUses . $useStatement . "\n",
                    $content
                );
            }
        } else {
            return false;
        }

        // Add the trait usage inside the class
        // Handle both single-line and multi-line trait declarations
        if (preg_match('/(class\s+Controller[^{]*\{\s*use\s+)([^;]+)(;)/s', $content, $matches)) {
            // Traits already exist on one or multiple lines
            $existingTraits = $matches[2];
            
            // Check if PaginatesQueries is already there
            if (str_contains($existingTraits, 'PaginatesQueries')) {
                return $content; // Already added
            }
            
            // Add our trait to the existing list
            $newTraits = $existingTraits . ', PaginatesQueries';
            $content = str_replace(
                $matches[1] . $matches[2] . $matches[3],
                $matches[1] . $newTraits . $matches[3],
                $content
            );
        } else {
            // No traits exist yet, add it
            if (preg_match('/(class\s+Controller[^{]*\{)(\s*)/', $content, $matches)) {
                $traitUsage = "\n    use PaginatesQueries;\n";
                $position = strpos($content, $matches[0]) + strlen($matches[0]);
                $content = substr_replace($content, $traitUsage, $position, 0);
            } else {
                return false;
            }
        }

        return $content;
    }
}
