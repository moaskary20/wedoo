<?php
namespace App\Models;
use CodeIgniter\Model;
class Featured_sections_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'sections';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['title', 'section_type', 'category_ids', 'partners_ids','status','limit','rank','banner_type','banner_url','app_banner_image','web_banner_image','description'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    public $base, $admin_id, $db;
}
