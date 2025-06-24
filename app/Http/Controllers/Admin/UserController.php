<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Show add user form
    public function create()
    {
        $users = User::orderByDesc('created_at')->get();
        return view('admin.users.add', compact('users'));
    }

    // Handle add user form submission
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,admin',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.add')->with('success', 'User added successfully!');
    }

    // Show all users
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search by name, email or role
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }
        
        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Order by
        $orderBy = $request->order_by ?? 'created_at';
        $orderDirection = $request->order_direction ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);
        
        // Get users with pagination
        $users = $query->paginate(15)->withQueryString();
        
        return view('admin.users.index', compact('users'));
    }

    // Show user details
    public function show($id)
    {
        $user = User::with('subscriptions.package')->findOrFail($id);
        
        // Get subscription status
        $activeSubscription = $user->activeSubscription();
        
        return view('admin.users.show', compact('user', 'activeSubscription'));
    }

    // Toggle user active/deactive
    public function toggleStatus($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->status = ($user->status ?? $user->user_status ?? 1) ? 0 : 1;
        $user->save();
        return redirect()->route('admin.users.index')->with('success', 'User status updated!');
    }

    // Delete user
    public function destroy($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted!');
    }

    // Handle legal document upload
    public function storeLegalDocument(Request $request)
    {
        // Allow long processing for large XML files
        set_time_limit(0); // No time limit
        ini_set('memory_limit', '1024M'); // Increase memory for large files

        $request->validate([
            'xmlfile' => 'required|file|mimetypes:text/xml,application/xml,application/x-xml,text/plain,text/x-xml',
            'law_id' => 'required|integer',
            'act_id' => 'required|integer',
            'act_name' => 'required|string',
            'jurisdiction_id' => 'required|integer',
        ]);

        $host = config('database.connections.mysql.host', 'localhost');
        $dbname = config('database.connections.mysql.database', 'testings');
        $user = config('database.connections.mysql.username', 'root');
        $pass = config('database.connections.mysql.password', '');
        $db = new \mysqli($host, $user, $pass, $dbname);
        if ($db->connect_error) {
            return back()->withErrors(['db' => 'Connection failed: ' . $db->connect_error]);
        }
        set_time_limit(1000);

        // Helper functions
        $ensure_master_table_exists = function($db) {
            $sql = "CREATE TABLE IF NOT EXISTS legal_tables_master (
                id INT AUTO_INCREMENT PRIMARY KEY,
                table_name VARCHAR(255) NOT NULL,
                original_filename VARCHAR(255) NOT NULL,
                law_id INT NOT NULL,
                act_id INT NOT NULL,
                act_name VARCHAR(255) NOT NULL,
                act_name_1 VARCHAR(255),
                act_name_2 VARCHAR(255),
                act_name_3 VARCHAR(255),
                jurisdiction_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                legaldocument_id INT,
                INDEX (table_name),
                INDEX (law_id),
                INDEX (act_id),
                INDEX (jurisdiction_id)
            )";
            $db->query($sql);
        };
        $generate_table_name = function($db) use ($ensure_master_table_exists) {
            $ensure_master_table_exists($db);
            $sql = "SELECT COUNT(*) as count FROM legal_tables_master";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            $next_number = $row['count'] + 1;
            return "legaldocument" . $next_number;
        };
        $create_legal_table = function($db, $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_id INT NOT NULL,
                parent_id INT,
                part VARCHAR(50),
                division VARCHAR(50),
                sub_division VARCHAR(50) NOT NULL DEFAULT '',
                section VARCHAR(50),
                sub_section VARCHAR(50),
                paragraph VARCHAR(50),
                sub_paragraph VARCHAR(50),
                section_id VARCHAR(100),
                title TEXT,
                text_content LONGTEXT,
                footnote TEXT,
                paging VARCHAR(100),
                law_id INT NOT NULL,
                act_id INT NOT NULL,
                jurisdiction_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (category_id),
                INDEX (parent_id),
                INDEX (section_id),
                INDEX (law_id),
                INDEX (act_id),
                INDEX (jurisdiction_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $db->query($sql);
        };
        $record_table_creation = function($db, $table_name, $original_filename, $law_id, $act_id, $act_name, $jurisdiction_id, $act_name_1 = '', $act_name_2 = '', $act_name_3 = '') {
            $stmt = $db->prepare("INSERT INTO legal_tables_master (table_name, original_filename, law_id, act_id, act_name, jurisdiction_id, act_name_1, act_name_2, act_name_3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiisisss", $table_name, $original_filename, $law_id, $act_id, $act_name, $jurisdiction_id, $act_name_1, $act_name_2, $act_name_3);
            $stmt->execute();
            $inserted_id = $db->insert_id;
            $update_stmt = $db->prepare("UPDATE legal_tables_master SET legaldocument_id = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $inserted_id, $inserted_id);
            $update_stmt->execute();
        };
        $get_next_category_id = function($db) {
            $result = $db->query("SELECT table_name FROM legal_tables_master");
            $table_names = [];
            while ($row = $result->fetch_assoc()) {
                $table_names[] = $row['table_name'];
            }
            if (empty($table_names)) return 1;
            $union_queries = [];
            foreach ($table_names as $table_name) {
                $union_queries[] = "(SELECT MAX(category_id) as category_id FROM $table_name)";
            }
            $sql = "SELECT MAX(category_id) as max_id FROM (" . implode(" UNION ALL ", $union_queries) . ") as combined";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            $max_category_id = $row['max_id'];
            return ($max_category_id === null) ? 1 : $max_category_id + 1;
        };

        $file = $request->file('xmlfile');
        $original_filename = $file->getClientOriginalName();
        $law_id = (int)$request->input('law_id');
        $act_id = (int)$request->input('act_id');
        $act_name = $request->input('act_name');
        $jurisdiction_id = (int)$request->input('jurisdiction_id');
        $act_name_1 = $request->input('act_name_1', '');
        $act_name_2 = $request->input('act_name_2', '');
        $act_name_3 = $request->input('act_name_3', '');

        $db->begin_transaction();
        try {
            $table_name = $generate_table_name($db);
            $create_legal_table($db, $table_name);
            $record_table_creation($db, $table_name, $original_filename, $law_id, $act_id, $act_name, $jurisdiction_id, $act_name_1, $act_name_2, $act_name_3);
            $category_id = $get_next_category_id($db);
            $xml = simplexml_load_file($file->getRealPath());
            if ($xml === false) {
                throw new \Exception("Failed to parse XML file");
            }
            // --- insert_data logic ---
            $last_inserted_id = null;
            $this->insertLegalDocumentData($db, $xml->Body, $table_name, $category_id, $law_id, $act_id, $jurisdiction_id, null, null, null, null, $last_inserted_id);
            $db->commit();
            return back()->with('success', "XML data has been successfully inserted into table '$table_name' with category ID: $category_id! Original filename: $original_filename");
        } catch (\Exception $e) {
            $db->rollback();
            return back()->withErrors(['xmlfile' => $e->getMessage()]);
        }
    }

    // Move insert_data logic to a real method, not a closure
    private function insertLegalDocumentData($db, $xml_element, $table_name, $category_id, $law_id, $act_id, $jurisdiction_id, $parent_id = null, $current_part = null, $current_division = null, $current_sub_division = null, &$last_inserted_id = null)
    {
        static $part_counter = 0;
        static $division_counter = 0;
        static $sub_division_counter = 0;
        foreach ($xml_element as $element) {
            $part = $current_part;
            $division = $current_division;
            $sub_division = '';
            $section = null;
            $sub_section = null;
            $paragraph = null;
            $sub_paragraph = null;
            $title = null;
            $text_content = null;
            $footnote = null;
            $paging = null;
            if ($element->getName() == 'Body') {
                $temp_last_id = $last_inserted_id;
                $this->insertLegalDocumentData($db, $element->children(), $table_name, $category_id, $law_id, $act_id, $jurisdiction_id, $parent_id, $current_part, $current_division, $current_sub_division, $temp_last_id);
                $last_inserted_id = $temp_last_id;
                continue;
            }
            if ($element->getName() == 'HistoricalNote' && $last_inserted_id !== null) {
                continue;
            }
            switch ($element->getName()) {
                case 'Heading':
                    $title = (string)$element->TitleText;
                    if ($element['level'] == '1') {
                        $part_counter++;
                        $part = (string)$part_counter;
                        $current_part = $part;
                        $division_counter = 0;
                        $division = '';
                        $current_division = '';
                        $sub_division_counter = 0;
                        $sub_division = '';
                        $current_sub_division = '';
                    } elseif ($element['level'] == '2') {
                        $division_counter++;
                        $division = (string)$division_counter;
                        $current_division = $division;
                        $sub_division_counter = 0;
                        $sub_division = '';
                        $current_sub_division = '';
                    } elseif ($element['level'] == '3') {
                        $sub_division_counter++;
                        $sub_division = (string)$sub_division_counter;
                        $current_sub_division = $sub_division;
                    }
                    break;
                case 'Section':
                    $part = $current_part;
                    $division = $current_division;
                    $sub_division = $current_sub_division ?? '';
                    $section = (string)$element->Label;
                    $title = (string)$element->MarginalNote;
                    $text_content = trim((string)$element->Text);
                    break;
                case 'Subsection':
                    $part = $current_part;
                    $division = $current_division;
                    $sub_division = $current_sub_division;
                    $section = '';
                    $sub_section = (string)$element->Label;
                    $text_content = trim((string)$element->Text);
                    break;
                case 'Paragraph':
                    $part = $current_part;
                    $division = $current_division;
                    $sub_division = $current_sub_division;
                    $section = '';
                    $sub_section = '';
                    $paragraph = (string)$element->Label;
                    $text_content = trim((string)$element->Text);
                    break;
                case 'Subparagraph':
                    $part = $current_part;
                    $division = $current_division;
                    $sub_division = $current_sub_division;
                    $section = '';
                    $sub_section = '';
                    $paragraph = '';
                    $sub_paragraph = (string)$element->Label;
                    $text_content = trim((string)$element->Text);
                    break;
            }
            if (empty($section) && empty($sub_section) && empty($paragraph) && empty($sub_paragraph) && empty($title) && empty($text_content)) {
                continue;
            }
            $section_id = ($section ?? '') . ($sub_section ?? '') . ($paragraph ?? '') . ($sub_paragraph ?? '');
            $sql = "INSERT INTO $table_name (
                category_id, parent_id, part, division, sub_division, section, sub_section, 
                paragraph, sub_paragraph, section_id, title, text_content, footnote,
                paging, law_id, act_id, jurisdiction_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param(
                "iissssssssssssiii",
                $category_id,
                $parent_id,
                $part,
                $division,
                $sub_division,
                $section,
                $sub_section,
                $paragraph,
                $sub_paragraph,
                $section_id,
                $title,
                $text_content,
                $footnote,
                $paging,
                $law_id,
                $act_id,
                $jurisdiction_id
            );
            $stmt->execute();
            $last_inserted_id = $db->insert_id;
            if ($element->children()->count() > 0) {
                $temp_last_id = $last_inserted_id;
                $this->insertLegalDocumentData($db, $element->children(), $table_name, $category_id, $law_id, $act_id, $jurisdiction_id, $last_inserted_id, $current_part, $current_division, $current_sub_division, $temp_last_id);
                $last_inserted_id = $temp_last_id;
            }
        }
    }
}
