<?php

namespace Winnipass;

use Carbon\Carbon;
use Google_Service_Analytics;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Google_Client;

class Analytics
{
    use Macroable;

    /** @var \Winnipass\AnalyticsClient */
    protected $client;

    /** @var string */
    protected $viewId;


    public function __construct(){}

    /**
     * @param string $viewId
     *
     * @return $this
     */
    public function setViewId(string $viewId)
    {
        $this->viewId = $viewId;

        return $this;
    }


    /**
     * @param array $token
     *
     * @param string $secretJsonPath
     *
     * @return $this
     */
    public function setClient(array $token, string $secretJsonPath){

        $googleClient = new Google_Client;

        $googleClient->setAuthConfig( $this->getClientSecretFile( $secretJsonPath ) );

        $googleClient->setAccessToken( json_encode( $token ) );

        $googleAnalyticsService = new Google_Service_Analytics( $googleClient );
        
        $this->client = new AnalyticsClient( $googleAnalyticsService );

        return $this;

    }

    protected function getClientSecretFile( $path ){
        return file_get_contents( $path );
    }

    protected function getPeriodFromDateTimeDate(string $startDate,string $endDate){
        return Period::create( $this->getDateTimeDate( $startDate ) , $this->getDateTimeDate( $endDate ) );
    }

    protected function getDateTimeDate(string $date,string $format = "Y-m-d H:i:s"){
        return new \DateTime( date( $format, strtotime( $date ) ) );
    }

    public function fetchVisitorsAndPageViews($startDate, $endDate): Collection
    {
        $period = $this->getPeriodFromDateTimeDate( $startDate , $endDate );

        $response = $this->performQuery(
            $period,
            'ga:users,ga:pageviews',
            ['dimensions' => 'ga:date,ga:pageTitle']
        );

        return collect($response['rows'] ?? [])->map(function (array $dateRow) {
            return [
                'date' => Carbon::createFromFormat('Ymd', $dateRow[0]),
                'pageTitle' => $dateRow[1],
                'visitors' => (int) $dateRow[2],
                'pageViews' => (int) $dateRow[3],
            ];
        });
    }

    public function fetchTotalVisitorsAndPageViews($startDate, $endDate): Collection
    {
        $period = $this->getPeriodFromDateTimeDate( $startDate , $endDate );

        $response = $this->performQuery(
            $period,
            'ga:users,ga:pageviews',
            ['dimensions' => 'ga:date']
        );

        return collect($response['rows'] ?? [])->map(function (array $dateRow) {
            return [
                'date' => Carbon::createFromFormat('Ymd', $dateRow[0]),
                'visitors' => (int) $dateRow[1],
                'pageViews' => (int) $dateRow[2],
            ];
        });
    }

    public function fetchMostVisitedPages($startDate, $endDate, int $maxResults = 20): Collection
    {
        $period = $this->getPeriodFromDateTimeDate( $startDate , $endDate );

        $response = $this->performQuery(
            $period,
            'ga:pageviews',
            [
                'dimensions' => 'ga:pagePath,ga:pageTitle',
                'sort' => '-ga:pageviews',
                'max-results' => $maxResults,
            ]
        );

        return collect($response['rows'] ?? [])
            ->map(function (array $pageRow) {
                return [
                    'url' => $pageRow[0],
                    'pageTitle' => $pageRow[1],
                    'pageViews' => (int) $pageRow[2],
                ];
            });
    }

    public function fetchTopReferrers($startDate, $endDate, int $maxResults = 20): Collection
    {
        $period = $this->getPeriodFromDateTimeDate( $startDate , $endDate );

        $response = $this->performQuery($period,
            'ga:pageviews',
            [
                'dimensions' => 'ga:fullReferrer',
                'sort' => '-ga:pageviews',
                'max-results' => $maxResults,
            ]
        );

        return collect($response['rows'] ?? [])->map(function (array $pageRow) {
            return [
                'url' => $pageRow[0],
                'pageViews' => (int) $pageRow[1],
            ];
        });
    }

    public function fetchTopBrowsers($startDate, $endDate, int $maxResults = 10): Collection
    {
        $period = $this->getPeriodFromDateTimeDate( $startDate , $endDate );

        $response = $this->performQuery(
            $period,
            'ga:sessions',
            [
                'dimensions' => 'ga:browser',
                'sort' => '-ga:sessions',
            ]
        );

        $topBrowsers = collect($response['rows'] ?? [])->map(function (array $browserRow) {
            return [
                'browser' => $browserRow[0],
                'sessions' => (int) $browserRow[1],
            ];
        });

        if ($topBrowsers->count() <= $maxResults) {
            return $topBrowsers;
        }

        return $this->summarizeTopBrowsers($topBrowsers, $maxResults);
    }

    protected function summarizeTopBrowsers(Collection $topBrowsers, int $maxResults): Collection
    {
        return $topBrowsers
            ->take($maxResults - 1)
            ->push([
                'browser' => 'Others',
                'sessions' => $topBrowsers->splice($maxResults - 1)->sum('sessions'),
            ]);
    }

    /**
     * Call the query method on the authenticated client.
     *
     * @param Period $period
     * @param string $metrics
     * @param array  $others
     *
     * @return array|null
     */
    public function performQuery(Period $period, string $metrics, array $others = [])
    {
        return $this->client->performQuery(
            $this->viewId,
            $period->startDate,
            $period->endDate,
            $metrics,
            $others
        );
    }

    /**
     * Get the underlying Google_Service_Analytics object. You can use this
     * to basically call anything on the Google Analytics API.
     *
     * @return \Google_Service_Analytics
     */
    public function getAnalyticsService(): Google_Service_Analytics
    {
        //return $this->client->getAnalyticsService();
    }
}
