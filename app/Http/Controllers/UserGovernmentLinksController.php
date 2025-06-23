<?php

namespace App\Http\Controllers;

use App\Models\GovernmentLink;
use Illuminate\Http\Request;

class UserGovernmentLinksController extends Controller
{
    /**
     * Display the categories of government links.
     */
    public function index()
    {
        // Get all unique categories from active government links
        $categories = GovernmentLink::where('active', true)
            ->whereNotNull('category')
            ->distinct('category')
            ->pluck('category')
            ->toArray();
            
        // Get the count of links per category
        $categoryCounts = [];
        foreach ($categories as $category) {
            $categoryCounts[$category] = GovernmentLink::where('category', $category)
                ->where('active', true)
                ->count();
        }
        
        return view('user.government-links.index', [
            'categories' => $categories,
            'categoryCounts' => $categoryCounts
        ]);
    }
    
    /**
     * Display the links for a specific category.
     */
    public function showCategory($category)
    {
        $links = GovernmentLink::where('category', $category)
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
            
        return view('user.government-links.category', [
            'category' => $category,
            'links' => $links
        ]);
    }
}
