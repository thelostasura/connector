<?php

namespace TheLostAsura\Connector\Utils;

use Medoo\Medoo;

class DB
{
    private static $instances = [];

    private static $medoo;

    private static $wpdb;

    protected function __construct() 
    {
        self::$wpdb = self::wpdb();

        self::$medoo = new Medoo([
            'database_type' => 'mysql',
            'database_name' => self::$wpdb->dbname,
            'server' => self::$wpdb->dbhost,
            'username' => self::$wpdb->dbuser,
            'password' => self::$wpdb->dbpassword,
            'prefix' => self::$wpdb->prefix,
        ]);
    }

    public static function wpdb() {
        global $wpdb;
        return $wpdb;
    }
    
    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance() : DB
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public static function __callStatic($method, $args)
    {
        return self::getInstance()::$medoo->{$method}(...$args);
    }
}
