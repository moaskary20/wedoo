<?php
namespace App\Models;
use CodeIgniter\Model;
class Enquiries_model extends Model
{
    protected $table = 'enquiries';
    protected $allowedFields = ['id', 'customer_id', 'title', 'status', 'userType', 'date', 'create_date', 'provider_id'];
    }
