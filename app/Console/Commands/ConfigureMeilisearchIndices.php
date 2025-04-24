<?php
// [ai-generated-code]
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConfigureMeilisearchIndices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:configure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure Meilisearch indices with proper settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Configuring Meilisearch indices...');
        
        $meilisearchHost = config('scout.meilisearch.host');
        $meilisearchKey = config('scout.meilisearch.key');
        
        $this->configureUsersIndex($meilisearchHost, $meilisearchKey);
        $this->configureNodesIndex($meilisearchHost, $meilisearchKey);
        
        $this->info('Meilisearch indices configured successfully!');
        
        return Command::SUCCESS;
    }
    
    /**
     * Configure the users index
     */
    private function configureUsersIndex(string $host, ?string $key): void
    {
        $this->info('Configuring users index...');
        
        $headers = [
            'Content-Type' => 'application/json',
        ];
        
        if ($key) {
            $headers['Authorization'] = "Bearer $key";
        }
        
        try {
            // Set filterable attributes
            $response = Http::withHeaders($headers)
                ->put("$host/indexes/users/settings/filterable-attributes", [
                    'id', 'name', 'email'
                ]);
                
            if ($response->successful()) {
                $this->info('Users filterable attributes set successfully');
            } else {
                $this->error('Failed to set users filterable attributes: ' . $response->body());
            }
            
            // Set searchable attributes
            $response = Http::withHeaders($headers)
                ->put("$host/indexes/users/settings/searchable-attributes", [
                    'id', 'name', 'email'
                ]);
                
            if ($response->successful()) {
                $this->info('Users searchable attributes set successfully');
            } else {
                $this->error('Failed to set users searchable attributes: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Error configuring users index: ' . $e->getMessage());
            Log::error('Error configuring users index: ' . $e->getMessage());
        }
    }
    
    /**
     * Configure the nodes index
     */
    private function configureNodesIndex(string $host, ?string $key): void
    {
        $this->info('Configuring nodes index...');
        
        $headers = [
            'Content-Type' => 'application/json',
        ];
        
        if ($key) {
            $headers['Authorization'] = "Bearer $key";
        }
        
        try {
            // Set filterable attributes
            $response = Http::withHeaders($headers)
                ->put("$host/indexes/nodes/settings/filterable-attributes", [
                    'id', 'name', 'type'
                ]);
                
            if ($response->successful()) {
                $this->info('Nodes filterable attributes set successfully');
            } else {
                $this->error('Failed to set nodes filterable attributes: ' . $response->body());
            }
            
            // Set searchable attributes
            $response = Http::withHeaders($headers)
                ->put("$host/indexes/nodes/settings/searchable-attributes", [
                    'id', 'name', 'type', 'content'
                ]);
                
            if ($response->successful()) {
                $this->info('Nodes searchable attributes set successfully');
            } else {
                $this->error('Failed to set nodes searchable attributes: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Error configuring nodes index: ' . $e->getMessage());
            Log::error('Error configuring nodes index: ' . $e->getMessage());
        }
    }
} 