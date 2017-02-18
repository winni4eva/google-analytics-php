<?php
namespace Winnipass;

//use Google_Service_Analytics_UserRef;
//use Google_Service_Analytics_EntityUserLinkPermissions;
//use Google_Service_Analytics_EntityUserLink;
use Winnipass\AnalyticsService;

class Analytics{

    protected $analyticsService;

    protected $accessToken;

    protected $secretFilePath;

    public function __construct(array $accessToken, string $secretFilePath ){
        $this->analyticsService = new AnalyticsService;
        $this->accessToken = json_encode( $accessToken );
        $this->secretFilePath = $secretFilePath;
    }

    public function initialize(){

        $client = $this->analyticsService->getClient();

        $secret_json = $this->analyticsService->getClientSecretFile( $this->secretFilePath );

        $client->setAuthConfig( $secret_json );

        $client->setAccessToken( $this->accessToken ) ;

        $pageViews = $this->analyticsService->setViewId( '124364440' )->setClient( $client )->fetchVisitorsAndPageViews( Period::create( $this->getDateTimeDate('2016-08-20'), $this->getDateTimeDate('2017-01-31') ) );
        
        var_dump( $pageViews );
    }

    protected function getDateTimeDate($period, $format = 'Y-m-d H:i:s'){
        return new \DateTime( date( $format, strtotime( $period ) ) );
    }

}