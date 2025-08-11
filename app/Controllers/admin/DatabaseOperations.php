<?php
namespace App\Controllers\admin;
use Config\Database;
class DatabaseOperations extends Admin
{
    protected $superadmin,$validation,$db; // No type declaration

    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        helper('ResponceServices');
        helper('api');
        $this->superadmin = $this->session->get('email');

    }
    public function index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $result = checkModificationInDemoMode($this->superadmin);
        if ($result !== true) {
            $_SESSION['toastMessage'] = labels($result['message'], $result['message']);
            $_SESSION['toastMessageType'] = 'error';
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/dashboard')->withCookies();
        }

        $db = Database::connect();
        $tables = $db->listTables();
        if (empty($tables)) {
            return ErrorResponse("No tables found in the database to backup.", true, [], [], 200, csrf_token(), csrf_hash());
        }
        $backupFileName = 'database_backup_' . date("Y-m-d-H-i-s") . '.sql';
        $backupFile = WRITEPATH . 'backups/' . $backupFileName;
        if (!is_dir(WRITEPATH . 'backups')) {
            mkdir(WRITEPATH . 'backups', 0777, true);
        }
        $handle = fopen($backupFile, 'w+');
        foreach ($tables as $table) {
            fwrite($handle, "/* Table structure for table `$table` */\n\n");
            $query = $db->query("SHOW CREATE TABLE $table");
            $row = $query->getRowArray();
            $createTable = array_values($row)[1] . ";\n\n";
            fwrite($handle, $createTable);
            fwrite($handle, "/* Dumping data for table `$table` */\n\n");
            $query = $db->table($table)->get();
            foreach ($query->getResultArray() as $row) {
                $keys = array_keys($row);
                $values = array_map(function ($value) use ($db) {
                    return $db->escape($value);
                }, array_values($row));
                $sql = "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $values) . ");\n";
                fwrite($handle, $sql);
            }
            fwrite($handle, "\n\n");
        }
        fclose($handle);
        if (file_exists($backupFile)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backupFile));
            flush(); 
            readfile($backupFile);
            // Delete the file after download (optional)
            unlink($backupFile);
            exit;
        } else {
            return ErrorResponse("Backup file not created", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function clean_database_index()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        setPageInfo($this->data, labels('clean_database', 'Clean Database') . ' | ' . labels('admin_panel', 'Admin Panel'), 'clean_database');
        $db = db_connect();
        $tables = $db->listTables();
        $tableInfo = [];
        foreach ($tables as $table) {
            if ($table == 'settings' || $table == 'updates' || $table == "user_permissions" ||  $table == "migrations"  || $table == "themes" || $table == "groups" || $table == "email_templates" || $table == "languages") {
                continue;
            }
            $query = $db->query("SELECT COUNT(*) AS total FROM `$table`");
            $result = $query->getRow();
            $totalRecords = $result->total;
            $tableInfo[] = [
                'table' => $table,
                'total_records' => $totalRecords
            ];
        }
        $this->data['tables'] = $tableInfo;
        return view('backend/admin/template', $this->data);
    }
    public function  clean_database_tables()
    {
        $request = service('request');
        $tables_to_clean = $request->getPost('tables_to_clean');
        $db = \Config\Database::connect();
        if (!empty($tables_to_clean) && is_array($tables_to_clean)) {
            foreach ($tables_to_clean as $table_name) {
                if($table_name=="addresses"){
                    $requiredTables = ["order_services", "orders"];
                    if (count(array_intersect($requiredTables, $tables_to_clean)) !== count($requiredTables)) {
                        return ErrorResponse("address table is used in the following tables, so you must clean them as well: " . json_encode($requiredTables), true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
                if ($table_name == "partner_details") {
                    $requiredTables = ["partner_timings", "payment_request", "services", "settlement_history"];
                    if (count(array_intersect($requiredTables, $tables_to_clean)) !== count($requiredTables)) {
                        return ErrorResponse("partner_details table is used in the following tables, so you must clean them as well: " . json_encode($requiredTables), true, [], [], 200, csrf_token(), csrf_hash());
                    }
                }
                $db->table($table_name)->truncate();
            }
            return successResponse("Table truncate successfully", false, [], [], 200, csrf_token(), csrf_hash());
        } else {
            return ErrorResponse("No table were selected", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
