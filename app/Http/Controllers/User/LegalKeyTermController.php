<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LegalKeyTerm;
use Illuminate\Http\Request;

class LegalKeyTermController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalKeyTerm::query()->where('status', 'active');
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('term', 'like', "%{$searchTerm}%")
                  ->orWhere('definition', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
            });
        }
        
        // Category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Language filter
        if ($request->has('language') && !empty($request->language)) {
            $query->where('language', $request->language);
        }
        
        $terms = $query->orderBy('term')->paginate(12);
        $categories = LegalKeyTerm::distinct('category')
            ->where('status', 'active')
            ->whereNotNull('category')
            ->pluck('category');
        $languages = LegalKeyTerm::getLanguages();
        
        return view('user.legal-key-terms.index', compact('terms', 'categories', 'languages'));
    }
}
