# google-analytics-php Installation

composer require winnipass/google-analytics-php

$token = [
        "access_token"=>"your-access-token", 
        "refresh_token"=>"refresh-token", 
        "token_type"=>"Bearer",
        "expires_in"=>3600, 
        "id_token"=>"TOKEN", 
        "created"=>1320790426
    ];

$secret_json_path = realpath(__DIR__) .'\path-to-your-secret-json-file.json' ;

$viewId = '1234567';

echo (new Analytics( $token , $secret_json_path, $viewId ))->initialize();//Returns a Laravel Collection Object

