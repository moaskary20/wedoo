<?php
namespace App\Controllers\admin;
use App\Models\Email_model;
class SendEmail extends Admin
{
    public   $validation, $faqs, $creator_id;
    protected $superadmin; 
    protected Email_model $email;
    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->email = new Email_model();
        $this->validation = \Config\Services::validation();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
        helper('ResponceServices');
    }
    public function index()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $this->data['users'] = fetch_details('users', [], ['id', 'username']);
            $this->data['partners'] = fetch_details('partner_details', []);
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', "2");
            if (isset($_GET['customer_filter']) && $_GET['customer_filter'] != '') {
                $builder->where('u.active',  $_GET['customer_filter']);
            }
            $customers = $builder->get()->getResultArray();
            $this->data['customers'] =   $customers;
            setPageInfo($this->data, labels('Send Email', 'Send Email') . ' | ' . labels('admin_panel', 'Admin Panel'), 'send_emails');
            return view('backend/admin/template', $this->data);
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/SendEmail.php - index()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function send_email()
    {
        try {
            $email_settings = get_settings('email_settings', true);
            $company_settings = get_settings('general_settings', true);
            $config = [
                'protocol' => 'smtp',
                'SMTPHost' => $email_settings['smtpHost'] ?? '',
                'SMTPPort' => $email_settings['smtpPort'] ?? 587,
                'SMTPUser' => $email_settings['smtpUsername'] ?? '',
                'SMTPPass' => $email_settings['smtpPassword'] ?? '',
                'SMTPCrypto' => 'tls',
                'mailType' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n"
            ];
            $email = \Config\Services::email($config);
            $email->setMailType('html');
            $from_email = $email_settings['smtpUsername'];
            $from_name = $company_settings['company_title'];
            $rules = [
                'subject' => ['rules' => 'required|trim', 'errors' => ['required' => 'Please enter subject']],
                'template' => ['rules' => 'required|trim', 'errors' => ['required' => 'Please enter template content']]
            ];
            $this->validation->setRules($rules);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                return ErrorResponse($errors, true, [], [], 200, csrf_token(), csrf_hash());
            }
            $template = $this->request->getPost('template');
            $subject = $this->request->getPost('subject');
            $type = $this->request->getPost('email_user_type');
            $bcc = $this->request->getPost('bcc');
            $cc = $this->request->getPost('cc');
            $user_ids = [];
            if ($type == "provider") {
                $user_ids = $this->request->getPost('provider_id');
                if (!is_array($user_ids) || empty($user_ids)) {
                    return ErrorResponse(['provider_id' => labels('Please select provider', 'Please select provider')], true, [], [], 200, csrf_token(), csrf_hash());
                }
            } elseif ($type == "customer") {
                $user_ids = $this->request->getPost('customer_id');
                if (!is_array($user_ids) || empty($user_ids)) {
                    return ErrorResponse(['customer_id' => labels('Please select customer', 'Please select customer')], true, [], [], 200, csrf_token(), csrf_hash());
                }
            }
            $users = fetch_details("users", [], ['email', 'id', 'username'], "", 0, 'id', "DESC", 'id', $user_ids);
            foreach ($users as $user) {
                $email->clear();
                $email->setFrom($from_email, $from_name)
                    ->setTo($user['email'])
                    ->setSubject($subject)
                    ->setMailType('html');
                if (isset($_POST['bcc'][0]) && !empty($_POST['bcc'][0])) {
                    $bcc_emails = $this->processBccEmails($_POST['bcc']);
                    if (!empty($bcc_emails)) {
                        $email->setBCC($bcc_emails);
                    }
                }
                if (isset($_POST['cc'][0]) && !empty($_POST['cc'][0])) {
                    $cc_emails = $this->processCcEmails($_POST['cc']);
                    if (!empty($cc_emails)) {
                        $email->setCC($cc_emails);
                    }
                }
                $processed_template = $this->processEmailTemplate($template, $user, $company_settings);
                $processed_template = $this->processInlineImages($email, $processed_template);
                $processed_template = html_entity_decode($processed_template);
                log_message('info', 'Processed email template: ' . $processed_template);
                $email->setMessage($processed_template);
                if (!$email->send()) {
                    log_message('error', 'Email sending failed for user ID: ' . $user['id'] . ' - ' . $email->printDebugger(['headers']));
                    continue;
                }
            }
            $email_data = [
                'content' => $template,
                'type' => $type,
                'bcc' => json_encode($bcc),
                'cc' => json_encode($cc),
                'subject' => $subject,
                'user_id' => json_encode($user_ids)
            ];
            insert_details($email_data, 'emails');
            return successResponse("Emails sent successfully", false, [], [], 200, csrf_token(), csrf_hash());
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage() . "\n" . $th->getTraceAsString());
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    private function processBccEmails($bcc_data)
    {
        $bcc = [];
        if (!empty($bcc_data[0])) {
            $val = explode(',', str_replace([']', '['], '', $bcc_data[0]));
            foreach ($val as $s) {
                $email = json_decode($s, true);
                if (isset($email['value']) && filter_var($email['value'], FILTER_VALIDATE_EMAIL)) {
                    $bcc[] = $email['value'];
                }
            }
        }
        return $bcc;
    }
    private function processCcEmails($cc_data)
    {
        $cc = [];
        if (!empty($cc_data[0])) {
            $val = explode(',', str_replace([']', '['], '', $cc_data[0]));
            foreach ($val as $s) {
                $email = json_decode($s, true);
                if (isset($email['value']) && filter_var($email['value'], FILTER_VALIDATE_EMAIL)) {
                    $cc[] = $email['value'];
                }
            }
        }
        return $cc;
    }
    private function processEmailTemplate($template, $user, $settings)
    {
        $replacements = [
            '[[unsubscribe_link]]' => base_url('unsubscribe_link/' . unsubscribe_link_user_encrypt($user['id'], $user['email'])),
            '[[user_id]]' => $user['id'],
            '[[user_name]]' => $user['username'],
            '[[company_name]]' => $settings['company_title'],
            '[[site_url]]' => base_url(),
            '[[company_contact_info]]' => get_settings('contact_us', true)['contact_us'] ?? '',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    private function processInlineImages($email, $template)
    {
        preg_match_all('/<img[^>]+src=["\'](.*?)["\'][^>]*>/i', $template, $matches);
        $imagePaths = $matches[1];
        foreach ($imagePaths as $imagePath) {
            if (file_exists($imagePath)) {
                $email->attach($imagePath);
                $cid = $email->setAttachmentCID(basename($imagePath));
                $template = str_replace($imagePath, "cid:$cid", $template);
            }
        }
        return $template;
    }
    public function list()
    {
        try {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $data = $this->email->list(false, $search, $limit, $offset, $sort, $order);
            return $data;
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/SendEmail.php - list()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function delete_email()
    {
        try {
            if (!$this->isLoggedIn || !$this->userIsAdmin) {
                return redirect('admin/login');
            }
            $result = checkModificationInDemoMode($this->superadmin);
            if ($result !== true) {
                return $this->response->setJSON($result);
            }
            $id = $this->request->getPost('id');
            $db      = \Config\Database::connect();
            $builder = $db->table('emails');
            if ($builder->delete(['id' => $id])) {
                return successResponse("Email deleted successfully", false, [], [], 200, csrf_token(), csrf_hash());
            } else {
                return ErrorResponse("An error occured during deleting this item", true, [], [], 200, csrf_token(), csrf_hash());
            }
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/SendEmail.php - delete_email()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
    public function unsubscribe_link_view()
    {
        $uri = service('uri');
        $data = $uri->getSegments()[1];
        setPageInfo($this->data, labels('Unsubscribe Email', 'Unsubscribe Email') . ' | ' . labels('admin_panel', 'Admin Panel'), 'unsubscribe_email');
        return view('/backend/admin/pages/unsubscribe_email.php', $this->data);
    }
    public function unsubscription_email_operation()
    {
        try {
            $decrypted = unsubscribe_link_user_decrypt($_POST['data']);
            $user_id = $decrypted[0];
            $email = $decrypted[1];
            $user = fetch_details('users', ['id' => $user_id, 'email' => $email], ['id']);
            if (!empty($user)) {
                $update = update_details(['unsubscribe_email' => 1], ['id' => $user_id, 'email' => $email], 'users');
                if ($update) {
                    $successMessage = labels('You have successfully unsubscribed', 'You have successfully unsubscribed');
                    session()->setFlashdata('success', $successMessage);
                } else {
                    $errorMessage = labels('Failed to unsubscribe. Please try again.', 'Failed to unsubscribe. Please try again') . '.';
                    session()->setFlashdata('error', $errorMessage);
                }
            } else {
                $errorMessage = labels('Invalid user or email', 'Invalid user or email');
                session()->setFlashdata('error', $errorMessage);
            }
            return redirect()->back();
        } catch (\Throwable $th) {
            log_the_responce($th, date("Y-m-d H:i:s") . '--> app/Controllers/admin/SendEmail.php - unsubscription_email_operation()');
            return ErrorResponse("Something Went Wrong", true, [], [], 200, csrf_token(), csrf_hash());
        }
    }
}
