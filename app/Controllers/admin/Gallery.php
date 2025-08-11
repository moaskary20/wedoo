<?php

namespace App\Controllers\admin;

use Aws\S3\S3Client;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Gallery extends Admin
{
    public $category,  $validation;
    protected $superadmin;

    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
        $this->superadmin = $this->session->get('email');
        helper('ResponceServices');
    }
    public function index()
    {



        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $directoryPaths = [
            FCPATH . '/public/uploads',
            FCPATH . '/public/backend/assets'
        ];
        $not_allowed_folders = ['mpdf', 'tools', 'css', 'fonts', 'js'];
        $folderData = [];
        $settings = get_settings('general_settings', true);
        $file_manager = $settings['file_manager'];


        if ($file_manager == "aws_s3") {
            $response = get_aws_s3_folder_info();

            foreach ($response['data'] as $key => $value) {

                $folderData[] = [
                    'name' => $value['name'],
                    'fileCount' => $value['fileCount'],
                    'path' => $value['path']
                ];
            }
        } else if ($file_manager == "local_server") {
            foreach ($directoryPaths as $directoryPath) {
                $folders = array_filter(glob($directoryPath . '/*'), 'is_dir');
                foreach ($folders as $folder) {
                    $publicPos = strpos($folder, '/public/');
                    if ($publicPos !== false) {
                        $pathIncludingPublic = substr($folder, $publicPos);
                    } else {
                        $pathIncludingPublic = $folder;
                    }
                    $folderName = basename($folder);
                    if (!in_array($folderName, $not_allowed_folders)) {
                        $files = glob($folder . '/*');
                        $files = array_filter($files, function ($file) {
                            return !preg_match('/\.(txt|html)$/i', $file);
                        });
                        $fileCount = count($files);
                        $folderData[] = [
                            'name' => $folderName,
                            'fileCount' => $fileCount,
                            'path' => $pathIncludingPublic
                        ];
                    }
                }
            }
        }

        $this->data['folders'] = $folderData;
        setPageInfo($this->data, labels('gallery', 'Gallery') . ' | ' . labels('admin_panel', 'Admin Panel'), 'gallery');
        return view('backend/admin/template', $this->data);
    }
    public function GetGallaryFiles()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return redirect('admin/login');
        }
        $uri = service('uri');
        $segments = $uri->getSegments();
        $galleryIndex = array_search('get-gallery-files', $segments);

        if ($galleryIndex !== false && $galleryIndex + 1 < count($segments)) {
            $new_path = implode('/', array_slice($segments, $galleryIndex + 1));
        } else {
            $new_path = '';
        }
        $settings = get_settings('general_settings', true);
        $file_manager = $settings['file_manager'];


        if (count($segments) >= 3) {
            $folder_name = end($segments);

            if ($file_manager == "aws_s3") {

                $files = get_aws_s3_folder_files($folder_name);

                foreach ($files['data'] as $key => $file) {


                    $filesData[] = [
                        'name' => $file['name'],
                        'type' => $file['type'],
                        'size' => $file['size'],
                        'full_path' => $file['full_path'],
                        'path' => $file['path'],
                        'disk' => 'aws_s3'
                    ];
                }
            } else if ($file_manager == 'local_server') {
                $basePath = FCPATH;
                $folderPath = rtrim($basePath, '/') . '/' . $new_path;
                $files = glob($folderPath . '/*');
                $filesData = [];
                foreach ($files as $file) {
                    $fileInfo = pathinfo($file);
                    $fileType = mime_content_type($file);

                    $fileSize = filesize($file);
                    $folderName = basename($file);
                    $fullPath = base_url() . '/' . $new_path . '/' . $folderName;
                    $servicePath = $new_path . '/' . $folderName;

                    if ($fileType != "text/html") {
                        $filesData[] = [
                            'name' => $fileInfo['basename'],
                            'type' => $fileType,
                            'size' => $this->formatFileSize($fileSize),
                            'full_path' => $fullPath,
                            'path' => $servicePath,
                            'disk' => 'local_server'

                        ];
                    }
                }
            }
        }

        $this->data['files'] = $filesData;
        $this->data['folder_name'] = $folder_name;
        $this->data['total_files'] = count($filesData);
        $this->data['disk'] = $file_manager;

        $this->data['path'] = ($new_path);
        setPageInfo($this->data, labels('gallery', 'Gallery') . '-' . labels($folder_name, $folder_name) . ' | ' . labels('admin_panel', 'Admin Panel'), 'gallery_files');
        return view('backend/admin/template', $this->data);
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

    public function downloadAll()
    {
        if (!$this->isLoggedIn || !$this->userIsAdmin) {
            return $this->response->setStatusCode(403)->setJSON(['error' => labels('unauthorized', 'Unauthorized')]);
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
                        'error' => labels('aws_config_missing', 'AWS configuration missing')
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
                    return $this->response->setStatusCode(500)->setJSON(['error' => labels('couldnt_create_zip_file', 'Could not create zip file')]);
                }
            } catch (Exception $e) {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'AWS Error: ' . $e->getMessage()
                ]);
            }
        } else if ($file_manager == 'local_server') {
            $folderPath = FCPATH . $full_path;

            if (!is_dir($folderPath) || strpos(realpath($folderPath), FCPATH) !== 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => labels('invalid_folder', 'Invalid folder')]);
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
                return $this->response->setStatusCode(500)->setJSON(['error' => labels('couldnt_create_zip_file', 'Could not create zip file')]);
            }
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => labels('invalid_file_manager_type', 'Invalid file manager type')]);
        }
    }

    // Helper function to recursively delete directory
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        return rmdir($dir);
    }
}
