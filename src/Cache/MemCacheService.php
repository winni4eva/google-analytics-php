<?php 
namespace Winnipass\Cache;

class MemCacheService {

    protected $iTtl = 600; // Time To Live

    protected $bEnabled = false; // Memcache enabled?

    protected $oCache = null;

    // constructor
    public function __construct() {

        if (class_exists('Memcache')) {

            $this->oCache = new Memcache();

            $this->bEnabled = true;

            ( new Dotenv\Dotenv(__DIR__) )->load();

            if (! $this->oCache->connect(getenv('DB_HOST'), 11211))  { // Instead 'localhost' here can be IP

                $this->oCache = null;

                $this->bEnabled = false;

            }

        }else{
            throw new \Exception("Class Memcache not found", 1);
        }

    }

    // get data from cache server
    function getData($sKey) {

        $vData = $this->oCache->get($sKey);

        return false === $vData ? null : $vData;

    }

    // save data to cache server
    function setData($sKey, $vData) {

        //Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib).

        return $this->oCache->set($sKey, $vData, 0, $this->iTtl);

    }

    // delete data from cache server
    function delData($sKey) {

        return $this->oCache->delete($sKey);

    }

}