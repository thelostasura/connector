<?php

namespace TheLostAsura\Connector\Lib;

use Exception;

class Asura {
	private static array $instances = [];

	private static SDK $sdk;

	protected function __construct() {
		self::$sdk = new SDK();
	}

	public static function __callStatic( $method, $args ) {
		return self::getInstance()::$sdk->{$method}( ...$args );
	}

	public static function getInstance(): Asura {
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
