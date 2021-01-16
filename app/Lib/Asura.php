<?php

namespace TheLostAsura\Connector\Lib;

class Asura
{
    private static $instances = [];

    private static $sdk;

    protected function __construct() 
    {
        self::$sdk = new SDK();
    }
    
    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance() : Asura
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public static function __callStatic($method, $args)
    {
        return self::getInstance()::$sdk->{$method}(...$args);
    }
}
