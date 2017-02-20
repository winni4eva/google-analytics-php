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

        // Setup File Path on your config files
        CacheManager::setDefaultConfig(array(
            "path" => __DIR__."{$dirSeparator}Cache{$dirSeparator}analytics-cache{$dirSeparator}",//'/var/www/phpfastcache.com/dev/tmp', // or in windows "C:/tmp/"
        ));

        //$this->cachePath = __DIR__."{$dirSeparator}Cache{$dirSeparator}analytics-cache{$dirSeparator}";

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

        $cachedString = $this->cache->getItem( $cacheName );

        if (is_null($cachedString->get())) {
            //echo "No item found in cache <br>";
    
            $this->setCache( 
                $cachedString, 
                $this->service->data_ga->get(
                    "ga:{$viewId}",
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d'),
                    $metrics,
                    $others
                )
           );
            
            $this->cache->save($cachedString);

            return $cachedString->get();
    
        } else {
            //echo "Item Found In Cache";
            //echo $cachedString->getExpirationDate()->format(Datetime::W3C);
            return $cachedString->get();
    

        }

    }

    protected function setCache(&$cachedString, $item, $expiry = 7200){
        $cachedString->set($item)
                ->expiresAfter($expiry);//in seconds, also accepts Datetime;
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
