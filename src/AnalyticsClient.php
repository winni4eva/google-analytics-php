<?php

namespace Winnipass;

use DateTime;
use Google_Service_Analytics;
use Cache;
//use Illuminate\Contracts\Cache\Repository;

class AnalyticsClient
{

    protected $cachePath = __DIR__.'/Cache/analytics-cache/';

    /** @var \Google_Service_Analytics */
    protected $service;

    /** @var \Cache */
    protected $cache;

    /** @var int */
    protected $cacheLifeTimeInMinutes = 0;


    public function __construct(Google_Service_Analytics $service)
    {
        $this->service = $service;

        $this->cache = (new Cache)->setCachePath( $this->cachePath );
        
    }

    /**
     * Set the cache time.
     *
     * @param int $cacheLifeTimeInMinutes
     *
     * @return self
     */
    public function setCacheLifeTimeInMinutes(int $cacheLifeTimeInMinutes)
    {
        $this->cacheLifeTimeInMinutes = $cacheLifeTimeInMinutes;

        return $this;
    }

    /**
     * Query the Google Analytics Service with given parameters.
     *
     * @param string    $viewId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string    $metrics
     * @param array     $others
     *
     * @return array|null
     */
    public function performQuery(string $viewId, DateTime $startDate, DateTime $endDate, string $metrics, array $others = [])
    {
        $cacheName = $this->determineCacheName(func_get_args());

        $this->cache->eraseExpired();

        if( $this->cache->isCached( $cacheName ) ){
            echo "Found something in cache <br>";
            return $this->cache->retrieve( $cacheName );
        }
        echo "Store new cache <br>";

        return $this->cache->store( 
            $cacheName,  
            $this->service->data_ga->get(
               "ga:{$viewId}",
               $startDate->format('Y-m-d'),
               $endDate->format('Y-m-d'),
               $metrics,
               $others
           )
        )->retrieve( $cacheName );

    }

    public function getAnalyticsService()
    {
        return $this->service;
    }

    /*
     * Determine the cache name for the set of query properties given.
     */
    protected function determineCacheName(array $properties): string
    {
        return 'winnipass.google-analytics.'.md5(serialize($properties));
    }
}
