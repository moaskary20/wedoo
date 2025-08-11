<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Language extends BaseController
{
    // public function index($lang)
    // {
    //     $session = session();
    //     $session->remove('lang');
    //     // $session->remove('is_rtl');
    //     $session->set('lang', $lang);
    //     $fetch_lang=fetch_details('languages',['code'=>$lang],['is_rtl']);
    //     $session->set('is_rtl',   $fetch_lang[0]['is_rtl']);
    //     $url = base_url();
    //     return redirect()->to($url);
    // }
    public function index($lang)
    {
        $session = session();
        $session->remove('lang');
        $session->set('lang', $lang);
        // Fetch language details including is_rtl from database
        $fetch_lang = fetch_details('languages', ['code' => $lang], ['is_rtl']);
        // Check if fetch_details returned a valid result
        if (!empty($fetch_lang) && isset($fetch_lang[0]['is_rtl'])) {
            $is_rtl = $fetch_lang[0]['is_rtl'];
            $session->set('is_rtl', $is_rtl);
            // Prepare the response data
            $response = [
                'is_rtl' => $is_rtl,
                'language' => $lang
            ];
            return $this->response->setJSON($response);
        } else {
            // Handle case where fetch_details did not return expected data
            // For example, redirect back with an error message or default value
            return redirect()->back()->with('error', 'Failed to fetch language details.');
        }
    }
    public function updateIsRtl()
    {

        
        $session = \Config\Services::session();
        $request = \Config\Services::request();
        $lang = $request->getPost('language');
        $is_rtl = $request->getPost('is_rtl');

        if ($is_rtl !== null && $lang != null) {
            $session->set('is_rtl', $is_rtl);
            $session->remove('lang');
            $session->set('lang', $lang);
            $language = \Config\Services::language();
            $language->setLocale($lang);
            echo 'Session updated';
        } else {
            echo 'No value received';
        }
    }
}
