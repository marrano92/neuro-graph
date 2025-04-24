<?php
// [ai-generated-code]
namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Search for users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            
            if (empty($query)) {
                return response()->json([
                    'results' => [],
                    'message' => 'Query parameter is required'
                ], 200);
            }
            
            $users = User::search($query)->get();
            
            return response()->json([
                'results' => $users,
                'message' => 'Search completed successfully'
            ], 200);
        } catch (Exception $e) {
            Log::error('Error searching users: ' . $e->getMessage());
            
            return response()->json([
                'results' => [],
                'message' => 'An error occurred while searching users',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Search for nodes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchNodes(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $filters = $request->input('filters', []);
            
            if (empty($query)) {
                return response()->json([
                    'results' => [],
                    'message' => 'Query parameter is required'
                ], 200);
            }
            
            $search = Node::search($query);
            
            if (!empty($filters['type'])) {
                $search->where('type', $filters['type']);
            }
            
            $nodes = $search->get();
            
            return response()->json([
                'results' => $nodes,
                'message' => 'Search completed successfully'
            ], 200);
        } catch (Exception $e) {
            Log::error('Error searching nodes: ' . $e->getMessage());
            
            return response()->json([
                'results' => [],
                'message' => 'An error occurred while searching nodes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Combined search for both users and nodes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            
            if (empty($query)) {
                return response()->json([
                    'users' => [],
                    'nodes' => [],
                    'message' => 'Query parameter is required'
                ], 200);
            }
            
            $users = User::search($query)->get();
            $nodes = Node::search($query)->get();
            
            return response()->json([
                'users' => $users,
                'nodes' => $nodes,
                'message' => 'Search completed successfully'
            ], 200);
        } catch (Exception $e) {
            Log::error('Error in combined search: ' . $e->getMessage());
            
            return response()->json([
                'users' => [],
                'nodes' => [],
                'message' => 'An error occurred during search',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
} 