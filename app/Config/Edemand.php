<?php

namespace Config;

class Edemand extends \CodeIgniter\Config\BaseConfig
{
    public $permissions  = [
        'orders' =>  array('read', 'update', 'delete'),
        'categories' =>  array('create', 'read', 'update', 'delete'),
        'subscription' =>  array('create', 'read', 'update', 'delete'),
        'sliders' =>  array('create', 'read', 'update', 'delete'),
        'tax' => array('create', 'read', 'update', 'delete'),
        'services' => array('create', 'read', 'update', 'delete'),
        'promo_code' => array('create', 'read', 'update', 'delete'),
        'featured_section' => array('create', 'read', 'update', 'delete'),
        'partner' => array('create', 'read', 'update', 'delete'),
        'customers' => array('read', 'update'),
        'send_notification' => array('create', 'read', 'delete'),
        'faq' => array('create', 'read', 'update', 'delete'),
        'system_update' => array('update'),
        'settings' => array('create', 'read', 'update'),
        'system_user' => array('create', 'read', 'update', 'delete'),
    ];
}
