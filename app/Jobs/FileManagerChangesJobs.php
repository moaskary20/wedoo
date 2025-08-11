<?php

namespace App\Jobs;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use CodeIgniter\Queue\BaseJob;
use CodeIgniter\Queue\Interfaces\JobInterface;


class FileManagerChangesJobs extends BaseJob implements JobInterface
{
    public function process()
    {

        log_message('error', 'worker started-----------------------------------');
        $file_manager = $this->data['file_manager'];



        if ($file_manager == "aws_s3") {
            $this->uploadToS3();
        } else if ($file_manager == "local_server") {
            $this->downloadFromS3();
        }
    }



    private function downloadFromS3()
    {
        $folders = [
            'categories/' => FCPATH . '/uploads/categories/',
            'become_provider/' => FCPATH . '/uploads/become_provider/',
            'chat_attachment/' => FCPATH . '/uploads/chat_attachment/',
            'feature_section/' => FCPATH . '/uploads/feature_section/',
            'partner/' => FCPATH . '/uploads/partner/',
            'promocodes/' => FCPATH . '/uploads/promocodes/',
            'ratings/' => FCPATH . '/uploads/ratings/',
            'services/' => FCPATH . '/uploads/services/',
            'site/' => FCPATH . '/uploads/site/',
            'sliders/' => FCPATH . '/uploads/sliders/',
            'web_settings/' => FCPATH . '/uploads/web_settings/',
            'address_id/' => FCPATH . '/backend/assets/address_id/',
            'national_id/' => FCPATH . '/backend/assets/national_id/',
            'passport/' => FCPATH . '/backend/assets/passport/',
            'profile/' => FCPATH . '/backend/assets/profile/',
            'profile/' => FCPATH . '/backend/assets/profiles/',
            'provider_work_evidence/' => FCPATH . '/backend/assets/provider_work_evidence/',
            'banner/' => FCPATH . '/backend/assets/banner/',
        ];

        $uploadFolders = [
            FCPATH . '/uploads/categories/',
            FCPATH . '/uploads/become_provider/',
            FCPATH . '/uploads/chat_attachment/',
            FCPATH . '/uploads/feature_section/',
            FCPATH . '/uploads/partner/',
            FCPATH . '/uploads/promocodes/',
            FCPATH . '/uploads/ratings/',
            FCPATH . '/uploads/services/',
            FCPATH . '/uploads/site/',
            FCPATH . '/uploads/sliders/',
            FCPATH . '/uploads/web_settings/',
            FCPATH . '/backend/assets/address_id/',
            FCPATH . '/backend/assets/national_id/',
            FCPATH . '/backend/assets/passport/',
            FCPATH . '/backend/assets/profile/',
            FCPATH . '/backend/assets/profiles/',
            FCPATH . '/backend/assets/provider_work_evidence/',
            FCPATH . '/backend/assets/banner/',
        ];

        foreach ($uploadFolders as $folder) {
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
                chmod($folder, 0755);
            }
        }

        $S3_settings = get_settings('general_settings', true);
        $aws_key = $S3_settings['aws_access_key_id'] ?? '';
        $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
        $bucket = $S3_settings['aws_bucket'] ?? '';
        $region = $S3_settings['aws_region'] ?? 'us-east-1';

        if (!$aws_key || !$aws_secret || !$bucket || !$region) {
            print_r("AWS configuration missing. Please check configuration variables.");
            return;
        }
        $config = [
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $aws_key,
                'secret' => $aws_secret,
            ],
        ];

        $s3 = new S3Client($config);
        foreach ($folders as $s3Folder => $localFolder) {
            try {
                $objects = $s3->listObjectsV2([
                    'Bucket' => $bucket,
                    'Prefix' => $s3Folder
                ]);

                if (empty($objects['Contents'])) {
                    log_message('error', "No files found in the S3 folder: " . $s3Folder);
                    print_r("No files found in the S3 folder: " . $s3Folder);
                    continue;
                }

                if (!is_dir($localFolder)) {
                    mkdir($localFolder, 0777, true);
                }

                foreach ($objects['Contents'] as $object) {
                    $key = $object['Key'];
                    $fileName = basename($key);

                    if (!$fileName) {
                        continue;
                    }

                    $localFilePath = $localFolder . $fileName;

                    // Download each file
                    $s3->getObject([
                        'Bucket' => $bucket,
                        'Key'    => $key,
                        'SaveAs' => $localFilePath,
                    ]);
                }
                log_message('error', "All files downloaded successfully to: " . $localFolder);

                print_r("All files downloaded successfully to: " . $localFolder);
            } catch (AwsException $e) {
                log_message('error', "Error downloading files from S3 folder: " . $s3Folder . " - " . $e->getMessage());

                print_r("Error downloading files from S3 folder: " . $s3Folder . " - " . $e->getMessage());
            }
        }
    }

    // private function uploadToS3()
    // {
    //     $folders = [
    //         'categories/' => FCPATH . '/uploads/categories/',
    //         'become_provider/' => FCPATH . '/uploads/become_provider/',
    //         'chat_attachment/' => FCPATH . '/uploads/chat_attachment/',
    //         'feature_section/' => FCPATH . '/uploads/feature_section/', 
    //         'partner/' => FCPATH . '/uploads/partner/',
    //         'promocodes/' => FCPATH . '/uploads/promocodes/',
    //         'ratings/' => FCPATH . '/uploads/ratings/',
    //         'services/' => FCPATH . '/uploads/services/',
    //         'site/' => FCPATH . '/uploads/site/',
    //         'sliders/' => FCPATH . '/uploads/sliders/',
    //         'web_settings/' => FCPATH . '/uploads/web_settings/',
    //         'address_id/' => FCPATH . '/backend/assets/address_id/',
    //         'national_id/' => FCPATH . '/backend/assets/national_id/',
    //         'passport/' => FCPATH . '/backend/assets/passport/',
    //        'profile/' => [
    //                 FCPATH . '/backend/assets/profile/', 
    //                 FCPATH . '/backend/assets/profiles/' // Now both folders are included
    //             ],
    //         'provider_work_evidence/' => FCPATH . '/backend/assets/provider_work_evidence/',
    //         'banner/' => FCPATH . '/backend/assets/banner/',
    //     ];

    //     // Get AWS configuration
    //     $S3_settings = get_settings('general_settings', true);
    //     $aws_key = $S3_settings['aws_access_key_id'] ?? '';
    //     $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
    //     $bucket = $S3_settings['aws_bucket'] ?? '';
    //     $region = $S3_settings['aws_region'] ?? 'us-east-1';

    //     if (!$aws_key || !$aws_secret || !$bucket || !$region) {
    //         print_r("AWS configuration missing. Please check configuration variables.");
    //         return;
    //     }

    //     $config = [
    //         'region' => $region,
    //         'version' => 'latest',
    //         'credentials' => [
    //             'key'    => $aws_key,
    //             'secret' => $aws_secret,
    //         ],
    //     ];

    //     $s3 = new S3Client($config);

    //     foreach ($folders as $s3Folder => $localFolder) {
    //         try {
    //             if (!is_dir($localFolder)) {
    //                 print_r("Local folder not found: " . $localFolder . "\n");
    //                 continue;
    //             }

    //             $files = new \RecursiveIteratorIterator(
    //                 new \RecursiveDirectoryIterator($localFolder, \RecursiveDirectoryIterator::SKIP_DOTS)
    //             );

    //             foreach ($files as $file) {
    //                 if (!$file->isFile()) {
    //                     continue;
    //                 }

    //                 $fileName = basename($file);
    //                 $s3Key = $s3Folder . $fileName;

    //                 // Upload the file to S3
    //                 try {
    //                     $result = $s3->putObject([
    //                         'Bucket' => $bucket,
    //                         'Key'    => $s3Key,
    //                         'SourceFile' => $file->getRealPath(),
    //                     ]);
    //                     log_message('error',"Uploaded: " .$fileName . " to " . $s3Folder . "\n");

    //                     print_r("Uploaded: " . $fileName . " to " . $s3Folder . "\n");
    //                 } catch (AwsException $e) {
    //                     log_message('error',"Error uploading file " . $fileName . ": " . $e->getMessage() . "\n");

    //                     print_r("Error uploading file " . $fileName . ": " . $e->getMessage() . "\n");
    //                 }
    //             }
    //             log_message('error',"All files uploaded successfully from:  " . $localFolder ." to S3 folder: " . $s3Folder . "\n");

    //             print_r("All files uploaded successfully from: " . $localFolder . " to S3 folder: " . $s3Folder . "\n");
    //         } catch (\Exception $e) {
    //             log_message('error',"Error processing folder " . $localFolder . ": " . $e->getMessage() . "\n");

    //             print_r("Error processing folder " . $localFolder . ": " . $e->getMessage() . "\n");
    //         }
    //     }
    // }

    private function uploadToS3()
    {

        // $folders = [
        //     'categories/' => [FCPATH . '/uploads/categories/'],
        //     'become_provider/' => [FCPATH . '/uploads/become_provider/'],
        //     'chat_attachment/' => [FCPATH . '/uploads/chat_attachment/'],
        //     'feature_section/' => [FCPATH . '/uploads/feature_section/'],
        //     'partner/' => [FCPATH . '/uploads/partner/'],
        //     'promocodes/' => [FCPATH . '/uploads/promocodes/'],
        //     'ratings/' => [FCPATH . '/uploads/ratings/'],
        //     'services/' => [FCPATH . '/uploads/services/'],
        //     'site/' => [FCPATH . '/uploads/site/'],
        //     'sliders/' => [FCPATH . '/uploads/sliders/'],
        //     'web_settings/' => [FCPATH . '/uploads/web_settings/'],
        //     'address_id/' => [FCPATH . '/backend/assets/address_id/'],
        //     'national_id/' => [FCPATH . '/backend/assets/national_id/'],
        //     'passport/' => [FCPATH . '/backend/assets/passport/'],
        //     'profile/' => [
        //         FCPATH . '/backend/assets/profile/',
        //         FCPATH . '/backend/assets/profiles/' // Now both folders are included
        //     ],
        //     'provider_work_evidence/' => [FCPATH . '/backend/assets/provider_work_evidence/'],
        //     'banner/' => [FCPATH . '/backend/assets/banner/'],
        // ];

        $folders = [
          
            'profile/' => [
                FCPATH . '/backend/assets/profile/',
                // FCPATH . '/backend/assets/profiles/' // Now both folders are included
            ],
           
        ];

        // Get AWS configuration
        $S3_settings = get_settings('general_settings', true);
        $aws_key = $S3_settings['aws_access_key_id'] ?? '';
        $aws_secret = $S3_settings['aws_secret_access_key'] ?? '';
        $bucket = $S3_settings['aws_bucket'] ?? '';
        $region = $S3_settings['aws_region'] ?? 'us-east-1';

        if (!$aws_key || !$aws_secret || !$bucket || !$region) {
            print_r("AWS configuration missing. Please check configuration variables.");
            return;
        }

        $config = [
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $aws_key,
                'secret' => $aws_secret,
            ],
        ];

        $s3 = new S3Client($config);

        foreach ($folders as $s3Folder => $localFolders) {
            foreach ((array) $localFolders as $localFolder) {
                try {
                    if (!is_dir($localFolder)) {
                        print_r("Local folder not found: " . $localFolder . "\n");
                        continue;
                    }

                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($localFolder, \RecursiveDirectoryIterator::SKIP_DOTS)
                    );

                    foreach ($files as $file) {
                        if (!$file->isFile()) {
                            continue;
                        }

                        $fileName = basename($file);
                        $s3Key = $s3Folder . $fileName; // Both folders will be merged into 'profile/' in S3

                        try {
                            $result = $s3->putObject([
                                'Bucket' => $bucket,
                                'Key'    => $s3Key,
                                'SourceFile' => $file->getRealPath(),
                            ]);

                            log_message('error', "Uploaded: " . $fileName . " to " . $s3Folder . "\n");
                            print_r("Uploaded: " . $fileName . " to " . $s3Folder . "\n");
                        } catch (AwsException $e) {
                            log_message('error', "Error uploading file " . $fileName . ": " . $e->getMessage() . "\n");
                            print_r("Error uploading file " . $fileName . ": " . $e->getMessage() . "\n");
                        }
                    }

                    log_message('error', "All files uploaded successfully from:  " . $localFolder . " to S3 folder: " . $s3Folder . "\n");


                    log_message('error', "WORKER STOPPED" . "\n");
                    print_r("All files uploaded successfully from: " . $localFolder . " to S3 folder: " . $s3Folder . "\n");
                } catch (\Exception $e) {
                    log_message('error', "Error processing folder " . $localFolder . ": " . $e->getMessage() . "\n");
                    print_r("Error processing folder " . $localFolder . ": " . $e->getMessage() . "\n");
                }
            }
        }
    }
}
