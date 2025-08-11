<?php
// app/Scripts/process_queue.php
// Create this as a new file in app/Scripts/process_queue.php

// Basic initialization
require __DIR__ . '/../../vendor/autoload.php';

// Simple logging function
function writeLog($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Set unlimited memory and time
ini_set('memory_limit', '-1');
set_time_limit(0);

$logFile = __DIR__ . '/../../writable/logs/queue_process.log';

try {
    writeLog("Process started", $logFile);
    
    // Simulate 10 iterations of work
    for ($i = 1; $i <= 10; $i++) {
        writeLog("Processing item $i of 10", $logFile);
        sleep(1); // Simulate work
        writeLog("Completed item $i", $logFile);
    }
    
    writeLog("Process completed successfully", $logFile);
} catch (Exception $e) {
    writeLog("Error: " . $e->getMessage(), $logFile);
}
