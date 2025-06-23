<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserLegalTableController extends Controller
{    public function show(Request $request, $clientId)
    {
        $client = DB::table('client_table')->where('id', $clientId)->first();        // Add a message to inform the user about the database tables
        $message = null;
        try {
            // Check if the legal_tables_master table exists
            $hasTable = DB::select("SHOW TABLES LIKE 'legal_tables_master'");
            
            if (empty($hasTable)) {
                $results = collect([]);
                $message = "The legal tables have not been set up yet. Please run the necessary migrations.";
            } else {
                // Simplified query to just fetch from legal_tables_master table
                $query = DB::table('legal_tables_master')
                    ->select('*');
                    
                // Only add where clause if the column exists
                try {
                    $columns = DB::select("SHOW COLUMNS FROM legal_tables_master");
                    $columnNames = array_column($columns, 'Field');
                    
                    if (in_array('status', $columnNames)) {
                        $query->where('status', 'active');
                    }
                    
                    if ($request->filled('search') && in_array('act_name', $columnNames)) {
                        $query->where('act_name', 'like', '%' . $request->search . '%');
                    }
                    if ($request->filled('act_id') && in_array('act_id', $columnNames)) {
                        $query->where('act_id', $request->act_id);
                    }
                    if ($request->filled('law_id') && in_array('law_id', $columnNames)) {
                        $query->where('law_id', $request->law_id);
                    }
                    if ($request->filled('jurisdiction_id') && in_array('jurisdiction_id', $columnNames)) {
                        $query->where('jurisdiction_id', $request->jurisdiction_id);
                    }
                    
                    if (in_array('id', $columnNames)) {
                        $query->orderBy('id', 'asc');
                    }
                } catch (\Exception $e) {
                    // If error occurs with columns, just get all records
                }

                $results = $query->get();
            }
        } catch (\Exception $e) {
            // If any error occurs, return empty collection
            $results = collect([]);
            $message = "Error accessing the database: " . $e->getMessage();
        }        return view('user-legal-tables', compact('client', 'results', 'message'));
    }
}
