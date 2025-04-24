<?php
// [ai-generated-code]
namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nodes = Node::all();
        return response()->json($nodes);
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'content' => 'nullable|string',
        ]);

        $node = Node::create($validated);
        return response()->json($node, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Node $node)
    {
        return response()->json($node);
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
    public function update(Request $request, Node $node)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'string|max:100',
            'content' => 'nullable|string',
        ]);

        $node->update($validated);
        return response()->json($node);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Node $node)
    {
        $node->delete();
        return response()->json(null, 204);
    }
} 