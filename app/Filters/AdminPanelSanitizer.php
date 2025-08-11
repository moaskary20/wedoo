<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminPanelSanitizer implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Sanitize POST data
        $post = $request->getPost();
        foreach ($post as $key => $value) {
            if (is_string($value)) {
                $post[$key] = $this->sanitizeInput($value);
            }
        }
        $request->setGlobal('post', $post);

        // Sanitize GET data
        $get = $request->getGet();
        foreach ($get as $key => $value) {
            if (is_string($value)) {
                $get[$key] = $this->sanitizeInput($value);
            }
        }
        $request->setGlobal('get', $get);

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after the controller is executed
    }

    // private function sanitizeInput($input)
    // {
    //     // Remove all HTML tags except for a whitelist
    //     $input = strip_tags($input, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>><iframe>');
        
    //     // Convert special characters to HTML entities
    //     $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
    //     return $input;
    // }

    private function sanitizeInput($input)
{
    // Allow iframe and other specific HTML tags
    $allowedTags = '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6><iframe>';

    // Strip unwanted tags while allowing specific ones
    $input = strip_tags($input, $allowedTags);

    // Do not apply htmlspecialchars for iframe content
    return $input;
}


}