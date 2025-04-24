<?php
// [ai-generated-code]
namespace App\Http\Controllers;

use App\Jobs\ExampleJob;
use Illuminate\Http\Request;

class HorizonDemoController extends Controller
{
    /**
     * Show the Horizon demo page.
     */
    public function index()
    {
        return view('horizon-demo');
    }
    
    /**
     * Dispatch example jobs.
     */
    public function dispatchJobs(Request $request)
    {
        $count = $request->input('count', 10);
        
        // Limit max jobs to 100 for safety
        $count = min(100, max(1, (int) $count));
        
        for ($i = 1; $i <= $count; $i++) {
            ExampleJob::dispatch("Web job #{$i} at " . now()->toDateTimeString());
        }
        
        return redirect()->route('horizon.demo')->with('status', "{$count} jobs dispatched successfully!");
    }
} 