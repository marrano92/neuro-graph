<?php
// [ai-generated-code]
namespace App\Http\Controllers;

use App\Models\Node;
use App\Services\EmbeddingService;
use App\Http\Resources\NodeResource;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nodes = Node::all();
        return NodeResource::collection($nodes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, EmbeddingService $embeddingService)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'source' => 'nullable|string|max:255',
            'embedding' => 'nullable|json',
            'color' => 'nullable|string|max:50',
        ]);

        // Create the node
        $node = Node::create($validated);

        // Generate embedding if not provided
        if (!isset($validated['embedding']) && $node->label) {
            $text = $node->label;
            if ($node->description) {
                $text .= " " . $node->description;
            }
            
            $node->embedding = $embeddingService->generateEmbedding($text);
            $node->save();
        }

        return new NodeResource($node);
    }

    /**
     * Display the specified resource.
     */
    public function show(Node $node)
    {
        return new NodeResource($node);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Node $node)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Node $node, EmbeddingService $embeddingService)
    {
        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|string|max:100',
            'source' => 'nullable|string|max:255',
            'embedding' => 'nullable|json',
            'color' => 'nullable|string|max:50',
        ]);

        // Check if content is being updated - if so, regenerate embedding
        $shouldRegenerateEmbedding = 
            (isset($validated['label']) && $validated['label'] !== $node->label) ||
            (isset($validated['description']) && $validated['description'] !== $node->description);

        $node->update($validated);

        // Regenerate embedding if content changed and embedding wasn't manually provided
        if ($shouldRegenerateEmbedding && !isset($validated['embedding'])) {
            $text = $node->label;
            if ($node->description) {
                $text .= " " . $node->description;
            }
            
            $node->embedding = $embeddingService->generateEmbedding($text);
            $node->save();
        }

        return new NodeResource($node);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Node $node)
    {
        $node->delete();
        return response()->json(null, 204);
    }

    /**
     * Search for nodes with similar embeddings
     */
    public function findSimilar(Node $node, Request $request, EmbeddingService $embeddingService)
    {
        $limit = $request->input('limit', 5);
        
        // Skip if node has no embedding
        if (!$node->embedding) {
            return response()->json(['error' => 'Node has no embedding vector'], 400);
        }
        
        // Get all other nodes with embeddings
        $candidateNodes = Node::whereNotNull('embedding')
            ->where('id', '!=', $node->id)
            ->get();
        
        // Calculate similarity for each candidate
        $similarNodes = [];
        foreach ($candidateNodes as $candidate) {
            $similarity = $embeddingService->cosineSimilarity(
                $node->embedding, 
                $candidate->embedding
            );
            
            $similarNodes[] = [
                'node' => new NodeResource($candidate),
                'similarity' => $similarity
            ];
        }
        
        // Sort by similarity (highest first)
        usort($similarNodes, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        // Limit results
        $similarNodes = array_slice($similarNodes, 0, $limit);
        
        return response()->json($similarNodes);
    }
} 