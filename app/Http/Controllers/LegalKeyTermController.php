<?php

namespace App\Http\Controllers;

use App\Models\LegalKeyTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LegalKeyTermController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalKeyTerm::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('term', 'like', "%{$searchTerm}%")
                  ->orWhere('definition', 'like', "%{$searchTerm}%");
            });
        }

        // Language filter
        if ($request->has('language') && !empty($request->language)) {
            $query->where('language', $request->language);
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
            $terms = $query->get();
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="legal-key-terms-' . date('Y-m-d') . '.csv"',
            ];
            
            $columns = ['ID', 'Term', 'Definition', 'Language', 'Category', 'Source', 'Notes', 'Status', 'Created At'];
            
            $callback = function() use ($terms, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                
                foreach ($terms as $term) {
                    $row = [
                        $term->id,
                        $term->term,
                        $term->definition,
                        $term->language,
                        $term->category,
                        $term->source,
                        $term->notes,
                        $term->status,
                        $term->created_at,
                    ];
                    fputcsv($file, $row);
                }
                fclose($file);
            };
            
            return Response::stream($callback, 200, $headers);
        }

        $terms = $query->orderBy('term')->paginate(10);
        $languages = LegalKeyTerm::getLanguages();
        $categories = LegalKeyTerm::getCategories();

        return view('legal-key-terms.index', compact('terms', 'languages', 'categories'));
    }

    public function create()
    {
        $languages = LegalKeyTerm::getLanguages();
        $categories = LegalKeyTerm::getCategories();
        return view('legal-key-terms.create', compact('languages', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term' => 'required|string|max:255',
            'definition' => 'required|string',
            'language' => 'required|string|max:10',
            'category' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        LegalKeyTerm::create($validated);

        return redirect()->route('admin.legal-key-terms.index')
            ->with('success', 'Legal key term created successfully.');
    }

    public function edit(string $id)
    {
        $term = LegalKeyTerm::findOrFail($id);
        $languages = LegalKeyTerm::getLanguages();
        $categories = LegalKeyTerm::getCategories();
        return view('legal-key-terms.edit', compact('term', 'languages', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'term' => 'required|string|max:255',
            'definition' => 'required|string',
            'language' => 'required|string|max:10',
            'category' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $term = LegalKeyTerm::findOrFail($id);
        $term->update($validated);

        return redirect()->route('admin.legal-key-terms.index')
            ->with('success', 'Legal key term updated successfully.');
    }

    public function destroy(string $id)
    {
        $term = LegalKeyTerm::findOrFail($id);
        $term->delete();

        return redirect()->route('admin.legal-key-terms.index')
            ->with('success', 'Legal key term deleted successfully.');
    }
}
