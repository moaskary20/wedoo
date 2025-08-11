<?php
namespace App\Models;
use CodeIgniter\Model;
class user_permissions_model extends Model
{
    protected $table = 'user_permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id','role','permissions'];
}
