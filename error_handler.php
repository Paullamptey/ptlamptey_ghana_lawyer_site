<?php
// server/error_handler.php
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Server error occurred',
        'debug' => (ini_get('display_errors')) ? "$errstr in $errfile on line $errline" : null
    ]);
    exit;
}

function handleException($e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Server exception occurred',
        'debug' => (ini_get('display_errors')) ? $e->getMessage() : null
    ]);
    exit;
}

set_error_handler('handleError');
set_exception_handler('handleException');
?>