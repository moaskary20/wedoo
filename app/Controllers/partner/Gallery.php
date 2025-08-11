<?php

namespace App\Controllers\partner;

use App\Models\Partners_model;
use Aws\S3\S3Client;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Gallery extends Partner
{
    public  $validations, $db;
    protected Partners_model $partner; // Declare the $partner property with the correct type

    public function __construct()
    {
        parent::__construct();
        $this->db      = \Config\Database::connect();
    }
    // public function index()
    // {
    //     if (!$this->isLoggedIn) {
    //         return redirect('partner/login');
    //     }
    //     $user_id = $this->ionAuth->user()->row()->id;
    //     setPageInfo($this->data, 'Gallery | Provider Panel', 'gallery');
    //     $directoryPaths = [
    //         FCPATH . '/public/uploads',
    //         FCPATH . '/public/backend/assets'
    //     ];
    //     $not_allowed_folders = [
    //         'mpdf', 'tools', 'css', 'fonts', 'js', 'categories', 'chat_attachement', 'languages', 'ratings', 'media', 'notification',
    //         'offers', 'provider_bulk_upload', 'site', 'sliders', 'users',
    //         'img', 'images', 'web_settings', 'chat_attachment', 'promocodes', 'provider_bulk_file', 'service_bulk_upload'
    //     ];
    //     $partner = fetch_details('partner_details', ['partner_id' => $user_id], ['banner', 'national_id', 'passport', 'address_id']);
    //     $services = fetch_details('services', ['user_id' => $user_id], ['image', 'other_images', 'files']);
    //     $orders = fetch_details('orders', ['partner_id' => $user_id], ['work_started_proof', 'work_completed_proof']);
    //     // Get partner files
    //     $partnerFiles = [];
    //     $fields = [
    //         'partner_details' => ['banner', 'national_id', 'passport', 'address_id'],
    //         'services' => ['image', 'other_images', 'files'],
    //         'orders' => ['work_started_proof', 'work_completed_proof']
    //     ];
    //     foreach ($fields as $type => $typeFields) {
    //         $data = $type === 'partner_details' ? [$partner[0]] : ($$type ?? []);
    //         foreach ($data as $item) {
    //             foreach ($typeFields as $field) {
    //                 if (!empty($item[$field])) {
    //                     $files = json_decode($item[$field], true);
    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         $files = explode(',', $item[$field]);
    //                     }
    //                     $partnerFiles = array_merge($partnerFiles, $files);
    //                 }
    //             }
    //         }
    //     }
    //     $partnerFiles = array_unique(array_filter($partnerFiles, 'strlen'));
    //     // Get folder data
    //     $folderData = [];
    //     foreach ($directoryPaths as $directoryPath) {
    //         $folders = array_filter(glob($directoryPath . '/*'), 'is_dir');
    //         foreach ($folders as $folder) {
    //             $publicPos = strpos($folder, '/public/');
    //             $pathIncludingPublic = $publicPos !== false ? substr($folder, $publicPos) : $folder;
    //             $folderName = basename($folder);
    //             if (!in_array($folderName, $not_allowed_folders)) {
    //                 $files = glob($folder . '/*');
    //                 $normalizedPartnerFiles = array_map(function ($file) {
    //                     return ltrim(str_replace(FCPATH, '', $file), '/');
    //                 }, $partnerFiles);
    //                 $partnerFolderFiles = array_filter($files, function ($file) use ($normalizedPartnerFiles) {
    //                     $normalizedFile = ltrim(str_replace(FCPATH, '', $file), '/');
    //                     return in_array($normalizedFile, $normalizedPartnerFiles) && file_exists($file) && !preg_match('/\.(txt|html)$/i', $file);
    //                 });
    //                 $fileCount = count($partnerFolderFiles);
    //                 if ($fileCount > 0) {
    //                     $folderData[] = [
    //                         'name' => $folderName,
    //                         'path' => $pathIncludingPublic,
    //                         'file_count' => $fileCount
    //                     ];
    //                 }
    //             }
    //         }
    //     }
    //     $this->data['folders'] = $folderData;
    //     $this->data['users'] = fetch_details('users', ['id' => $user_id], ['company']);
    //     return view('backend/partner/template', $this->data);
    // }
    // public function index()
    // {
    //     if (!$this->isLoggedIn) {
    //         return redirect('partner/login');
    //     }

    //     $user_id = $this->ionAuth->user()->row()->id;
    //     setPageInfo($this->data, 'Gallery | Provider Panel', 'gallery');
    //     $settings = get_settings('general_settings', true);
    //     $file_manager = $settings['file_manager'];
    //     if ($file_manager == "aws_s3") {
    //         $not_allowed_folders = [
    //             'mpdf',
    //             'tools',
    //             'css',
    //             'fonts',
    //             'js',
    //             'categories',
    //             'chat_attachement',
    //             'languages',
    //             'ratings',
    //             'media',
    //             'notification',
    //             'offers',
    //             'provider_bulk_upload',
    //             'site',
    //             'sliders',
    //             'users',
    //             'img',
    //             'images'
    //         ];

    //         // Get all partner files from database
    //         $partner = fetch_details(
    //             'partner_details',
    //             ['partner_id' => $user_id],
    //             ['banner', 'national_id', 'passport', 'address_id']
    //         );
    //         $services = fetch_details(
    //             'services',
    //             ['user_id' => $user_id],
    //             ['image', 'other_images', 'files']
    //         );
    //         $orders = fetch_details(
    //             'orders',
    //             ['partner_id' => $user_id],
    //             ['work_started_proof', 'work_completed_proof']
    //         );

    //         // Collect all files in one array
    //         $partnerFiles = [];

    //         // Add partner files
    //         foreach (['banner', 'national_id', 'passport', 'address_id'] as $field) {
    //             if (!empty($partner[0][$field])) {
    //                 $files = json_decode($partner[0][$field], true);
    //                 if (json_last_error() !== JSON_ERROR_NONE) {
    //                     $files = explode(',', $partner[0][$field]);
    //                 }
    //                 if (is_array($files)) {
    //                     foreach ($files as $file) {
    //                         $partnerFiles[] = $field . '/' . $file; // Use the field name as the folder prefix
    //                     }
    //                 }
    //             }
    //         }

    //         // Add service files
    //         foreach ($services as $service) {
    //             foreach (['image', 'other_images', 'files'] as $field) {
    //                 if (!empty($service[$field])) {
    //                     $files = json_decode($service[$field], true);
    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         $files = explode(',', $service[$field]);
    //                     }
    //                     if (is_array($files)) {
    //                         foreach ($files as $file) {
    //                             $partnerFiles[] = 'service/' . $file; // Prefix with 'service'
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         // Add order files
    //         foreach ($orders as $order) {
    //             foreach (['work_started_proof', 'work_completed_proof'] as $field) {
    //                 if (!empty($order[$field])) {
    //                     $files = json_decode($order[$field], true);
    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         $files = explode(',', $order[$field]);
    //                     }
    //                     if (is_array($files)) {
    //                         foreach ($files as $file) {
    //                             $partnerFiles[] = 'order/' . $file; // Prefix with 'order'
    //                         }
    //                     }
    //                 }
    //             }
    //         }


    //         // Remove duplicates and empty entries
    //         $partnerFiles = array_unique(array_filter($partnerFiles));

    //         // Get file details from S3
    //         $fileDetails = [];
    //         foreach ($partnerFiles as $filePath) {
    //             // Skip files in not allowed folders
    //             $folder = explode('/', $filePath)[0];
    //             if (in_array($folder, $not_allowed_folders)) {
    //                 continue;
    //             }

    //             // Get file details from S3
    //             $result = get_aws_s3_file($filePath);

    //             echo "<pre>";
    //             print_R($result['data']);
    //             die;
    //             if (!$result['error']) {
    //                 // $fileDetails[] = $result['data'];
    //             }
    //         }
    //         die;

    //         // Sort files by last modified date (newest first)
    //         usort($fileDetails, function ($a, $b) {
    //             return strtotime($b['lastModified']) - strtotime($a['lastModified']);
    //         });
    //     } else if ($file_manager == "local_server") {
    //         // Define paths and excluded folders
    //         $directoryPaths = [
    //             FCPATH . '/public/uploads',
    //             FCPATH . '/public/backend/assets'
    //         ];

    //         $not_allowed_folders = [
    //             'mpdf',
    //             'tools',
    //             'css',
    //             'fonts',
    //             'js',
    //             'categories',
    //             'chat_attachement',
    //             'languages',
    //             'ratings',
    //             'media',
    //             'notification',
    //             'offers',
    //             'provider_bulk_upload',
    //             'site',
    //             'sliders',
    //             'users',
    //             'img',
    //             'images'
    //         ];

    //         // Get all partner files
    //         $partner = fetch_details(
    //             'partner_details',
    //             ['partner_id' => $user_id],
    //             ['banner', 'national_id', 'passport', 'address_id']
    //         );
    //         $services = fetch_details(
    //             'services',
    //             ['user_id' => $user_id],
    //             ['image', 'other_images', 'files']
    //         );
    //         $orders = fetch_details(
    //             'orders',
    //             ['partner_id' => $user_id],
    //             ['work_started_proof', 'work_completed_proof']
    //         );

    //         // Collect all files in one array
    //         $partnerFiles = [];

    //         // Add partner files
    //         foreach (['banner', 'national_id', 'passport', 'address_id'] as $field) {
    //             if (!empty($partner[0][$field])) {
    //                 $files = json_decode($partner[0][$field], true);
    //                 if (json_last_error() !== JSON_ERROR_NONE) {
    //                     $files = explode(',', $partner[0][$field]);
    //                 }
    //                 if (is_array($files)) {
    //                     $partnerFiles = array_merge($partnerFiles, $files);
    //                 }
    //             }
    //         }

    //         // Add service files
    //         foreach ($services as $service) {
    //             foreach (['image', 'other_images', 'files'] as $field) {
    //                 if (!empty($service[$field])) {
    //                     $files = json_decode($service[$field], true);
    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         $files = explode(',', $service[$field]);
    //                     }
    //                     if (is_array($files)) {
    //                         $partnerFiles = array_merge($partnerFiles, $files);
    //                     }
    //                 }
    //             }
    //         }

    //         // Add order files
    //         foreach ($orders as $order) {
    //             foreach (['work_started_proof', 'work_completed_proof'] as $field) {
    //                 if (!empty($order[$field])) {
    //                     $files = json_decode($order[$field], true);
    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         $files = explode(',', $order[$field]);
    //                     }
    //                     if (is_array($files)) {
    //                         $partnerFiles = array_merge($partnerFiles, $files);
    //                     }
    //                 }
    //             }
    //         }

    //         // Remove duplicates and empty entries
    //         $partnerFiles = array_unique(array_filter($partnerFiles));

    //         // Get folder data
    //         $folderData = [];
    //         foreach ($directoryPaths as $directoryPath) {
    //             $folders = array_filter(glob($directoryPath . '/*'), 'is_dir');

    //             foreach ($folders as $folder) {
    //                 $folderName = basename($folder);

    //                 // Skip excluded folders
    //                 if (in_array($folderName, $not_allowed_folders)) {
    //                     continue;
    //                 }

    //                 // Get relative path
    //                 $relativePath = str_replace(FCPATH, '', $folder);

    //                 // Count files in this folder that belong to partner
    //                 $fileCount = 0;
    //                 $files = glob($folder . '/*');

    //                 foreach ($files as $file) {
    //                     $relativeFilePath = ltrim(str_replace(FCPATH, '', $file), '/');
    //                     if (
    //                         in_array($relativeFilePath, $partnerFiles) &&
    //                         file_exists($file) &&
    //                         !preg_match('/\.(txt|html)$/i', $file)
    //                     ) {
    //                         $fileCount++;
    //                     }
    //                 }

    //                 // Add folder if it contains partner files
    //                 if ($fileCount > 0) {
    //                     $folderData[] = [
    //                         'name' => $folderName,
    //                         'path' => $relativePath,
    //                         'file_count' => $fileCount
    //                     ];
    //                 }
    //             }
    //         }
    //     }


    //     // Set view data
    //     $this->data['folders'] = $folderData;
    //     $this->data['users'] = fetch_details('users', ['id' => $user_id], ['company']);

    //     return view('backend/partner/template', $this->data);
    // }
    public function index()
    {
        if (!$this->isLoggedIn) {
            return redirect('partner/login');
        }

        $user_id = $this->ionAuth->user()->row()->id;
        setPageInfo($this->data, 'Gallery | Provider Panel', 'gallery');

        $settings = get_settings('general_settings', true);
        $file_manager = $settings['file_manager'];

        $not_allowed_folders = [
            'mpdf',
            'tools',
            'css',
            'fonts',
            'js',
            'categories',
            'chat_attachement',
            'languages',
            'ratings',
            'media',
            'notification',
            'offers',
            'provider_bulk_upload',
            'site',
            'sliders',
            'users',
            'img',
            'images'
        ];

        $partnerFiles = [];
        $foldersToCheck = [];
        $folderData = [];

        // Fetch files from database
        $partner = fetch_details('partner_details', ['partner_id' => $user_id], ['banner', 'national_id', 'passport', 'address_id']);
        $services = fetch_details('services', ['user_id' => $user_id], ['image', 'other_images', 'files']);
        $orders = fetch_details('orders', ['partner_id' => $user_id], ['work_started_proof', 'work_completed_proof']);

        // Helper function to decode or split files
        $parseFiles = function ($data) {
            $files = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $files = explode(',', $data);
            }
            return is_array($files) ? array_filter($files) : [];
        };

        // Collect partner files
        foreach (['banner', 'national_id', 'passport', 'address_id'] as $field) {
            if (!empty($partner[0][$field])) {
                $files = $parseFiles($partner[0][$field]);
                foreach ($files as $file) {
                    $partnerFiles[] = $field . '/' . $file; // Prefix with folder
                }
            }
        }

        // Collect service files
        foreach ($services as $service) {
            foreach (['image', 'other_images', 'files'] as $field) {
                if (!empty($service[$field])) {
                    $files = $parseFiles($service[$field]);
                    foreach ($files as $file) {
                        $partnerFiles[] = $field . '/' . $file; // Prefix with service folder
                    }
                }
            }
        }

        // Collect order files
        foreach ($orders as $order) {
            foreach (['work_started_proof', 'work_completed_proof'] as $field) {
                if (!empty($order[$field])) {
                    $files = $parseFiles($order[$field]);
                    foreach ($files as $file) {
                        $partnerFiles[] = $field . '/' . $file; // Prefix with order folder
                    }
                }
            }
        }

        // Remove duplicates and empty entries
        $partnerFiles = array_unique(array_filter($partnerFiles));

        // Determine file manager (AWS or Local)
        if ($file_manager == "aws_s3") {
            // Fetch details from AWS S3
            foreach ($partnerFiles as $filePath) {
                $folder = explode('/', $filePath)[0];
                if (in_array($folder, $not_allowed_folders)) {
                    continue;
                }

                // Check if folder data already exists to avoid redundant API calls
                if (!in_array($folder, $foldersToCheck)) {
                    $foldersToCheck[] = $folder;
                    $result = get_aws_s3_folder_info($folder);

                    if (isset($result['data']) && !$result['error']) {
                        foreach ($result['data'] as $value) {
                            $folderData[] = [
                                'name' => $value['name'],
                                'file_count' => $value['fileCount'],
                                'path' => $value['path']
                            ];
                        }
                    }
                }
            }
        } elseif ($file_manager == "local_server") {
            // Local file system directories
            $directoryPaths = [
                FCPATH . '/public/uploads',
                FCPATH . '/public/backend/assets'
            ];

            foreach ($directoryPaths as $directoryPath) {
                // Get directories in the specified path
                $folders = array_filter(glob($directoryPath . '/*'), 'is_dir');

                foreach ($folders as $folder) {
                    $folderName = basename($folder);
                    if (in_array($folderName, $not_allowed_folders)) {
                        continue;
                    }

                    $relativePath = str_replace(FCPATH, '', $folder);
                    $fileCount = 0;

                    // Count files matching criteria
                    foreach (glob($folder . '/*') as $file) {
                        $relativeFilePath = ltrim(str_replace(FCPATH, '', $file), '/');
                        if (
                            in_array($relativeFilePath, $partnerFiles) &&
                            file_exists($file) &&
                            !preg_match('/\.(txt|html)$/i', $file) // Exclude unwanted file types
                        ) {
                            $fileCount++;
                        }
                    }

                    // Add to folder data if files found
                    if ($fileCount > 0) {
                        $folderData[] = [
                            'name' => $folderName,
                            'path' => $relativePath,
                            'file_count' => $fileCount
                        ];
                    }
                }
            }
        }

        // Set view data
        $this->data['folders'] = $folderData;
        $this->data['users'] = fetch_details('users', ['id' => $user_id], ['company']);

        return view('backend/partner/template', $this->data);
    }


    // public function GetGallaryFiles()
    // {
    //     // Ensure the user is logged in
    //     if (!$this->isLoggedIn) {
    //         return redirect('partner/login');
    //     }
    //     $user_id = $this->ionAuth->user()->row()->id;
    //     $uri = service('uri');
    //     $segments = $uri->getSegments();
    //     $settings = get_settings('general_settings', true);
    //     $file_manager = $settings['file_manager'];
    //     $not_allowed_folders = [
    //         'mpdf', 'tools', 'css', 'fonts', 'js', 'categories', 'chat_attachement', 
    //         'languages', 'ratings', 'media', 'notification', 'offers', 'provider_bulk_upload', 
    //         'site', 'sliders', 'users', 'img', 'images'
    //     ];
    //     if($file_manager=="aws_s3"){



    //         $details = fetch_details('services', ['user_id' => $user_id], ['image', 'other_images', 'files']);
    //         $orders = fetch_details('orders', ['partner_id' => $user_id], ['work_started_proof', 'work_completed_proof']);
    //         $details = array_merge($details, $orders);

    //         $getFileNames = function ($field) use ($details) {
    //             return array_reduce($details, function ($carry, $item) use ($field) {
    //                 if (!empty($item[$field])) {
    //                     $files = json_decode($item[$field], true);
    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         $files = explode(',', $item[$field]);
    //                     }
    //                     $carry = array_merge($carry, array_map('basename', $files));
    //                 }
    //                 return $carry;
    //             }, []);
    //         };

    //         $allFiles = array_unique(array_filter(array_merge(
    //             $getFileNames('image'),
    //             $getFileNames('other_images'),
    //             $getFileNames('files'),
    //             $getFileNames('national_id'),
    //             $getFileNames('address_id'),
    //             $getFileNames('banner'),
    //             $getFileNames('passport'),
    //             $getFileNames('work_started_proof'),
    //             $getFileNames('work_completed_proof'),
    //         ), 'strlen'));

    //         $filesData = array_filter($files, function ($file) use ($allFiles) {
    //             return in_array($file['name'], $allFiles) && $file['type'] != "text/html";
    //         });

    //     }else if($file_manager=="local_server"){
    //         $new_path = implode('/', array_slice($segments, array_search('get-gallery-files', $segments) + 1));
    //         $folderPath = rtrim(FCPATH, '/') . '/' . $new_path;

    //         $files = glob($folderPath . '/*');

    //         $details = fetch_details('services', ['user_id' => $user_id], ['image', 'other_images', 'files']);
    //         $orders = fetch_details('orders', ['partner_id' => $user_id], ['work_started_proof', 'work_completed_proof']);
    //         $details = array_merge($details, $orders);
    //         $getFileNames = function ($field) use ($details) {
    //             return array_reduce($details, function ($carry, $item) use ($field) {
    //                 if (!empty($item[$field])) {
    //                     $files = json_decode($item[$field], true);
    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         $files = explode(',', $item[$field]);
    //                     }
    //                     $carry = array_merge($carry, array_map('basename', $files));
    //                 }
    //                 return $carry;
    //             }, []);
    //         };
    //         $allFiles = array_unique(array_filter(array_merge(
    //             $getFileNames('image'),
    //             $getFileNames('other_images'),
    //             $getFileNames('files'),
    //             $getFileNames('national_id'),
    //             $getFileNames('address_id'),
    //             $getFileNames('banner'),
    //             $getFileNames('passport'),
    //             $getFileNames('work_started_proof'),
    //             $getFileNames('work_completed_proof'),
    //         ), 'strlen'));
    //         $filesData = array_map(function ($file) use ($new_path, $allFiles) {
    //             $fileInfo = pathinfo($file);
    //             $fileName = $fileInfo['basename'];
    //             if (in_array($fileName, $allFiles) && mime_content_type($file) != "text/html") {
    //                 return [
    //                     'name' => $fileName,
    //                     'type' => mime_content_type($file),
    //                     'size' => $this->formatFileSize(filesize($file)),
    //                     'full_path' => base_url() . '/' . $new_path . '/' . $fileName,
    //                     'path' => $new_path . '/' . $fileName,
    //                 ];
    //             }
    //         }, $files);
    //     }



    //     $this->data['files'] = array_filter($filesData);
    //     $this->data['folder_name'] = end($segments);
    //     $this->data['total_files'] = count($this->data['files']);
    //     $this->data['path'] = $new_path;
    //     setPageInfo($this->data, 'Gallery-' . $this->data['folder_name'] . ' | Provider Panel', 'gallery_files');
    //     return view('backend/partner/template', $this->data);
    // }
    //     public function GetGallaryFiles()
    // {
    //     // Ensure the user is logged in
    //     if (!$this->isLoggedIn) {
    //         return redirect('partner/login');
    //     }
    //     $user_id = $this->ionAuth->user()->row()->id;
    //     $uri = service('uri');
    //     $segments = $uri->getSegments();
    //     $settings = get_settings('general_settings', true);
    //     $file_manager = $settings['file_manager'];
    //     $not_allowed_folders = [
    //         'mpdf', 'tools', 'css', 'fonts', 'js', 'categories', 'chat_attachement', 
    //         'languages', 'ratings', 'media', 'notification', 'offers', 'provider_bulk_upload', 
    //         'site', 'sliders', 'users', 'img', 'images'
    //     ];

    //     if ($file_manager == "aws_s3") {
    //         if ($file_manager == "aws_s3") {
    //             $files = get_provider_files_from_aws_s3_folder($segments);

    //             $details = [];

    //             $partner = fetch_details('partner_details', ['partner_id' => $user_id], ['passport', 'national_id', 'banner', 'address_id']);
    //             $details = array_merge($details, $partner);

    //             $services = fetch_details('services', ['user_id' => $user_id], ['image', 'other_images', 'files']);
    //             $details = array_merge($details, $services);

    //             $orders = fetch_details('orders', ['partner_id' => $user_id], ['work_started_proof', 'work_completed_proof']);
    //             $details = array_merge($details, $orders);

    //             $new_path = implode('/', array_slice($segments, array_search('get-gallery-files', $segments) + 1));

    //             $carry = [];
    //             foreach ($details as $item) {
    //                 foreach (['passport', 'national_id', 'banner', 'address_id', 'image', 'other_images', 'files', 'work_started_proof', 'work_completed_proof'] as $field) {
    //                     if (!empty($item[$field])) {
    //                         $filesList = json_decode($item[$field], true);
    //                         if (json_last_error() !== JSON_ERROR_NONE) {
    //                             $filesList = explode(',', $item[$field]);
    //                         }
    //                         foreach ($filesList as $file) {
    //                             // $fileName = basename($file);

    //                             // print_R($fileName);
    //                             print_R($file);

    //                             die;
    //                             foreach ($files as $awsFile) {
    //                                 if ($fileName === $awsFile['name']) {
    //                                     $carry[] = [
    //                                         'name' => $fileName,
    //                                         'type' => $awsFile['type'],
    //                                         'size' => $awsFile['size'],
    //                                         'full_path' => $awsFile['full_path'],
    //                                         'path' => $awsFile['path'],
    //                                     ];
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }


    //             // $allFiles = array_unique(array_filter(array_merge($carry), 'strlen'));

    //             $filesData = [];
    //             foreach ($carry as $file) {
    //                 if (in_array($file['name'], $carry) && $file['type'] != "text/html") {
    //                     $filesData[] = $file;
    //                 }
    //             }

    //         } 


    //     } else if ($file_manager == "local_server") {
    //         $new_path = implode('/', array_slice($segments, array_search('get-gallery-files', $segments) + 1));
    //         $folderPath = rtrim(FCPATH, '/') . '/' . $new_path;

    //         $files = glob($folderPath . '/*');

    //         $details = fetch_details('services', ['user_id' => $user_id], ['image', 'other_images', 'files']);
    //         $orders = fetch_details('orders', ['partner_id' => $user_id], ['work_started_proof', 'work_completed_proof']);
    //         $details = array_merge($details, $orders);
    //         $getFileNames = function ($field) use ($details) {
    //             return array_reduce($details, function ($carry, $item) use ($field) {
    //                 if (!empty($item[$field])) {
    //                     $files = json_decode($item[$field], true);
    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         $files = explode(',', $item[$field]);
    //                     }
    //                     $carry = array_merge($carry, array_map('basename', $files));
    //                 }
    //                 return $carry;
    //             }, []);
    //         };
    //         $allFiles = array_unique(array_filter(array_merge(
    //             $getFileNames('image'),
    //             $getFileNames('other_images'),
    //             $getFileNames('files'),
    //             $getFileNames('national_id'),
    //             $getFileNames('address_id'),
    //             $getFileNames('banner'),
    //             $getFileNames('passport'),
    //             $getFileNames('work_started_proof'),
    //             $getFileNames('work_completed_proof'),
    //         ), 'strlen'));
    //         $filesData = array_map(function ($file) use ($new_path, $allFiles) {
    //             $fileInfo = pathinfo($file);
    //             $fileName = $fileInfo['basename'];
    //             if (in_array($fileName, $allFiles) && mime_content_type($file) != "text/html") {
    //                 return [
    //                     'name' => $fileName,
    //                     'type' => mime_content_type($file),
    //                     'size' => $this->formatFileSize(filesize($file)),
    //                     'full_path' => base_url() . '/' . $new_path . '/' . $fileName,
    //                     'path' => $new_path . '/' . $fileName,
    //                 ];
    //             }
    //         }, $files);
    //     }

    //     $this->data['files'] = array_filter($filesData);
    //     $this->data['folder_name'] = end($segments);
    //     $this->data['total_files'] = count($this->data['files']);
    //     $this->data['path'] = $new_path;
    //     setPageInfo($this->data, 'Gallery-' . $this->data['folder_name'] . ' | Provider Panel', 'gallery_files');
    //     return view('backend/partner/template', $this->data);
    // }
    public function GetGallaryFiles()
    {
        // Ensure the user is logged in
        if (!$this->isLoggedIn) {
            return redirect('partner/login');
        }
        $user_id = $this->ionAuth->user()->row()->id;
        $uri = service('uri');
        $segments = $uri->getSegments();
        $settings = get_settings('general_settings', true);
        $file_manager = $settings['file_manager'];
        $not_allowed_folders = [
            'mpdf',
            'tools',
            'css',
            'fonts',
            'js',
            'categories',
            'chat_attachement',
            'languages',
            'ratings',
            'media',
            'notification',
            'offers',
            'provider_bulk_upload',
            'site',
            'sliders',
            'users',
            'img',
            'images'
        ];
        if ($file_manager == "aws_s3") {
            $files = get_provider_files_from_aws_s3_folder($segments);

            $details = [];

            $partner = fetch_details('partner_details', ['partner_id' => $user_id], ['passport', 'national_id', 'banner', 'address_id']);
            $details = array_merge($details, $partner);

            $services = fetch_details('services', ['user_id' => $user_id], ['image', 'other_images', 'files']);
            $details = array_merge($details, $services);

            $orders = fetch_details('orders', ['partner_id' => $user_id], ['work_started_proof', 'work_completed_proof']);
            $details = array_merge($details, $orders);

            $new_path = implode('/', array_slice($segments, array_search('get-gallery-files', $segments) + 1));

            $carry = [];
            foreach ($details as $item) {
                foreach (['passport', 'national_id', 'banner', 'address_id', 'image', 'other_images', 'files', 'work_started_proof', 'work_completed_proof'] as $field) {
                    if (!empty($item[$field])) {
                        $filesList = json_decode($item[$field], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $filesList = explode(',', $item[$field]);
                        }
                        foreach ($filesList as $file) {
                            $fileName = basename($file);
                            foreach ($files as $awsFile) {

                                if ($fileName === $awsFile['name']) {
                                    $carry[] = $fileName;
                                }
                            }
                        }
                    }
                }
            }

            $allFiles = array_unique(array_filter($carry));

            $filesData = [];
            foreach ($files as $file) {
                if (in_array($file['name'], $allFiles) && $file['type'] != "text/html") {
                    $filesData[] = $file;
                }
            }
        } else if ($file_manager == "local_server") {
            $new_path = implode('/', array_slice($segments, array_search('get-gallery-files', $segments) + 1));
            $folderPath = rtrim(FCPATH, '/') . '/' . $new_path;

            $files = glob($folderPath . '/*');

            $details = [];

            $partner = fetch_details('partner_details', ['partner_id' => $user_id], ['passport', 'national_id', 'banner', 'address_id']);
            $details = array_merge($details, $partner);

            $services = fetch_details('services', ['user_id' => $user_id], ['image', 'other_images', 'files']);
            $details = array_merge($details, $services);

            $orders = fetch_details('orders', ['partner_id' => $user_id], ['work_started_proof', 'work_completed_proof']);
            $details = array_merge($details, $orders);

            $carry = [];
            foreach ($details as $item) {
                foreach (['passport', 'national_id', 'banner', 'address_id', 'image', 'other_images', 'files', 'work_started_proof', 'work_completed_proof'] as $field) {
                    if (!empty($item[$field])) {
                        $filesList = json_decode($item[$field], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $filesList = explode(',', $item[$field]);
                        }
                        foreach ($filesList as $file) {
                            $filePath = rtrim(FCPATH, '/') . '/' . $new_path . '/' . basename($file);
                            if (file_exists($filePath)) {
                                $carry[] = basename($file);
                            }
                        }
                    }
                }
            }

            $allFiles = array_unique(array_filter($carry));

            $filesData = [];
            foreach ($files as $file) {
                if (in_array(basename($file), $allFiles) && mime_content_type($file) != "text/html") {
                    $filesData[] = [
                        'name' => basename($file),
                        'type' => mime_content_type($file),
                        'size' => $this->formatFileSize(filesize($file)),
                        'full_path' => base_url() . '/' . $new_path . '/' . basename($file),
                        'path' => $new_path . '/' . basename($file),
                    ];
                }
            }
        }

        $this->data['files'] = array_filter($filesData);
        $this->data['folder_name'] = end($segments);
        $this->data['total_files'] = count($this->data['files']);
        $this->data['path'] = $new_path;
        $this->data['disk'] = $file_manager;

        setPageInfo($this->data, 'Gallery-' . $this->data['folder_name'] . ' | Provider Panel', 'gallery_files');
        return view('backend/partner/template', $this->data);
    }

    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    // public function downloadAll()
    // {
    //     if (!$this->isLoggedIn) {
    //         return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
    //     }
    //     $folder = $this->request->getPost('folder');
    //     $full_path = $this->request->getPost('full_path');
    //     $folderPath = FCPATH . $full_path;
    //     if (!is_dir($folderPath) || strpos(realpath($folderPath), FCPATH) !== 0) {
    //         return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid folder']);
    //     }
    //     $zipName = $folder . '.zip';
    //     $zipPath = FCPATH . 'public/uploads/' . $zipName;
    //     $zip = new ZipArchive();
    //     if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    //         $files = new RecursiveIteratorIterator(
    //             new RecursiveDirectoryIterator($folderPath),
    //             RecursiveIteratorIterator::LEAVES_ONLY
    //         );
    //         foreach ($files as $name => $file) {
    //             if (!$file->isDir()) {
    //                 $filePath = $file->getRealPath();
    //                 $relativePath = substr($filePath, strlen($folderPath) + 1);
    //                 $zip->addFile($filePath, $relativePath);
    //             }
    //         }
    //         $zip->close();
    //         header('Content-Type: application/zip');
    //         header('Content-Disposition: attachment; filename="' . $zipName . '"');
    //         header('Content-Length: ' . filesize($zipPath));
    //         header('Pragma: no-cache');
    //         header('Expires: 0');
    //         readfile($zipPath);
    //         unlink($zipPath);
    //         exit;
    //     } else {
    //         return $this->response->setStatusCode(500)->setJSON(['error' => 'Could not create zip file']);
    //     }
    // }
    public function downloadAll()
    {

        if (!$this->isLoggedIn ) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $folder = $this->request->getPost('folder');
        $full_path = $this->request->getPost('full_path');
        $file_manager = $this->request->getPost('disk');

        if ($file_manager == "aws_s3") {
            try {
                $S3_settings = get_settings('general_settings', true);
                $aws_key = $S3_settings['aws_access_key_id'] ?? '';
                $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
                $region = $S3_settings['aws_region'] ?? 'us-east-1';
                $bucket_name = $S3_settings['aws_bucket'] ?? '';

                if (!$aws_key || !$aws_secret || !$bucket_name || !$region) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'error' => 'AWS configuration missing'
                    ]);
                }

                $s3 = new S3Client([
                    'version' => 'latest',
                    'region'  => $region,
                    'credentials' => [
                        'key'    => $aws_key,
                        'secret' => $aws_secret,
                    ]
                ]);

                // Ensure folder path ends with '/'
                $folder_path = rtrim($full_path, '/') . '/';

                // List all objects in the folder
                $objects = $s3->getPaginator('ListObjectsV2', [
                    'Bucket' => $bucket_name,
                    'Prefix' => $folder_path
                ]);

                // Create temporary directory for zip files
                $temp_dir = FCPATH . 'public/uploads/temp/' . uniqid('s3_');
                if (!is_dir($temp_dir)) {
                    mkdir($temp_dir, 0777, true);
                }

                // Download each file from S3
                foreach ($objects as $result) {
                    foreach ($result['Contents'] as $object) {
                        // Skip if it's the folder itself
                        if ($object['Key'] === $folder_path) {
                            continue;
                        }

                        // Create local directory structure
                        $relative_path = substr($object['Key'], strlen($folder_path));
                        $local_path = $temp_dir . '/' . $relative_path;
                        $local_dir = dirname($local_path);

                        if (!is_dir($local_dir)) {
                            mkdir($local_dir, 0777, true);
                        }

                        // Download file from S3
                        $s3->getObject([
                            'Bucket' => $bucket_name,
                            'Key'    => $object['Key'],
                            'SaveAs' => $local_path
                        ]);
                    }
                }

                // Create ZIP file
                $zipName = $folder . '.zip';
                $zipPath = FCPATH . 'public/uploads/' . $zipName;

                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($temp_dir),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    foreach ($files as $name => $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = substr($filePath, strlen($temp_dir) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }

                    $zip->close();

                    // Clean up temporary directory
                    $this->deleteDirectory($temp_dir);

                    // Send the ZIP file
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . $zipName . '"');
                    header('Content-Length: ' . filesize($zipPath));
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    readfile($zipPath);
                    unlink($zipPath);
                    exit;
                } else {
                    $this->deleteDirectory($temp_dir);
                    return $this->response->setStatusCode(500)->setJSON(['error' => 'Could not create zip file']);
                }
            } catch (Exception $e) {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'AWS Error: ' . $e->getMessage()
                ]);
            }
        } else if ($file_manager == 'local_server') {
            $folderPath = FCPATH . $full_path;

            if (!is_dir($folderPath) || strpos(realpath($folderPath), FCPATH) !== 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid folder']);
            }

            $zipName = $folder . '.zip';
            $zipPath = FCPATH . 'public/uploads/' . $zipName;
            $zip = new ZipArchive();

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($folderPath),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($folderPath) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                $zip->close();
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipName . '"');
                header('Content-Length: ' . filesize($zipPath));
                header('Pragma: no-cache');
                header('Expires: 0');
                readfile($zipPath);
                unlink($zipPath);
                exit;
            } else {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Could not create zip file']);
            }
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Invalid file manager type']);
        }
    }
}
