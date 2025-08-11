<?php
// function generate_tokens($identity)
// {
//     $jwt = new App\Libraries\JWT();
//     $db      = \Config\Database::connect();
//     $user_id = $db->table('users')->select('id')->where(['phone' => $identity])->get()->getResultArray()[0]['id'];
//     $payload = [
//         'iat' => time(), /* issued at time */
//         'iss' => 'edemand',
//         'exp' => time() + (60 * 60 * 24 * 365),
//         'sub' => 'edemand_authentication',
//         'user_id' => $user_id
//     ];
//     $token = $jwt->encode($payload, API_SECRET);
//     return $token;
// }


function generate_tokens($identity, $user_group, $uid = null,$loginType=null)
{


   


    $jwt = new App\Libraries\JWT();
    $db = \Config\Database::connect();

    $builder = $db->table('users u');

    if (!empty($uid) && !empty($identity)) {
       
        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', $user_group)
            ->where('uid', $uid)->where('phone', $identity);
    } else if (!empty($uid)) {
     
        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', $user_group)
            ->where('uid', $uid);
    } else {
        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', $user_group)
            ->where('phone', $identity)
            ->where('loginType',$loginType);
    }
    $user_id = $builder->get()->getResultArray()[0]['id'];

    $payload = [
        'iat' => time(), /* issued at time */
        'iss' => 'edemand',
        'exp' => time() + (60 * 60 * 24 * 365),
        'sub' => 'edemand_authentication',
        'user_id' => $user_id
    ];
    $token = $jwt->encode($payload, API_SECRET);
    return $token;
}
function verify_tokens()
{
    $responses = \Config\Services::response();
    $jwt = new App\Libraries\JWT;
    try {
        $token = $jwt->getBearerToken();
    } catch (\Exception $e) {
        $response['error'] = true;
        $response['message'] = $e->getMessage();
        print_r(json_encode($response));
        return false;
    }
    if (!empty($token)) {
        $api_keys = API_SECRET;
        if (empty($api_keys)) {
            $response['error'] = true;
            $response['message'] = 'No Client(s) Data Found !';
            print_r(json_encode($response));
            return false;
        }
        App\Libraries\JWT::$leeway = 60000000000000;
        $flag = true; //For payload indication that it return some data or throws an expection.
        $error = true; //It will indicate that the payload had verified the signature and hash is valid or not.
        $message = '';
        $user_token = "";
        try {
            $user_id = $jwt->decode_unsafe($token)->user_id;
            $user_token = fetch_details('users', ['id' => $user_id])[0]['api_key'];
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        if ($user_token == $token) {
            try {
                $payload = $jwt->decode($token, $api_keys, ['HS256']);
                if (isset($payload->iss) && $payload->iss == 'edemand') {
                    $error = false;
                    $flag = false;
                } else {
                    $error = true;
                    $flag = false;
                    $message = 'Invalid Hash';
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        } else {
            $error = true;
            $flag = false;
            $message = 'Token expired. Please login again';
        }
        if ($flag) {
            $response['error'] = true;
            $response['message'] = $message;
            print_r(json_encode($response));
            return false;
        } else {
            if ($error == true) {
                $response['error'] = true;
                $response['message'] = $message;
                $responses->setStatusCode(401);
                print_r(json_encode($response));
                return false;
            } else {
                return $payload->user_id;
            }
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Unauthorized access not allowed";
        print_r(json_encode($response));
        return false;
    }
}
function verify_app_request()
{
    // to verify the token from application
    $responses = \Config\Services::response();
    $jwt = new App\Libraries\JWT;
    try {
        $token = $jwt->getBearerToken();
    } catch (\Exception $e) {
        return [
            "error" => true,
            "message" => $e->getMessage(),
            "status" => 401,
            "data" => []
        ];
    }
    if (!empty($token)) {
        $api_keys = API_SECRET;
        if (empty($api_keys)) {
            return [
                "error" => true,
                "message" => 'No API found !',
                "status" => 401,
                "data" => []
            ];
        }
        $flag = true; //For payload indication that it return some data or throws an expection.
        $error = true; //It will indicate that the payload had verified the signature and hash is valid or not.
        $message = '';
        $status_code = 0;
        $user_token = [];
        try {
            $user_id = $jwt->decode_unsafe($token)->user_id;
            $user_data = fetch_details('users', ['id' => $user_id]);
            // $user_token = $user_data[0]['api_key'];
            $user_token = fetch_details('users_tokens', ['user_id' => $user_id]);
            $db = \Config\Database::connect();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        foreach ($user_token as $row) {
            try {
                if ($row['token'] == $token) {
                    $payload = $jwt->decode($token, $api_keys, ['HS256']);
                    if (isset($payload->iss)) {
                        $error = false;
                        $flag = false;
                    } else {
                        $error = true;
                        $flag = false;
                        $message = 'Token Expired';
                        $status_code = 403;
                        break;
                    }
                } else {
                    $message = 'Token not verified !!';
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        }
        if ($flag) {
            return [
                "error" => true,
                "message" => $message,
                "status" => 401,
                "data" => []
            ];
        } else {
            if ($error == true) {
                return [
                    "error" => true,
                    "message" => $message,
                    "status" => 401,
                    "status_code" => 102,
                    "data" => []
                ];
            } else {
                return [
                    "error" => false,
                    "message" => "Token verified !!",
                    "status" => 200,
                    "data" => isset($user_data[0]) ? $user_data[0] : ''
                ];
            }
        }
    } else {
        return [
            "error" => true,
            "message" => "Unauthorized access not allowed",
            "status" => 401,
            "status_code" => 101,
            "data" => []
        ];
    }
}
