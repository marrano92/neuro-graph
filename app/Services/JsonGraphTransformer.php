<?php
// [ai-generated-code]
namespace App\Services;

use App\Contracts\GraphTransformer;

class JsonGraphTransformer implements GraphTransformer
{
    /**
     * Transform a graph structure to JSON format.
     *
     * @param array $data
     * @return array
     */
    public function transform(array $data): array
    {
        // Process the data for JSON format
        return [
            'nodes' => $data['nodes'] ?? [],
            'edges' => $data['connections'] ?? [],
            'format' => 'json'
        ];
    }
} 