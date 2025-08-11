<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ImageFallback implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // No need to handle the request; it's about manipulating the response
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // If the response is not empty, modify the HTML content
        if ($response->getBody()) {
            $body = $response->getBody();
            $CI = \Config\Services::image();
            
            // Regular expression for matching <img> tags and replacing the src attribute
            $pattern = '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i';
            
            $body = preg_replace_callback($pattern, function ($matches) {
                // Apply image fallback logic
                $image_path = $matches[1];
                // You can use the helper function or directly manipulate the fallback
                $fallback_image = image_url($image_path);
                return str_replace($matches[1], $fallback_image, $matches[0]);
            }, $body);
            
            $response->setBody($body);
        }

        return $response;
    }
    // public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    // {
    //     if ($response->getBody()) {
    //         $body = $response->getBody();
    //         $CI = \Config\Services::image();

    //         // Updated pattern to capture entire img tag
    //         $pattern = '/<img\b[^>]*>/i';

    //         $body = preg_replace_callback($pattern, function ($matches) {
    //             $img_tag = $matches[0];
                
    //             // Extract src if it exists
    //             preg_match('/src=["\']([^"\']+)["\']/i', $img_tag, $src_matches);
    //             $image_path = $src_matches[1] ?? '';
                
    //             // Generate fallback image URL
    //             $fallback_image = image_url('/path/to/default-image.jpg');
                
    //             // Add onerror attribute if it doesn't exist
    //             if (!stripos($img_tag, 'onerror=')) {
    //                 $img_tag = str_replace('<img', '<img onerror="this.src=\'' . $fallback_image . '\'"', $img_tag);
    //             }
                
    //             // Update src attribute if it exists
    //             if ($image_path) {
    //                 $processed_path = image_url($image_path);
    //                 $img_tag = preg_replace('/src=["\']([^"\']+)["\']/i', 'src="' . $processed_path . '"', $img_tag);
    //             }
                
    //             return $img_tag;
    //         }, $body);

    //         $response->setBody($body);
    //     }

    //     return $response;
    // }
}
