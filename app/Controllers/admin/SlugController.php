<?php

namespace App\Controllers\admin;

class SlugController extends Admin
{
    protected $user_details = null;
    
    public function __construct()
    {
        helper('api');
        helper("function");
        helper('ResponceServices');
    }
    
    public function category()
    {
        $db = \Config\Database::connect();
        $categories = $db->table('categories')
            ->select('id, name')
            ->where('slug','')->orWhere('slug',null)
            ->get()
            ->getResultArray();
            
        // print_r($db->getLastQuery());
        if (empty($categories)) {
            return response_helper('Category not found', true);
        }
        
        foreach($categories as $category){
            $slug = url_title($category['name'], '-', true);
            $slug = generate_unique_slug($slug,'categories');

            $db->table('categories')
            ->where('id', $category['id'])
            ->update(['slug' => $slug]);
        
        }
        
        return response_helper('Category slug generated successfully');
    }
    
    public function partner()
    {
        $db = \Config\Database::connect();
        $partners = $db->table('partner_details pd')
            ->select('pd.*,u.username,pd.slug as slug')
            ->join('users u', 'pd.partner_id = u.id')
            ->where('pd.slug','')->orWhere('pd.slug',null)
            ->get()
            ->getResultArray();
            
        if (empty($partners)) {
            return response_helper('Provider not found', true);
        }
        
        foreach($partners as $partner){
            $slug = url_title($partner['username'], '-', true);
            $slug = generate_unique_slug($slug,'partner_details');

            $db->table('partner_details')
            ->where('partner_id', $partner['id'])
            ->update(['slug' => $slug]);            
        }
        return response_helper('Provider slug generated successfully');
    }

    public function service()
    {
        $db = \Config\Database::connect();
        $services = $db->table('services')
            ->where('slug','')->orWhere('slug',null)
            ->get()
            ->getResultArray();
            
        if (empty($services)) {
            return response_helper('Services not found', true);
        }
        
        foreach($services as $service){
            $slug = url_title($service['title'], '-', true);
            $slug = generate_unique_slug($slug,'services');

            $db->table('services')
            ->where('id', $service['id'])
            ->update(['slug' => $slug]);            
        }
        return response_helper('Service slug generated successfully');
    }
} 