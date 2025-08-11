<?php

namespace App\Controllers\admin;

use App\Models\Language_model;

class Languages extends Admin
{

    protected Language_model $langauge;

    public function __construct()
    {
        parent::__construct();
        $this->langauge = new Language_model();
        helper('ResponceServices');
    }
    public function index()
    {


        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $session = session();
        $lang = $session->get('lang');

        if (empty($lang)) {
            $lang = 'en';
        }
        $this->data['code'] = $lang;
        setPageInfo($this->data, labels('language', 'Language') . ' | ' . labels('admin_panel', 'Admin Panel'), 'languages');
        $this->data['languages'] = fetch_details('languages', [], [], null, '0', 'id', 'ASC');
        return view('backend/admin/template', $this->data);
    }
    public function change($lang)
    {

        try {
            $session = session();
            $session->remove('lang');
            $session->set('lang', $lang);
            return redirect()->to("admin/languages/");
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Languages.php - change()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function insert()
    {
        try {

            helper('files');
            helper('filesystem');
            $db = \Config\Database::connect();
            $name = trim($_POST['language_name']);
            $code = $_POST['language_code'];
            $langauge_file = $this->request->getFile('language_json');
            $path = "/public/uploads/languages/";
            $ext = $langauge_file->getExtension();
            $image_name = $code . '.' . $ext;
            $my_lang = $code;
            move_file($langauge_file, $path, $image_name);
            $check = $db->table('languages')->insert($data = ['language' => $name, 'code' => $code, 'is_rtl' => isset($_POST['is_rtl']) ? '1' : '0']);
            if ($check) {
                $data = json_decode(file_get_contents(base_url($path . "/" . $code . ".json")), false);
                $my_lang = $code;


                $langstr = "\$lang['label_language'] = \"$my_lang\";" . "\n";
                $langstr_final = "<?php
                ";
                foreach ($data as $key => $val) {
                    $langstr_final .= "\$lang['$key'] = \"$val\";" . "\n";
                }
                $langstr_final .= 'return $lang;';
                if (!is_dir('./app/Language/' . $my_lang . '/')) {
                    mkdir('./app/Language/' . $my_lang . '/', 0777, true);
                }
                if (file_exists('./app/Language/' . $my_lang . '/Text.php')) {
                    delete_files('./app/Language/' . $my_lang . '/Text.php');
                    write_file('./app/Language/' . $my_lang . '/Text.php', $langstr_final);
                } else {
                    write_file('./app/Language/' . $my_lang . '/Text.php', $langstr_final);
                }
                return successResponse("Language added", false, [], [], 200, csrf_token(), csrf_hash());

                $_SESSION['toastMessage'] = labels('language_added', 'Language added') . "..";
                $_SESSION['toastMessageType'] = 'success';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/languages')->withCookies();
            } else {
                $_SESSION['toastMessage'] = labels('error', 'Error') . "..";
                $_SESSION['toastMessageType'] = 'success';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/languages')->withCookies();
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Languages.php - insert()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function remove()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            $db = \Config\Database::connect();
            $id = $this->request->getVar('id');
            $builder = $db->table('languages');
            $builder->where('id', $id);
            $data = fetch_details("languages", ['id' => $id]);
            if (empty($data)) {
                return redirect('admin/login');
            }
            $code = $data[0]['code'];
            $old_path = "public/uploads/languages/" . $code . '.json';
            if ($code == "en") {
                return ErrorResponse("Default language cannot be removed", true, [], [], 200, csrf_token(), csrf_hash());
            }
            if ($builder->delete()) {
                unlink($old_path);
                delete_directory("app/Language/$code/");
                return successResponse("Langauge Removed successfully", false, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Languages.php - remove()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function language_sample()
    {
        try {
            $filePath = (FCPATH . '/public/test.json');
            $headers = ['Content-Type: application/json'];
            $fileName = 'en.json';
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)->setFileName($fileName);
            } else {
                $_SESSION['toastMessage'] = labels('cant_download', 'Cannot download');
                $_SESSION['toastMessageType'] = 'error';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/languages')->withCookies();
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Languages.php - language_sample()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function language_old()
    {
        try {
            $uri = service('uri');
            $code = $uri->getSegments()[1];
            $filePath = (FCPATH . '/public/uploads/languages/' . $code . '.json');
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null)->setFileName($code . ".json");
            } else {
                $_SESSION['toastMessage'] = labels('cant_download', 'Cannot download');
                $_SESSION['toastMessageType'] = 'error';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/languages')->withCookies();
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Languages.php - language_old()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function list()
    {
        try {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $where = [];
            $from_app = false;
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $from_app = true;
            }
            $data = $this->langauge->list($from_app, $search, $limit, $offset, $sort, $order, $where);
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                if (!empty($data['data'])) {
                    return successResponse("Language fetched successfully", false, $data['data'], [], 200, csrf_token(), csrf_hash());
                } else {
                    return ErrorResponse("Langauge not found on", true, $data['data'], [], 200, csrf_token(), csrf_hash());
                }
            }
            return $data;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Languages.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }

    public function update()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }

            $id = $this->request->getPost('id');
            $db = \Config\Database::connect();
            $builder = $db->table('languages');
            $builder->select('*')->where('id', $id);
            $language_record = $builder->get()->getRow();
            $old_code = $language_record->code;

            if (isset($_POST) && !empty($_POST)) {
                $code = $this->request->getPost('edit_code');
                $name = $this->request->getPost('edit_name');
                $filePath = FCPATH . '/public/uploads/languages/' . $code . '.json';
                $langauge_file = $this->request->getFile('update_language_json');
                $path = "/public/uploads/languages/";
                $oldFilePath = FCPATH . $path . $old_code . '.json';
                $newFilePath = FCPATH . $path . $code . '.json';

                // Check if a new file was uploaded
                if ($langauge_file->isValid() && !$langauge_file->hasMoved()) {
                    // Process uploaded file
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                    $file_name = $code . '.' . $langauge_file->getExtension();
                    $langauge_file->move(FCPATH . $path, $file_name);

                    // Load data from new file
                    $data = json_decode(file_get_contents($newFilePath), false);

                    // Verify JSON validity and write language PHP file
                    // if (is_array($data) || is_object($data)) {
                    //     $langstr_final = "<?php\n";
                    //     foreach ($data as $key => $val) {
                    //         $langstr_final .= "\$lang['$key'] = \"$val\";\n";
                    //     }
                    //     $langstr_final .= 'return $lang;';

                    //     if (!is_dir(APPPATH . 'Language/' . $code . '/')) {
                    //         mkdir('./app/Language/' . $code . '/', 0777, true);
                    //     }

                    //     if (is_dir(APPPATH . 'Language/' . $old_code . '/')) {
                    //         delete_directory("app/Language/$old_code/");
                    //     }

                    //     write_file('./app/Language/' . $code . '/Text.php', $langstr_final);
                    // } else {
                    //     throw new \Exception("Invalid JSON data in $newFilePath.");
                    // }


                    helper('filesystem'); // Ensure file helper is loaded

                    if (is_array($data) || is_object($data)) {
                        $langstr_final = "<?php\n";
                        foreach ($data as $key => $val) {
                            $langstr_final .= "\$lang['$key'] = \"$val\";\n";
                        }
                        $langstr_final .= "return \$lang;";
                    
                        $langPath = APPPATH . 'Language/' . $code . DIRECTORY_SEPARATOR;
                        $oldLangPath = APPPATH . 'Language/' . $old_code . DIRECTORY_SEPARATOR;
                        $filePath = $langPath . 'Text.php';
                    
                        // Create the directory if it doesn't exist
                        if (!is_dir($langPath)) {
                            mkdir($langPath, 0777, true);
                        }
                    
                        write_file('./app/Language/' . $code . '/Text.php', $langstr_final);
                        // // Delete old language directory if it exists
                        // if (is_dir($oldLangPath)) {
                        //     delete_directory($oldLangPath);
                        // }
                    } else {
                        throw new \Exception("Invalid JSON data in $newFilePath.");
                    }
                    

                    // die;
                    
                } else {
                    // No file uploaded, proceed with updating database and renaming the existing file
                    if (file_exists($oldFilePath)) {
                        rename($oldFilePath, $newFilePath);
                    }

                    log_message('info', "File upload not detected. Proceeding with the update using existing file.");
                }

                // Update database record
                $data = [
                    'language' => $name,
                    'code' => $code,
                    'is_rtl' => isset($_POST['is_rtl']) ? '1' : '0'
                ];
                $this->langauge->update($id, $data);

                $_SESSION['toastMessage'] = labels('language_updated_successfully', 'Language updated successfully');
                $_SESSION['toastMessageType'] = 'success';
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/languages')->withCookies();
            }
        } catch (\Throwable $th) {
            throw $th;
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Languages.php - update()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }


    public function store_default_language()
    {

        try {
            $default_language = fetch_details('languages', ['is_default' => '1']);

            if (!empty($default_language)) {
                $language_model = new Language_model();
                $language_model->update($default_language[0]['id'], ['is_default' => 0]);
                $language_model->update($_POST['id'], ['is_default' => '1']);
            }

            $response = [
                'error' => false,
                'message' => labels('default_language_set', 'Default language set') . '.',
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];

            return $this->response->setJSON($response);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/Languages.php - store_default_language()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
