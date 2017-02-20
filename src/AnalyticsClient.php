<?php

namespace Winnipass;

use DateTime;
use Google_Service_Analytics;
use phpFastCache\CacheManager;
//use Illuminate\Contracts\Cache\Repository;

class AnalyticsClient
{

    protected $cachePath;

    /** @var \Google_Service_Analytics */
    protected $service;

    /** @var \Cache */
    protected $cache;

    /** @var int */
    protected $cacheLifeTimeInMinutes = 0;

    protected $cacheTime = 7200;// 7200 second maps to two hours of cache time


    public function __construct(Google_Service_Analytics $service)
    {
        $this->service = $service;

        $dirSeparator = DIRECTORY_SEPARATOR; 
        //echo realpath(__DIR__)."/Cache/analytics-cache";
        CacheManager::setDefaultConfig(array(
            "path" => realpath(__DIR__)."/Cache/analytics-cache",
        ));

        $this->cache = CacheManager::getInstance('files'); //(new Cache)->setCachePath( $this->cachePath );
        
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

        $cachedString = $this->cache->getItem( $cacheName );//

        if (is_null($cachedString->get())) {
    
           $cachedString->set(
               $this->service->data_ga->get(
                    "ga:{$viewId}",
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d'),
                    $metrics,
                    $others
                )
           )->expiresAfter($this->cacheTime);
            
            $this->cache->save($cachedString);

            return $this->fetchFromCache( $cachedString );
    
        }
            
        return $this->fetchFromCache( $cachedString );

    }

    protected function fetchFromCache(&$cachedString){
        return $cachedString->get();
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
