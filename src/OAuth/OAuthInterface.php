<?php

namespace Winnipass\OAuth;

interface OAuthInterface{

    public function redirect();

    public function authenticate( $code );

    public function getAccessToken();

    public function setAccessToken( $accessToken );
}