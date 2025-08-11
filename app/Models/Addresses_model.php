<?php

namespace App\Models;

use CodeIgniter\Model;

class Addresses_model  extends Model
{
    protected $table = 'addresses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id ', 'type', 'address', 'area', 'mobile', 'alternate_mobile', 'pincode', 'city_id', 'city', 'landmark', 'state', 'country', 'lattitude', 'longitude', 'is_default'];

    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('addresses a');
        $multipleWhere = [];
        $bulkData = $rows = $tempRow = [];
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        $sort = "a.id";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'a.id') {
                $sort = "a.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        $order = "ASC";
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`a.id`' => $search,
                '`a.type`' => $search,
                '`a.address`' => $search,
                '`a.area`' => $search,
                '`a.mobile`' => $search,
                '`a.alternate_mobile`' => $search,
                '`a.pincode`' => $search,
                '`a.city`' => $search,
                '`a.state`' => $search,
                '`a.country`' => $search,
                '`u.username`' => $search,
            ];
        }
        $address_count = $builder->select('count(a.id) as total')
            ->join('users u', 'u.id=a.user_id');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $address_count = $builder->get()->getResultArray();
        $total = $address_count[0]['total'];
        $builder->select('a.id,a.user_id,a.type,a.address,a.area,a.mobile,a.alternate_mobile,a.pincode,a.city,a.landmark,a.state,a.country,a.lattitude,a.longitude,a.is_default,u.username')
            ->join('users u', 'u.id=a.user_id');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $address_record = [];
        $address_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $rows = [];
        foreach ($address_record as $row) {
            $tempRow = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'type' => $row['type'],
                'address' => $row['address'],
                'city_name' => $row['city'],
                'area' => $row['area'],
                'mobile' => ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ? ((!empty($row['mobile'])) ? 'XXXX' . substr($row['mobile'], 7) : "XXX-XX-XX  ") : $row['mobile'],
                'alternate_mobile' => $row['alternate_mobile'],
                'pincode' => $row['pincode'],
                'city_id' => $row['city'],
                'landmark' => $row['landmark'],
                'state' => $row['state'],
                'country' => $row['country'],
                'lattitude' => $row['lattitude'],
                'longitude' => $row['longitude'],
                'is_default' => $row['is_default']
            ];
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        if ($from_app) {
            $data['total'] = $total;
            $data['data'] = $rows;
            return $data;
        } else {
            return json_encode($bulkData);
        }
    }
}
