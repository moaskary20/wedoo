<?php
namespace App\Controllers\partner;
use App\Models\Transaction_model;
class Transactions extends Partner
{
    public $partner, $validations, $db;
    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        helper('ResponceServices');
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsPartner) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            setPageInfo($this->data, 'Transactions | Provider Panel', 'transactions');
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
    public function list()
    {
        $model = new Transaction_model();
        return $model->list_transactions(false, '', 10, 0, '', '', ['t.partner_id' => $this->userId]);
    }
}
