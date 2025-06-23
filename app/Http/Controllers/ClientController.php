<?php

namespace App\Http\Controllers;
use App\Models\Client;

use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ClientController extends Controller
{    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_status' => 'required|in:Active,Inactive',
        ]);        try {
            // Create a new client
            $client = Client::create([
                'client_name' => $request->client_name,
                'client_email' => $request->client_email,
                'client_status' => $request->client_status,
                'user_id' => Auth::id(),
                'last_accessed' => now(),
            ]);

            return redirect()->route('user.dashboard')
                ->with('success', 'Client added successfully!');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error adding client: ' . $e->getMessage());
            
            return redirect()->route('user.dashboard')
                ->with('error', 'Failed to add client. Please try again.');
        }
    }

    public function selectClient(Request $request)
    {
        // Implementation for selecting client
    }

    public function home()
    {
        // Implementation for home page
        return view('home');
    }

    public function viewTemplates()
    {
        // Implementation for templates view
        return view('templates');
    }    public function viewLegalTable($id, Request $request)
    {
        $legalTable = LegalDocument::findOrFail($id);
        
        // Get client info if client_id provided
        $client = null;
        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
        }
        
        // Get the table data with pagination
        $query = \Illuminate\Support\Facades\DB::table($legalTable->table_name);
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('text_content', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('section', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        // Get column info
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($legalTable->table_name);
        $tableData = $query->paginate(50);
        
        // Process the content to make references clickable
        $tableData->getCollection()->transform(function ($item) {
            if (isset($item->text_content)) {
                $item->text_content = $this->makeReferencesClickable($item->text_content);
            }
            return $item;
        });
        
        return view('view-legal-table-data', compact('legalTable', 'tableData', 'columns', 'client'));
    }

    public function getSectionContent($tableId, $sectionRef)
    {
        try {
            // Find the legal table
            $legalTable = LegalDocument::findOrFail($tableId);
            $tableName = $legalTable->table_name;
            
            // Decode the section reference
            $sectionRef = urldecode($sectionRef);
            
            // Query the table for the section
            $query = \Illuminate\Support\Facades\DB::table($tableName);
            
            // Check if section reference is a simple section number or complex reference
            if (preg_match('/^\d+(\.\d+)?$/', $sectionRef)) {
                // Simple section number
                $query->where('section', $sectionRef);
            } else if (preg_match('/^(\d+(\.\d+)?)\(([^)]+)\)/', $sectionRef, $matches)) {
                // Section with subsection like 123(4)
                $query->where('section', $matches[1])
                      ->where('sub_section', $matches[3]);
            } else {
                // Try to match the exact reference
                $query->where(function($q) use ($sectionRef) {
                    $q->where('section', $sectionRef)
                      ->orWhere('text_content', 'LIKE', "%{$sectionRef}%");
                });
            }
            
            $results = $query->get();
            
            if ($results->isEmpty()) {
                return response()->json(['content' => null]);
            }
            
            // Build the content from results
            $content = "<div class='section-reference-content'>";
            foreach ($results as $row) {
                if (!empty($row->title)) {
                    $content .= "<h5 class='mt-3'>{$row->title}</h5>";
                }
                
                if (!empty($row->text_content)) {
                    $processedContent = $this->makeReferencesClickable($row->text_content);
                    $content .= "<div class='text-content'>{$processedContent}</div>";
                }
                
                if (!empty($row->footnote)) {
                    $content .= "<div class='footnote mt-2'><em>{$row->footnote}</em></div>";
                }
                
                $content .= "<hr>";
            }
            $content .= "</div>";
            
            return response()->json(['content' => $content]);
        } catch (\Exception $e) {
            return response()->json([
                'content' => "<div class='alert alert-danger'>Error: {$e->getMessage()}</div>"
            ]);
        }
    }

    private function makeReferencesClickable($text)
    {
        if (empty($text)) {
            return $text;
        }
        
        // Pattern 1: Simple section references like "section 123"
        $text = preg_replace(
            '/\b(section|sections)\s+(\d+(?:\.\d+)?)\b/i',
            '<span class="ref" data-section-id="$2">$1 $2</span>',
            $text
        );
        
        // Pattern 2: References with parentheses like "paragraph (a)" or "subsection (2)"
        $text = preg_replace_callback(
            '/\b(paragraph|paragraphs|subsection|subsections)\s+\(([a-z0-9\.]+)\)(?:\s+or\s+\(([a-z0-9\.]+)\))?/i',
            function($matches) {
                $result = '<span class="ref" data-section-id="(' . $matches[2] . ')">'. $matches[1] . ' (' . $matches[2] . ')</span>';
                
                if (isset($matches[3])) {
                    $result .= ' or <span class="ref" data-section-id="(' . $matches[3] . ')">(' . $matches[3] . ')</span>';
                }
                
                return $result;
            },
            $text
        );
        
        // Pattern 3: Complex section references like "123(4)" or "123.4(5)(a)"
        $text = preg_replace(
            '/\b(\d+(?:\.\d+)?(?:\([^)]+\)){1,4})\b(?!\s*\([a-z](?:\.\d+)?\))(?![^<>]*<\/span>)/i',
            '<span class="ref" data-section-id="$1">$1</span>',
            $text
        );
        
        return $text;
    }
}
