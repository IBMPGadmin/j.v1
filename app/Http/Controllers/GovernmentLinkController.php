<?php

namespace App\Http\Controllers;

use App\Models\GovernmentLink;
use Illuminate\Http\Request;

class GovernmentLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GovernmentLink::query();
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('url', 'like', "%{$searchTerm}%")
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
            $query->where('active', $request->status);
        }
        
        // Handle CSV Export
        if ($request->has('export') && $request->export === 'csv') {
            $links = $query->orderBy('sort_order')->orderBy('name')->get();
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="government-links-' . date('Y-m-d') . '.csv"',
            ];
            
            $columns = ['ID', 'Name', 'URL', 'Category', 'Description', 'Active', 'Sort Order', 'Created At', 'Updated At'];
            
            $callback = function() use ($links, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                
                foreach ($links as $link) {
                    $row = [
                        $link->id,
                        $link->name,
                        $link->url,
                        $link->category,
                        $link->description,
                        $link->active ? 'Yes' : 'No',
                        $link->sort_order,
                        $link->created_at,
                        $link->updated_at
                    ];
                    
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        // Get the filtered links with pagination
        $links = $query->orderBy('sort_order')->orderBy('name')->paginate(10);
        
        // Get all unique categories for the dropdown
        $categories = GovernmentLink::distinct('category')->whereNotNull('category')->pluck('category')->toArray();
        
        return view('government-links.index', compact('links', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('government-links.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        GovernmentLink::create($validated);

        return redirect()->route('admin.government-links.index')
            ->with('success', 'Government link created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $link = GovernmentLink::findOrFail($id);
        return view('government-links.show', compact('link'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $link = GovernmentLink::findOrFail($id);
        return view('government-links.edit', compact('link'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $link = GovernmentLink::findOrFail($id);
        $link->update($validated);

        return redirect()->route('admin.government-links.index')
            ->with('success', 'Government link updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $link = GovernmentLink::findOrFail($id);
        $link->delete();

        return redirect()->route('admin.government-links.index')
            ->with('success', 'Government link deleted successfully.');
    }
}
