<?php

namespace App\Http\Controllers;

use App\Helpers\LegalReferenceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewLegalTableController extends Controller
{
    public function show(Request $request)
    {
        $tableName = $request->table;
        $categoryId = $request->category_id;
        $client_id = $request->client_id;
        
        // Get client information
        $client = DB::table('client_table')->where('id', $client_id)->first();
        
        // Get metadata about this legal table
        $metadata = null;
        $legalTable = null;
        try {
            $metadata = DB::table('legal_tables_master')
                ->where('id', $categoryId)
                ->first();
                
            // Also assign to legalTable for compatibility with the view
            $legalTable = $metadata;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to find legal table metadata.');
        }
        
        // Validate table name to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            return redirect()->back()->with('error', 'Invalid table name.');
        }
        
        // Check if table exists
        $tableData = [];
        $columns = [];
        $error = null;
        
        try {
            // Check if table exists
            $exists = Schema::hasTable($tableName);
            
            if ($exists) {
                // Get all columns from the table
                $columns = Schema::getColumnListing($tableName);
                
                // Execute the query to get all data with pagination
                $tableData = DB::table($tableName)->paginate(15); // 15 items per page
                
                // Get user's annotations if user is authenticated
                $annotations = [];
                if (Auth::check()) {
                    $userId = Auth::id();
                    $annotations = DB::table('juris_user_texts')
                        ->where('user_id', $userId)
                        ->where('category_id', $categoryId)
                        ->get()
                        ->keyBy('section_id');
                }
            } else {
                $error = "The table '$tableName' does not exist in the database.";
            }
        } catch (\Exception $e) {
            $error = "Error accessing the table: " . $e->getMessage();
        }
        
        return view('view-legal-table-data', compact(
            'client', 
            'metadata',
            'legalTable',
            'tableName', 
            'tableData', 
            'columns', 
            'error',
            'annotations',
            'categoryId'
        ));
    }
    
    /**
     * Get the lowest (most specific) identifier from a row
     * 
     * @param object $row The row to analyze
     * @return string|null The lowest identifier found
     */
    private function getLowestIdentifier($row)
    {
        if (!empty($row->sub_paragraph)) return $row->sub_paragraph;
        if (!empty($row->paragraph)) return $row->paragraph;
        if (!empty($row->sub_section)) return $row->sub_section;
        if (!empty($row->section)) return $row->section;
        if (!empty($row->sub_division)) return $row->sub_division;
        return null;
    }    /**
     * Get content for a specific section - updated implementation based on legacy logic
     */
    public function getSectionContent(Request $request, $tableId, $sectionRef)
    {
        try {
            Log::info("Section content request for tableId=$tableId, sectionRef=$sectionRef");
            
            // Get table info from legal_tables_master
            $tableInfo = DB::table('legal_tables_master')
                ->where('id', $tableId)
                ->first();
                
            if (!$tableInfo) {
                Log::warning("Table not found for ID: $tableId");
                return response()->json(['error' => true, 'message' => 'Table not found'], 404);
            }
            
            Log::info("Found table: " . $tableInfo->table_name);
            $currentTable = $tableInfo->table_name;
            $actNames = array_filter([
                $tableInfo->act_name_1 ?? null,
                $tableInfo->act_name_2 ?? null,
                $tableInfo->act_name_3 ?? null,
            ]);
            
            $sections = [];
            $categoryId = $tableId;
            $sectionId = $sectionRef;
            
            // Check if this is a cross-act reference (contains Act or Rules name in parentheses)
            if (preg_match('/^([^(]+)\(([^)]+(?:Act|Rules)[^)]*)\)$/i', $sectionId, $matches)) {
                $sectionIdPart = trim($matches[1]); // e.g., '2(1)'
                $actName = trim($matches[2]); // e.g., 'Immigration and Refugee Protection Act'
                
                // Search for act/rules name in legal_tables_master
                $actQuery = DB::table('legal_tables_master')
                    ->where(function($query) use ($actName) {
                        $query->where('act_name_1', 'like', "%$actName%")
                              ->orWhere('act_name_2', 'like', "%$actName%")
                              ->orWhere('act_name_3', 'like', "%$actName%");
                    })
                    ->where('id', '!=', $categoryId)
                    ->orderByRaw(
                        "CASE 
                            WHEN act_name_1 = ? THEN 1
                            WHEN act_name_2 = ? THEN 2
                            WHEN act_name_3 = ? THEN 3
                            WHEN act_name_1 LIKE ? THEN 4
                            WHEN act_name_2 LIKE ? THEN 5
                            WHEN act_name_3 LIKE ? THEN 6
                            ELSE 7
                        END", 
                        [$actName, $actName, $actName, "%$actName%", "%$actName%", "%$actName%"]
                    )
                    ->limit(1);
                
                $actRow = $actQuery->first();
                
                if ($actRow) {
                    // Found the referenced act, now search its table
                    $refTable = $actRow->table_name;
                    $actCategoryId = $actRow->id;
                    
                    // Validate table name to prevent SQL injection
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $refTable)) {
                        return response()->json(['error' => 'Invalid table name'], 400);
                    }
                    
                    // Check if table exists
                    if (!Schema::hasTable($refTable)) {
                        return response()->json(['error' => 'Referenced table does not exist'], 404);
                    }
                    
                    // Query the referenced table
                    $refQuery = DB::table($refTable)
                        ->where('category_id', $actCategoryId)
                        ->where(function($query) use ($sectionIdPart) {
                            $query->where('section_id', $sectionIdPart)
                                  ->orWhere('section_id', 'like', $sectionIdPart.'.%')
                                  ->orWhere('section_id', 'like', $sectionIdPart.'%');
                        })
                        ->orderByRaw(
                            "CASE 
                                WHEN section_id = ? THEN 1
                                WHEN section_id LIKE ? THEN 2
                                ELSE 3
                            END", 
                            [$sectionIdPart, $sectionIdPart.'.%']
                        )
                        ->limit(10);
                    
                    $refRows = $refQuery->get();
                    
                    foreach ($refRows as $row) {
                        // Add meta information
                        $row->lowest_identifier = $this->getLowestIdentifier($row);
                        $row->from_other_category = 1;
                        $row->source_table = $refTable;
                        
                        // Add the act name to the title
                        $row->title = ($row->title ?? '') . ' [' . $actName . ']';
                        
                        // Add the category ID explicitly
                        $row->category_id = $actCategoryId;
                        
                        // Process content to make references clickable if needed
                        if (isset($row->text_content)) {
                            $row->text_content = LegalReferenceHelper::processContent($row->text_content, $actCategoryId, $sectionIdPart);
                        }
                        
                        $sections[] = $row;
                    }
                    
                    if (!empty($sections)) {
                        // Found sections in the referenced act
                        return response()->json(['error' => false, 'data' => $sections]);
                    } else {
                        // Act exists but section not found
                        return response()->json([
                            'error' => false, 
                            'data' => [[
                                'act_reference_found' => true,
                                'act_id' => $actRow->id,
                                'table_name' => $actRow->table_name,
                                'act_name' => $actName,
                                'section_searched' => $sectionIdPart,
                                'act_names' => array_filter([
                                    $actRow->act_name_1,
                                    $actRow->act_name_2,
                                    $actRow->act_name_3
                                ]),
                                'title' => 'Reference to ' . $actName,
                                'text_content' => 'Section ' . $sectionIdPart . ' not found in ' . $actName . '. Click to browse this document.',
                                'section_id' => $sectionIdPart,
                                'category_id' => $actRow->id
                            ]]
                        ]);
                    }
                } else {
                    // Referenced act not found
                    return response()->json([
                        'error' => false, 
                        'data' => [[
                            'error' => true,
                            'title' => 'Reference Not Found',
                            'text_content' => 'Could not find "' . $actName . '" in the legal database.',
                            'section_id' => $sectionId,
                            'category_id' => $categoryId
                        ]]
                    ]);
                }
            }
            
            // Regular search in current table (not a cross-act reference)
            // Validate table name to prevent SQL injection
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $currentTable)) {
                return response()->json(['error' => 'Invalid table name'], 400);
            }
            
            // Check if table exists
            if (!Schema::hasTable($currentTable)) {
                return response()->json(['error' => 'Table does not exist'], 404);
            }
            
            // ENHANCED SEARCH LOGIC: Parse the section reference to handle different patterns
            // Parse section IDs like "46", "46(1)", "46(1)(a)", etc.
            $section = null;
            $subsection = null;
            $paragraph = null;
            $subparagraph = null;
            
            // Log raw sectionId for debugging
            Log::info("Parsing reference: $sectionId");
            
            // Handle simple section numbers like "46"
            if (preg_match('/^(\d+(?:\.\d+)?)$/', $sectionId, $matches)) {
                $section = $matches[1];
                Log::info("Parsed as simple section: $section");
            }
            // Handle section with subsection like "46(1)"
            elseif (preg_match('/^(\d+(?:\.\d+)?)\((\d+(?:\.\d+)?)\)$/', $sectionId, $matches)) {
                $section = $matches[1];
                $subsection = $matches[2];
                Log::info("Parsed as section+subsection: section=$section, subsection=$subsection");
            }
            // Handle section with subsection and paragraph like "46(1)(a)"
            elseif (preg_match('/^(\d+(?:\.\d+)?)\((\d+(?:\.\d+)?)\)\(([a-z](?:\.\d+)?)\)$/', $sectionId, $matches)) {
                $section = $matches[1];
                $subsection = $matches[2];
                $paragraph = $matches[3];
                Log::info("Parsed as section+subsection+paragraph: section=$section, subsection=$subsection, paragraph=$paragraph");
            }
            // Handle section with subsection, paragraph and subparagraph like "46(1)(a)(i)"
            elseif (preg_match('/^(\d+(?:\.\d+)?)\((\d+(?:\.\d+)?)\)\(([a-z](?:\.\d+)?)\)\(([ivxlcdm]+|\d+)\)$/', $sectionId, $matches)) {
                $section = $matches[1];
                $subsection = $matches[2];
                $paragraph = $matches[3];
                $subparagraph = $matches[4];
                Log::info("Parsed as full hierarchy: section=$section, subsection=$subsection, paragraph=$paragraph, subparagraph=$subparagraph");
            }
            // Handle direct paragraph reference like "(a)" - use context from frontend if provided
            elseif (preg_match('/^\(([a-z](?:\.\d+)?)\)$/', $sectionId, $matches)) {
                $paragraph = $matches[1];
                $contextSection = $request->input('context_section');
                $contextSubsection = $request->input('context_subsection');
                if ($contextSection) {
                    $section = $contextSection;
                    $subsection = $contextSubsection;
                    Log::info("Using context: section=$section, subsection=$subsection");
                } else {
                    Log::info("No context provided, will search for paragraph across all sections");
                }
            }
            
            // Build query based on the parsed components
            $query = DB::table($currentTable)
                ->where('category_id', $categoryId);
                  // Apply filters based on what we parsed
            if ($section) {
                // Try both exact match and LIKE with wildcards for section
                $query->where(function($q) use ($section) {
                    $q->where('section', $section)
                      ->orWhere('section', 'LIKE', $section.'%')
                      ->orWhere('section_id', 'LIKE', $section.'%');
                });
                
                Log::info("Searching for section with value: $section (using more flexible matching)");
            }
            if ($subsection) {
                $query->where(function($q) use ($subsection) {
                    $q->where('sub_section', $subsection)
                      ->orWhere('sub_section', 'LIKE', $subsection.'%');
                });
            }
            if ($paragraph) {
                $query->where(function($q) use ($paragraph) {
                    $q->where('paragraph', $paragraph)
                      ->orWhere('paragraph', 'LIKE', $paragraph.'%');
                });
            }
            if ($subparagraph) {
                $query->where(function($q) use ($subparagraph) {
                    $q->where('sub_paragraph', $subparagraph)
                      ->orWhere('sub_paragraph', 'LIKE', $subparagraph.'%');
                });
            }
            
            // If we didn't match any of the patterns, fall back to the original search logic
            if (!$section && !$paragraph) {
                Log::info("No pattern matched, using fallback search on section_id: $sectionId");
                $query = DB::table($currentTable)
                    ->where('category_id', $categoryId)
                    ->where(function($q) use ($sectionId) {
                        $q->where('section_id', $sectionId)
                          ->orWhere('section_id', 'like', $sectionId.'.%')
                          ->orWhere('section_id', 'like', $sectionId.'%');
                    });
            }
            
            // Order results and limit to 10
            $query->orderByRaw(
                "CASE 
                    WHEN section_id = ? THEN 1
                    WHEN section_id LIKE ? THEN 2
                    ELSE 3
                END", 
                [$sectionId, $sectionId.'.%']
            )->limit(10);
              // Log the SQL query for debugging
            $sql = $query->toSql();
            $bindings = $query->getBindings();
            Log::info("Executing query: $sql with bindings: " . json_encode($bindings));
            
            // Add raw SQL reconstruction for easier debugging
            $rawSql = str_replace(['?'], array_map(function($binding) {
                return is_numeric($binding) ? $binding : "'" . $binding . "'";
            }, $bindings), $sql);
            Log::info("Raw SQL equivalent: $rawSql");
              
            $rows = $query->get();
            Log::info("Found " . count($rows) . " rows");
            
            // If no rows found, return a clear error message
            if ($rows->isEmpty()) {
                Log::warning("No rows found for section reference: $sectionId");
                return response()->json([
                    'error' => false,
                    'data' => [
                        [
                            'title' => 'No Content Found',
                            'text_content' => "No content found for reference: $sectionId. Please check the reference and try again.",
                            'section_id' => $sectionId,
                            'category_id' => $categoryId,
                            'debug_info' => [
                                'section' => $section,
                                'subsection' => $subsection,
                                'paragraph' => $paragraph,
                                'subparagraph' => $subparagraph,
                                'query_type' => $section || $paragraph ? 'structured' : 'fallback'
                            ]
                        ]
                    ]
                ]);
            }
            
            foreach ($rows as $row) {
                // Add meta information
                $row->lowest_identifier = $this->getLowestIdentifier($row);
                $row->from_other_category = 0;
                $row->source_table = $currentTable;
                
                // Process content to make references clickable if needed
                if (isset($row->text_content)) {
                    $row->text_content = LegalReferenceHelper::processContent($row->text_content, $categoryId, $sectionId);
                }
                
                $sections[] = $row;
            }
            
            // Remove duplicates
            $uniqueSections = [];
            $seenCombinations = [];
            
            foreach ($sections as $section) {
                $key = ($section->section_id ?? '') . '_' . ($section->category_id ?? '');
                if (!in_array($key, $seenCombinations)) {
                    $seenCombinations[] = $key;
                    $uniqueSections[] = $section;
                }
            }
            
            // Return results
            return response()->json(['error' => false, 'data' => $uniqueSections]);
        } catch (\Exception $e) {
            Log::error("Error in getSectionContent: " . $e->getMessage());
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Save or update an annotation
     */
    public function saveAnnotation(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $request->validate([
            'section_id' => 'required|string',
            'category_id' => 'required|integer',
            'note_text' => 'required|string',
        ]);
        
        $userId = Auth::id();
        $sectionId = $request->section_id;
        $categoryId = $request->category_id;
        $noteText = $request->note_text;
        
        try {
            // Check if annotation already exists
            $existing = DB::table('juris_user_texts')
                ->where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->where('section_id', $sectionId)
                ->first();
                
            if ($existing) {
                // Update existing annotation
                DB::table('juris_user_texts')
                    ->where('id', $existing->id)
                    ->update([
                        'note_text' => $noteText,
                        'updated_at' => now()
                    ]);
                    
                return response()->json([
                    'success' => true,
                    'message' => 'Annotation updated successfully',
                    'id' => $existing->id
                ]);
            } else {
                // Create new annotation
                $id = DB::table('juris_user_texts')->insertGetId([
                    'user_id' => $userId,
                    'category_id' => $categoryId,
                    'section_id' => $sectionId,
                    'note_text' => $noteText,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Annotation saved successfully',
                    'id' => $id
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving annotation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete an annotation
     */
    public function deleteAnnotation(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $userId = Auth::id();
        
        try {
            // Verify the annotation belongs to the user
            $annotation = DB::table('juris_user_texts')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->first();
                
            if (!$annotation) {
                return response()->json(['error' => 'Annotation not found or access denied'], 404);
            }
            
            // Delete the annotation
            DB::table('juris_user_texts')
                ->where('id', $id)
                ->delete();
                
            return response()->json([
                'success' => true,
                'message' => 'Annotation deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting annotation: ' . $e->getMessage()
            ], 500);
        }
    }
      /**
     * Simplified method to fetch reference content by direct ID
     */
    public function fetchReferenceById($referenceId)
    {
        try {
            Log::info('Reference fetch request for ID: ' . $referenceId);
            
            // Parse the reference ID to extract table and row information
            // Format: table_id:row_id or just row_id (using default table)
            $parts = explode(':', $referenceId);
            
            if (count($parts) > 1) {
                $tableId = $parts[0];
                $rowId = $parts[1];
            } else {
                // If no table specified, use the row ID directly
                $rowId = $parts[0];
                // Get the active table from session or use a default
                $tableId = session('active_table_id', null);
            }
            
            Log::info("Parsed reference: tableId=$tableId, rowId=$rowId");
            
            if (!$tableId) {
                Log::warning('No table specified and no active table found');
                return response()->json(['error' => true, 'message' => 'No table specified and no active table found'], 400);
            }
            
            // Get the table name from legal_tables_master
            $tableInfo = DB::table('legal_tables_master')
                ->where('id', $tableId)
                ->first();
                
            if (!$tableInfo) {
                Log::warning("Table not found for ID: $tableId");
                return response()->json(['error' => true, 'message' => 'Table not found'], 404);
            }
            
            $tableName = $tableInfo->table_name;
            Log::info("Found table: $tableName");
            
            // Validate table name to prevent SQL injection
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
                Log::warning("Invalid table name: $tableName");
                return response()->json(['error' => true, 'message' => 'Invalid table name'], 400);
            }
            
            // Direct lookup by row ID (much simpler than the complex search)
            $row = DB::table($tableName)
                ->where('id', $rowId)
                ->first();
                
            if (!$row) {
                Log::warning("Row not found: $rowId in table $tableName");
                return response()->json(['error' => true, 'message' => 'Reference not found'], 404);
            }
            
            Log::info("Found row data: " . json_encode($row));
            
            // Process any references in the content
            if (isset($row->text_content)) {
                // Get the section ID for context when processing references
                $sectionId = $row->section_id ?? null;
                if (!$sectionId && isset($row->section)) {
                    // Build section ID from components if not directly available
                    $sectionId = $row->section;
                    if (!empty($row->sub_section)) {
                        $sectionId .= '(' . $row->sub_section . ')';
                    }
                    if (!empty($row->paragraph)) {
                        $sectionId .= '(' . $row->paragraph . ')';
                    }
                    if (!empty($row->sub_paragraph)) {
                        $sectionId .= '(' . $row->sub_paragraph . ')';
                    }
                }
                
                $row->text_content = LegalReferenceHelper::processContent($row->text_content, $tableId, $sectionId);
            }
            
            // Add metadata for display
            $row->source_table = $tableName;
            $row->table_id = $tableId;
            
            // Build a more complete title if possible
            $title = $row->title ?? ($row->heading ?? null);
            if (!$title) {
                // Create a title from the hierarchical information
                $titleParts = [];
                if (!empty($row->part)) $titleParts[] = "Part {$row->part}";
                if (!empty($row->division)) $titleParts[] = "Division {$row->division}";
                if (!empty($row->sub_division)) $titleParts[] = "Subdivision {$row->sub_division}";
                if (!empty($row->section)) $titleParts[] = "Section {$row->section}";
                if (!empty($row->sub_section)) $titleParts[] = "Subsection {$row->sub_section}";
                if (!empty($row->paragraph)) $titleParts[] = "Paragraph {$row->paragraph}";
                if (!empty($row->sub_paragraph)) $titleParts[] = "Subparagraph {$row->sub_paragraph}";
                
                $title = !empty($titleParts) ? implode(' > ', $titleParts) : "Reference {$rowId}";
            }
            
            // Get the content, with fallbacks for different column names
            $content = $row->text_content ?? ($row->section_text ?? ($row->content ?? 'No content available'));
            
            $response = [
                'error' => false, 
                'data' => [
                    'title' => $title,
                    'content' => $content,
                    'section_id' => $row->section_id ?? null,
                    'metadata' => [
                        'part' => $row->part ?? null,
                        'division' => $row->division ?? null,
                        'sub_division' => $row->sub_division ?? null,
                        'section' => $row->section ?? null,
                        'sub_section' => $row->sub_section ?? null,
                        'paragraph' => $row->paragraph ?? null,
                        'sub_paragraph' => $row->sub_paragraph ?? null
                    ]
                ]
            ];
            
            Log::info('Sending response: ' . json_encode($response));
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error in fetchReferenceById: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
}
