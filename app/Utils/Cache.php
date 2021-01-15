<?php

namespace TheLostAsura\Connector\Utils;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;

class Cache
{
    private static $instances = [];

    private static $cache;
    
    protected function __construct() 
    {
        // Create a new Container object, needed by the cache manager.
        $container = new Container;

        // The CacheManager creates the cache "repository" based on config values
        // which are loaded from the config class in the container.
        // More about the config class can be found in the config component; for now we will use an array
        $container['config'] = [
            'cache.default' => 'file',
            'cache.stores.file' => [
                'driver' => 'file',
                'path' => ASURA_CONNECTOR_PATH . '/storage/framework/cache/data'
            ]
        ];

        // To use the file cache driver we need an instance of Illuminate's Filesystem, also stored in the container
        $container['files'] = new Filesystem;

        // Create the CacheManager
        $cacheManager = new CacheManager($container);

        // Get the default cache driver (file in this case)
        self::$cache = $cacheManager->store();

        // Or, if you have multiple drivers:
        // $cache = $cacheManager->store('file');
    }
    
    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance() : Cache
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public static function __callStatic($method, $args)
    {
        return self::getInstance()::$cache->{$method}(...$args);
    }
}
