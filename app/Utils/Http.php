<?php

namespace TheLostAsura\Connector\Utils;

use Illuminate\Http\Client\Factory;

class Http
{
    private static $instances = [];

    private static $client;
    
    protected function __construct() 
    {
        self::$client = new Factory();
    }
    
    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance() : Http
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public static function __callStatic($method, $args)
    {
        return self::getInstance()::$client->{$method}(...$args);
    }
}