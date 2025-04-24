<?php
// [ai-generated-code]
namespace App\Console\Commands;

use App\Discovery\ControllerStructureScout;
use Illuminate\Console\Command;
use ReflectionClass;
use ReflectionMethod;

class DiscoverControllersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discover:controllers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover controllers in the application using structure discoverer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Discovering controllers in the application...');
        
        $controllers = ControllerStructureScout::create()->get();
        
        if (empty($controllers)) {
            $this->warn('No controllers found.');
            return 0;
        }
        
        $this->info('Found the following controllers:');
        
        foreach ($controllers as $controller) {
            $this->line("- {$controller}");
            
            // Also display public methods of the controller
            try {
                $reflection = new ReflectionClass($controller);
                $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                
                $publicMethods = array_filter($methods, function ($method) use ($reflection) {
                    // Skip inherited methods from the base Controller class
                    return $method->class === $reflection->getName() && !$method->isConstructor();
                });
                
                if (!empty($publicMethods)) {
                    $this->line('  Public methods:');
                    foreach ($publicMethods as $method) {
                        $this->line("    • {$method->getName()}()");
                    }
                }
            } catch (\Exception $e) {
                $this->error("  Error inspecting methods: {$e->getMessage()}");
            }
        }
        
        return 0;
    }
} 