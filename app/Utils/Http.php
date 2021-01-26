<?php

namespace TheLostAsura\Connector\Utils;

use Exception;
use Illuminate\Http\Client\Factory;

class Http {
	private static $instances = [];

	private static $client;

	protected function __construct() {
		self::$client = new Factory();
	}

	public static function __callStatic( $method, $args ) {
		return self::getInstance()::$client->{$method}( ...$args );
	}

	public static function getInstance(): Http {
		$cls = static::class;
		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new static();
		}

		return self::$instances[ $cls ];
	}

	public function __wakeup() {
		throw new Exception( "Cannot unserialize a singleton." );
	}

	protected function __clone() {
	}
}