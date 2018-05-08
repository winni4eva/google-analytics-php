<?php

namespace Winnipass\OAuth\Google;

use Winnipass\OAuth\OAuthInterface;
use Google_Client;

class GoogleOAuth implements OAuthInterface
{

    protected $client;

    public $authUrl;

    public $token;

    public function __construct(string $clientsSecretJsonPath, string $redirectUrl, array $scopes, string $accessType = "offline" )
    {
        $this->client = new Google_Client;
        $this->client->setAuthConfig( $clientsSecretJsonPath );
        $this->client->setAccessType( "offline" );        // offline access
        $this->client->setApprovalPrompt( "force" );
        $this->client->setIncludeGrantedScopes( true );   // incremental auth
        //$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $this->client->setScopes( $scopes );
        //$this->client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/index.php');
        $this->client->setRedirectUri( $redirectUrl );
        $this->authUrl = $this->client->createAuthUrl();
    }

    public function redirect()
    {
        header( 'Location: ' . filter_var( $this->authUrl, FILTER_SANITIZE_URL ) );
    }

    public function authenticate( $code )
    {
        if( $this->client->authenticate( $code ) )
        {
            $this->getAccessToken();

            return $this;
        }
        
        throw new Exception("Authentication failed", 403);
        
    }

    public function getTokens()
    {
        return $this->token;
    }

    public function getAccessToken()
    {
        return $this->token = $this->client->getAccessToken();
    }

    public function setAccessToken( $accessToken )
    {
        $this->client->setAccessToken( $accessToken );
    }

    public function isAccessTokenExpired()
    {
        return $this->client->isAccessTokenExpired();
    }

    public function refreshToken( $refreshToken )
    {
        $this->client->refreshToken( $refreshToken );
    }

    public function makeRequest( $url )
    {
        // returns a Guzzle HTTP Client
        $httpClient = $this->client->authorize();

        // make an HTTP request
        return $httpClient->get( $url );
    }

    public function makeJsonRequest($method = 'GET',string $url, $body = [],array $headers = [],bool $verify = false, bool $http_errors = false)
    {
        $client = $this->client->authorize();

        return $client->request( strtoupper($method) , $url, [
            'headers' => $headers, 
            'json' => $body, 
            'verify' => $verify,
            'http_errors' => $http_errors,
        ]);
    }

}