<?php
// Test file to check if the add_service method is working
echo "Testing add_service method accessibility...\n";

// Check if method exists in controller
$controller_file = "/var/www/html2/app/Controllers/admin/Services.php";
if (file_exists($controller_file)) {
    $content = file_get_contents($controller_file);
    if (strpos($content, 'function add_service()') !== false) {
        echo "✓ add_service() method exists in controller\n";
    } else {
        echo "✗ add_service() method NOT found in controller\n";
    }
    
    if (strpos($content, 'function add_service_view()') !== false) {
        echo "✓ add_service_view() method exists in controller\n";
    } else {
        echo "✗ add_service_view() method NOT found in controller\n";
    }
} else {
    echo "✗ Controller file not found\n";
}

echo "Test completed.\n";
?>
