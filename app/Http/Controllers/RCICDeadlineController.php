<?php

namespace App\Http\Controllers;

use App\Models\RCICDeadline;
use Illuminate\Http\Request;

class RCICDeadlineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RCICDeadline::query();
        
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
        
        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Handle CSV Export
        if ($request->has('export') && $request->export === 'csv') {
            $deadlines = $query->orderBy('title')->get();
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="rcic-deadlines-' . date('Y-m-d') . '.csv"',
            ];
            
            $columns = ['ID', 'Title', 'Category', 'Description', 'Days Before', 'Status', 'Created At', 'Updated At'];
            
            $callback = function() use ($deadlines, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                
                foreach ($deadlines as $deadline) {
                    $row = [
                        $deadline->id,
                        $deadline->title,
                        $deadline->category,
                        $deadline->description,
                        $deadline->days_before,
                        $deadline->status,
                        $deadline->created_at,
                        $deadline->updated_at
                    ];
                    
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        // Get the filtered deadlines with pagination
        $deadlines = $query->orderBy('title')->paginate(10);
        
        // Get all unique categories for the dropdown
        $categories = RCICDeadline::distinct('category')->whereNotNull('category')->pluck('category')->toArray();
        
        return view('rcic-deadlines.index', compact('deadlines', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('rcic-deadlines.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'days_before' => 'nullable|integer',
            'status' => 'required|string|in:active,inactive',
        ]);

        RCICDeadline::create($validated);

        return redirect()->route('admin.rcic-deadlines.index')
            ->with('success', 'RCIC deadline created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $deadline = RCICDeadline::findOrFail($id);
        return view('rcic-deadlines.show', compact('deadline'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $deadline = RCICDeadline::findOrFail($id);
        return view('rcic-deadlines.edit', compact('deadline'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'days_before' => 'nullable|integer',
            'status' => 'required|string|in:active,inactive',
        ]);

        $deadline = RCICDeadline::findOrFail($id);
        $deadline->update($validated);

        return redirect()->route('admin.rcic-deadlines.index')
            ->with('success', 'RCIC deadline updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deadline = RCICDeadline::findOrFail($id);
        $deadline->delete();

        return redirect()->route('admin.rcic-deadlines.index')
            ->with('success', 'RCIC deadline deleted successfully.');
    }
}
