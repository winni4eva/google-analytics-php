<?php
namespace Winnipass;

use Google_Client;
use Google_Service_Analytics_UserRef;
use Google_Service_Analytics_EntityUserLinkPermissions;
use Google_Service_Analytics_EntityUserLink;
use Google_Service_Analytics;

class Analytics{

    private $token = [
        "access_token"=>"ya29.Glv0A52rCsGniNKq5P__cEe04QGxYa5BWS90Wxs8pz8UPVMxNZbDeG9ZmkyerXetVoxXsmwkNlyNO1BCNXuZY_VnYud8bRveyhV0uN91wdLaqowNGYVrkLBPaav9", 
        "refresh_token"=>"1/j9ViTQ5EBrCnK38NKAa9-olQpfZtixe2TuoxMjBOvKM", 
        "token_type"=>"Bearer",
        "expires_in"=>3600, 
        "id_token"=>"TOKEN", 
        "created"=>1320790426
    ];

    public function __construct(){}

    public function authenticate(){

        $client = $this->getClient();

        $secret_json = $this->getClientSecretFile( realpath(__DIR__ . '/..').'\credentials\client_secret_347555205836-on3arn2hcu4rq09eundk8vl9t23h1hrn.apps.googleusercontent.com.json' );

        $client->setAuthConfig( $secret_json );

        $client->setAccessToken( json_encode( $this->token ) );

        $analytics = new Google_Service_Analytics($client);

        try {

            $data = $analytics->data_ga->get("ga:124364440", "2016-08-20", "2017-08-31", "ga:users,ga:sessions" );

            var_dump($data);

        } catch (apiServiceException $e) {
            print 'There was an Analytics API service error '. $e->getCode() . ':' . $e->getMessage();
        } catch (apiException $e) {
            print 'There was a general API error '. $e->getCode() . ':' . $e->getMessage();
        }
    }

    public function getClient(){
        return new Google_Client();
    }

    public function getClientSecretFile( $path ){
        return file_get_contents( $path );
    }

}