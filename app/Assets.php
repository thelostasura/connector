<?php

namespace TheLostAsura\Connector;

/**
 * Scripts and Styles Class
 */
class Assets {

	function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
		}
	}

	/**
	 * Register our app scripts and styles
	 *
	 * @return void
	 */
	public function register() {
		$this->register_scripts( $this->get_scripts() );
		$this->register_styles( $this->get_styles() );
	}

	/**
	 * Register scripts
	 *
	 * @param array $scripts
	 *
	 * @return void
	 */
	private function register_scripts( $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
			$version   = isset( $script['version'] ) ? $script['version'] : ASURA_CONNECTOR_VERSION;

			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @return array
	 */
	public function get_scripts() {
		$scripts = [
			'asura-connector-manifest' => [
				'src'       => ASURA_CONNECTOR_ASSETS . '/js/manifest.js',
				'deps'      => [ 'wp-i18n' ],
				'version'   => filemtime( ASURA_CONNECTOR_PATH . '/public/js/manifest.js' ),
				'in_footer' => true
			],
			'asura-connector-vendor'   => [
				'src'       => ASURA_CONNECTOR_ASSETS . '/js/vendor.js',
				'deps'      => [ 'asura-connector-manifest' ],
				'version'   => filemtime( ASURA_CONNECTOR_PATH . '/public/js/vendor.js' ),
				'in_footer' => true
			],
			'asura-connector-frontend' => [
				'src'       => ASURA_CONNECTOR_ASSETS . '/js/frontend.js',
				'deps'      => [ 'asura-connector-vendor' ],
				'version'   => filemtime( ASURA_CONNECTOR_PATH . '/public/js/frontend.js' ),
				'in_footer' => true
			],
			'asura-connector-admin'    => [
				'src'       => ASURA_CONNECTOR_ASSETS . '/js/admin.js',
				'deps'      => [ 'asura-connector-vendor', 'asura-connector-manifest' ],
				'version'   => filemtime( ASURA_CONNECTOR_PATH . '/public/js/admin.js' ),
				'in_footer' => true
			]
		];

		return $scripts;
	}

	/**
	 * Register styles
	 *
	 * @param array $styles
	 *
	 * @return void
	 */
	public function register_styles( $styles ) {
		foreach ( $styles as $handle => $style ) {
			$deps = isset( $style['deps'] ) ? $style['deps'] : false;

			wp_register_style( $handle, $style['src'], $deps, ASURA_CONNECTOR_VERSION );
		}
	}

	/**
	 * Get registered styles
	 *
	 * @return array
	 */
	public function get_styles() {

		$styles = [
			'asura-connector-style'    => [
				'src' => ASURA_CONNECTOR_ASSETS . '/css/app.css'
			],
			'asura-connector-frontend' => [
				'src' => ASURA_CONNECTOR_ASSETS . '/css/frontend.css'
			],
			'asura-connector-admin'    => [
				'src' => ASURA_CONNECTOR_ASSETS . '/css/admin.css'
			],
		];

		return $styles;
	}
}
