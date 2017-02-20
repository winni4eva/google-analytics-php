# google-analytics-php Installation

composer require winnipass/google-analytics-php

use Winnipass\Analytics;
use Winnipass\Period;

$token = [
        "access_token"=>"your-access-token", 
        "refresh_token"=>"refresh-token", 
        "token_type"=>"Bearer",
        "expires_in"=>3600, 
        "id_token"=>"TOKEN", 
        "created"=>1320790426
    ];

$secret_json_path = realpath(__DIR__) .'\path-to-your-secret-json-file.json' ;

$analyticsService = new Analytics;

$service = $analyticsService->setClient( $token, $secret_json_path )
                ->setViewId( '1234567' );
                
                
$visitorsAndPageViews = $service->fetchVisitorsAndPageViews( '2016-08-20', '2017-01-31' );

$totalVisitorsAndPageViews = $service->fetchTotalVisitorsAndPageViews( '2016-08-20', '2017-01-31' );

$mostVisitedPaged = $service->fetchMostVisitedPages( '2016-08-20', '2017-01-31' );

$topReferrers = $service->fetchTopReferrers( '2016-08-20', '2017-01-31' );

$topBrowsers = $service->fetchTopBrowsers( '2016-08-20', '2017-01-31' );

#Custom Query
$startDate = new DateTime( date( 'Y-m-d', strtotime( '2016-08-20' ) ) ); 

$endDate = new DateTime( date( 'Y-m-d', strtotime( '2016-08-20' ) ) ); 

$visitorsAndPageViews = $service->performQuery( new Period($startDate, $endDate), 'ga:pageviews' );

#Iterating over response using Laravel Collection Helper method collect()
#for more info about Laravel Collections visit https://laravel.com/docs/5.4/collections
$pageViews = collect($visitorsAndPageViews['rows'] ?? [])->map(function ($pageViews) {
     return $pageViews;
});


var_dump($visitorsAndPageViews);

