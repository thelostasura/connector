<?php

/**
 * Asura Connector
 *
 * @wordpress-plugin
 * Plugin Name:         Asura Connector
 * Description:         Access to design sets collections managed by the Asura plugin.
 * Version:             3.0.3
 * Author:              thelostasura
 * Author URI:          https://thelostasura.com
 * Requires at least:   5.5
 * Tested up to:        5.6
 * Requires PHP:        7.4
 * Text Domain:         asura-connector
 * Domain Path:         /languages
 *
 * @package             Asura Connector
 * @author              thelostasura <mail@thelostasura.com>
 * @link                https://thelostasura.com
 * @since               1.0.0
 * @copyright           2020 thelostasura
 * @version             3.0.3
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

use TheLostAsura\Connector\Utils\Migration;

/**
 * Asura_Connector class
 *
 * @class Asura_Connector The class that holds the entire Asura_Connector plugin
 */
final class Asura_Connector {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '3.0.3';
	public $db_version = '001';


	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = [];

	/**
	 * Constructor for the Asura_Connector class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 */
	public function __construct() {

		$this->define_constants();

		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

	/**
	 * Define the constants
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'ASURA_CONNECTOR_VERSION', $this->version );
		define( 'ASURA_CONNECTOR_DB_VERSION', $this->db_version );
		define( 'ASURA_CONNECTOR_FILE', __FILE__ );
		define( 'ASURA_CONNECTOR_PATH', dirname( ASURA_CONNECTOR_FILE ) );
		define( 'ASURA_CONNECTOR_MIGRATION_PATH', ASURA_CONNECTOR_PATH . '/database/migrations/' );
		define( 'ASURA_CONNECTOR_URL', plugins_url( '', ASURA_CONNECTOR_FILE ) );
		define( 'ASURA_CONNECTOR_ASSETS', ASURA_CONNECTOR_URL . '/public' );
	}

	/**
	 * Initializes the Asura_Connector() class
	 *
	 * Checks for an existing Asura_Connector() instance
	 * and if it doesn't find one, creates it.
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new Asura_Connector();
		}

		return $instance;
	}

	public function activate() {
		if ( ! get_option( 'asura_connector_installed' ) ) {
			update_option( 'asura_connector_installed', time() );
		}

		$installed_db_version = get_option( 'asura_connector_db_version' );

		if ( ! $installed_db_version || intval( $installed_db_version ) !== intval( ASURA_CONNECTOR_DB_VERSION ) ) {
			Migration::migrate( $installed_db_version ?: 0, ASURA_CONNECTOR_DB_VERSION );
			update_option( 'asura_connector_db_version', ASURA_CONNECTOR_DB_VERSION );
		}

		update_option( 'asura_connector_version', ASURA_CONNECTOR_VERSION );
	}

	public function deactivate() {
	}

	/**
	 * Magic getter to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}

		return $this->{$prop};
	}

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	public function __isset( $prop ): bool {
		return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
	}

	/**
	 * Load the plugin after all plugis are loaded
	 *
	 * @return void
	 */
	public function init_plugin() {
		$this->init_hooks();
	}

	/**
	 * Initialize the hooks
	 *
	 * @return void
	 */
	public function init_hooks() {
		// Localize our plugin
		add_action( 'init', [ $this, 'localization_setup' ], 100 );

		add_action( 'init', [ $this, 'init_classes' ] );
	}

	/**
	 * Instantiate the required classes
	 *
	 * @return void
	 */
	public function init_classes() {

		$this->container['assets'] = new TheLostAsura\Connector\Assets();
		// $this->container['api'] = new TheLostAsura\Connector\Api();

		if ( $this->is_request( 'admin' ) ) {
			$this->container['admin'] = new TheLostAsura\Connector\Admin();
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->container['frontend'] = new TheLostAsura\Connector\Frontend();
		}

		if ( $this->is_request( 'ajax' ) ) {
			$this->container['ajax'] = new TheLostAsura\Connector\Ajax();
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( string $type ): bool {
		switch ( $type ) {
			case 'admin':
				return is_admin();

			case 'ajax':
				return defined( 'DOING_AJAX' );

			case 'rest':
				return defined( 'REST_REQUEST' );

			case 'cron':
				return defined( 'DOING_CRON' );

			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );

			default:
				break;
		}
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @uses load_plugin_textdomain()
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'asura-connector', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

$thelostasura_connector = Asura_Connector::init();
