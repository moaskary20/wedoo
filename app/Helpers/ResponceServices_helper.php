<?php
function successResponse($message = "Success", $error = false, $data = null, $customData = [], $code = 200, $csrfName = null, $csrfHash = null)
{
    $response = [
        'error'     => $error,
        'message'   => labels($message, $message),
        'data'      => $data,
        'code'      => $code,
        'csrfName'  => $csrfName,
        'csrfHash'  => $csrfHash,
    ];

    return response_helper()->setJSON(array_merge($response, $customData));
}
function ErrorResponse($message = "Success", $error = false, $data = null, $customData = [], $code = 200, $csrfName = null, $csrfHash = null)
{
    $response = [
        'error'     => $error,
        'message'   => labels($message, $message),
        'data'      => $data,
        'code'      => $code,
        'csrfName'  => $csrfName,
        'csrfHash'  => $csrfHash,
    ];

    return response_helper()->setJSON(array_merge($response, $customData));
}

function NoPermission($message = "Sorry! You're not permitted to take this action", $error = true, $data = null, $customData = [], $code = 200, $csrfName = null, $csrfHash = null)
{
    $response = [
        'error'     => $error,
        'message'   => labels($message, $message),
        'data'      => $data,
        'code'      => $code,
        'csrfName'  => $csrfName,
        'csrfHash'  => $csrfHash,
    ];

    return response_helper()->setJSON(array_merge($response, $customData));
}
function demoModeNotAllowed()
{
    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
        $response = [
            'error'     => true,
            'message'   => DEMO_MODE_ERROR,
            'csrfName'  => csrf_token(),
            'csrfHash'  => csrf_hash(),
        ];

        return $response;
        return response_helper()->setJSON($response);
    } else {

        return ['error' => false];
    }
}

function ApiErrorResponse($message = "Success", $error = false, $data = null)
{
    $response = [
        'error'     => $error,
        'message'   => $message,
        'data'      => $data,

    ];

    return response_helper()->setJSON(array_merge($response));
}



function log_the_responce($message = "something went wrong", $controller = false)
{
    log_message('error', $message . '- at ' . $controller);
}



