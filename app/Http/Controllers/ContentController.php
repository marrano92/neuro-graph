<?php
// [ai-generated-code]
namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Node;
use App\Http\Resources\ContentResource;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contents = Content::all();
        return ContentResource::collection($contents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'source_type' => 'required|string|max:100',
            'source_url' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'node_ids' => 'nullable|array',
            'node_ids.*' => 'exists:nodes,id',
        ]);

        // Extract node_ids before creating content
        $nodeIds = $validated['node_ids'] ?? [];
        unset($validated['node_ids']);

        // Create the content
        $content = Content::create($validated);

        // Attach nodes
        if (!empty($nodeIds)) {
            $content->nodes()->attach($nodeIds);
        }

        // Load the nodes relation for the response
        $content->load('nodes');

        return new ContentResource($content);
    }

    /**
     * Display the specified resource.
     */
    public function show(Content $content)
    {
        $content->load('nodes');
        return new ContentResource($content);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Content $content)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'source_type' => 'sometimes|string|max:100',
            'source_url' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'node_ids' => 'nullable|array',
            'node_ids.*' => 'exists:nodes,id',
        ]);

        // Extract node_ids before updating content
        $nodeIds = null;
        if (isset($validated['node_ids'])) {
            $nodeIds = $validated['node_ids'];
            unset($validated['node_ids']);
        }

        // Update the content
        $content->update($validated);

        // Sync nodes if provided
        if ($nodeIds !== null) {
            $content->nodes()->sync($nodeIds);
        }

        // Load the nodes relation for the response
        $content->load('nodes');

        return new ContentResource($content);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Content $content)
    {
        $content->delete();
        return response()->json(null, 204);
    }

    /**
     * Add nodes to a content
     */
    public function addNodes(Request $request, Content $content)
    {
        $validated = $request->validate([
            'node_ids' => 'required|array',
            'node_ids.*' => 'exists:nodes,id',
        ]);

        $content->nodes()->attach($validated['node_ids']);
        $content->load('nodes');

        return new ContentResource($content);
    }

    /**
     * Remove nodes from a content
     */
    public function removeNodes(Request $request, Content $content)
    {
        $validated = $request->validate([
            'node_ids' => 'required|array',
            'node_ids.*' => 'exists:nodes,id',
        ]);

        $content->nodes()->detach($validated['node_ids']);
        $content->load('nodes');

        return new ContentResource($content);
    }
} 