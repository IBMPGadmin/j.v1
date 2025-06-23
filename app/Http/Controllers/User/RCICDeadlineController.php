<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RCICDeadline;
use Illuminate\Http\Request;

class RCICDeadlineController extends Controller
{
    public function index(Request $request)
    {
        $query = RCICDeadline::query()->where('status', 'active');
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
            });
        }
        
        // Category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }
        
        // Get the filtered deadlines with pagination
        $deadlines = $query->orderBy('title')->paginate(10);
        
        // Get all unique categories for the dropdown
        $categories = RCICDeadline::distinct('category')
            ->where('status', 'active')
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();
        
        return view('user.rcic-deadlines.index', compact('deadlines', 'categories'));
    }
}
