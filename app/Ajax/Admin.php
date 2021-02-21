<?php

namespace TheLostAsura\Connector\Ajax;

use Illuminate\Support\Carbon;
use TheLostAsura\Connector\Lib\Asura;
use TheLostAsura\Connector\Models\License;
use TheLostAsura\Connector\Models\Provider;
use TheLostAsura\Connector\Utils\Cache;
use TheLostAsura\Connector\Utils\DB;
use WP_Error;

class Admin {

	public function __construct() {
		$this->register();
	}

	public function register() {
		$this->register_ajaxs( $this->get_ajaxs() );
	}

	private function register_ajaxs( array $ajaxs ) {
		foreach ( $ajaxs as $tag => $ajax ) {
			$priority = $ajax['priority'] ?? 10;
			$args     = $ajax['args'] ?? 1;

			add_action( "wp_ajax_asura_connector_{$tag}", $ajax['handler'], $priority, $args );
		}
	}

	private function get_ajaxs(): array {
		$ajaxs = [
			'add_provider'    => [
				'handler' => [ $this, 'add_provider' ]
			],
			'delete_provider' => [
				'handler' => [ $this, 'delete_provider' ]
			],
			'list_providers'  => [
				'handler' => [ $this, 'list_providers' ]
			],
			'get_provider'    => [
				'handler' => [ $this, 'get_provider' ]
			],
			'edit_provider'   => [
				'handler' => [ $this, 'edit_provider' ]
			],
			'list_licenses'   => [
				'handler' => [ $this, 'list_licenses' ]
			],
			'add_license'     => [
				'handler' => [ $this, 'add_license' ]
			],
			'delete_license'  => [
				'handler' => [ $this, 'delete_license' ]
			],
			'clean_cache'  => [
				'handler' => [ $this, 'clean_cache' ]
			],
			'wizard_colors'  => [
				'handler' => [ $this, 'wizard_colors' ]
			],
			'wizard_stylesheets'  => [
				'handler' => [ $this, 'wizard_stylesheets' ]
			],
			'wizard_settings'  => [
				'handler' => [ $this, 'wizard_settings' ]
			],
			'wizard_stylesets'  => [
				'handler' => [ $this, 'wizard_stylesets' ]
			],
			'wizard_selectors'  => [
				'handler' => [ $this, 'wizard_selectors' ]
			],
			'wizard_templates'  => [
				'handler' => [ $this, 'wizard_templates' ]
			],
			'wizard_pages'  => [
				'handler' => [ $this, 'wizard_pages' ]
			],
			'wizard_classes'  => [
				'handler' => [ $this, 'wizard_classes' ]
			],
		];

		return $ajaxs;

	}

	/**
	 * @see ct_setup_default_colors
	 */
	public function wizard_colors() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The license id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The term slug is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_string( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The term slug should string', 'asura-connector' )
				),
				400
			);
		}

		$provider_id = $_REQUEST['provider_id'];
		$license_id = $_REQUEST['license_id'];
		$term_slug = $_REQUEST['term_slug'];
		$overwrite = $_REQUEST['overwrite'] === 'true';

		$provider = (object) DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $provider_id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$license = (object) DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id',
			'hash',
		],
		[
			'provider_id' => $provider_id,
			'id'          => $license_id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$cache = Cache::remember( "colors_{$license->provider_id}_{$license->id}_{$term_slug}", Carbon::now()->addHour(), function () use ( $provider, $license, $term_slug ) {
			$response = Asura::oxygenbuilder_colors( $provider, $license->hash, $term_slug );

			if ( $response->status() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve colors for license id {$license->id} and term slug {$term_slug}. http error code: {$response->status()}" );

				return null;
			}

			return json_decode( $response->body(), true )['data'];
		} );

		if ( ! $cache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve colors, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		$colors = isset($cache['colors'])? $cache['colors'] : false;
		$lookupTable = isset($cache['lookuptable'])? $cache['lookuptable'] : false;
	
		// if a lookup table is provided (by a classic design set, store it as transient data to be used in other steps)
		if(is_array($lookupTable)) {
			// convert keys to lower case
			$smLookupTable = [];
	
			foreach($lookupTable as $key => $item) {
				$smLookupTable[strToLower($key)] = $item;
			} 
			
			set_transient('oxygen_vsb_source_color_lookup_table', $smLookupTable);
		}
		else {
			delete_transient('oxygen_vsb_source_color_lookup_table');
		}
	
		if(is_array($colors)) {
	
			$sourceColorMap = [];
	
			// set Name for the incoming colors
			$setName = sanitize_text_field($term_slug);
	
			if($overwrite) { // replace all old data
	
				$existing = [
					'colorsIncrement' => 0,
					'setsIncrement' => 1,
					'colors' => [],
					'sets' => [
						[
							'id' => 1,
							'name' => $setName,
						]
					],
				];
	
				foreach($colors as $key => $color) {
	
					$sourceColorMap[$color['id']] = ++$existing['colorsIncrement'];
	
					$color['id'] = $existing['colorsIncrement'];
	
					// assign the new Parent
					$color['set'] = 1;
	
					$existing['colors'][] = $color;
				}
	
				update_option('oxygen_vsb_global_colors', $existing);
	
			}
	
			else { // add to the existing data
				
				// colors are an array of arrays with each having a name and a value
				// get existing colors
	
				$existing = get_option('oxygen_vsb_global_colors', []);
	
				$existing['colors'];
	
				$existing['sets'];
	
				// find a set with that name
				$existingSetId = false;
	
				foreach($existing['sets'] as $key => $set) {
	
					if($set['name'] == $setName) {
						$existingSetId = $set['id'];
					}
	
					//remove hashkey attribute from the existing sets
					if(isset($existing['sets'][$key]['$$hashKey']))
						unset($existing['sets'][$key]['$$hashKey']);
				}
	
				// if this set does not already exist, create it
				if($existingSetId === false) {
	
					$existingSetId = ++$existing['setsIncrement'];
	
					$existing['sets'][] = [
						'id' => $existingSetId, // and increment
						'name' => $setName
					];
	
				}
				
				// remove hash keys from all existing colors
				foreach($existing['colors'] as $key => $color) {
					if(isset($existing['colors'][$key]['$$hashKey']))
						unset($existing['colors'][$key]['$$hashKey']);
				}
	
				// for each of the incoming colors
				foreach($colors as $key => $color) {
	
					// if a color with the same name already exists in the same set, then over write it
					$existingColorUpdated = false;
	
					foreach($existing['colors'] as $eKey => $eColor) {
	
						if($eColor['name'] == $color['name'] && $eColor['set'] === $existingSetId) {
	
							$existing['colors'][$eKey]['value'] = strtolower($color['value']);
	
							$sourceColorMap[$color['id']] = $eColor['id'];
	
							$existingColorUpdated = true;
							break;
						}
	
					}
	
					//updating an existing color, so no need to add it as a new one, so skip the rest
					if($existingColorUpdated) {
						continue;
					}
	
					$sourceColorMap[$color['id']] = ++$existing['colorsIncrement'];
	
					// add a new ID for adjusting it into the existing colors array
					$color['id'] = $existing['colorsIncrement'];
	
					// assign the new Parent
					$color['set'] = $existingSetId;
	
					$color['value'] = strtolower($color['value']);
	
					// add the color to the existing colors;
	
					$existing['colors'][] = $color;
	
				}
	
				update_option('oxygen_vsb_global_colors', $existing);
			}
	
			set_transient('oxygen_vsb_source_color_map', $sourceColorMap);
	
		}

		wp_send_json_success( null );

		wp_die();
	}

	/**
	 * @see ct_setup_default_stylesheets
	 */
	public function wizard_stylesheets() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The license id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The term slug is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_string( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The term slug should string', 'asura-connector' )
				),
				400
			);
		}

		$provider_id = $_REQUEST['provider_id'];
		$license_id = $_REQUEST['license_id'];
		$term_slug = $_REQUEST['term_slug'];
		$overwrite = $_REQUEST['overwrite'] === 'true';

		$provider = (object) DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $provider_id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$license = (object) DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id',
			'hash',
		],
		[
			'provider_id' => $provider_id,
			'id'          => $license_id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$cache = Cache::remember( "stylesheets_{$license->provider_id}_{$license->id}_{$term_slug}", Carbon::now()->addHour(), function () use ( $provider, $license, $term_slug ) {
			$response = Asura::oxygenbuilder_stylesheets( $provider, $license->hash, $term_slug );

			if ( $response->status() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve stylesheets for license id {$license->id} and term slug {$term_slug}. http error code: {$response->status()}" );

				return null;
			}

			return json_decode( $response->body(), true )['data'];
		} );

		if ( ! $cache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve stylesheets, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		$folderName = sanitize_text_field($term_slug);
		$stylesheets = $cache;

		if(!is_array($stylesheets)) {
			wp_send_json_error(
				new WP_Error(
					'invalid_data_type',
					__( "Couldn't retrieve stylesheets, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		// all the incoming stylesheets should go under a folder with the name of the source site

		// append to existing style sheets
		$existing = get_option('ct_style_sheets', []);

		// find the topmost ID value in the existing stylesheets.
		$id = 0;
		$folder_id = false;
		// lets assume that this is not the old data, for now
		foreach($existing as $key => $value) {
			if(is_array($value) && isset($value['id']) && intval($value['id']) > $id) {
				$id = intval($value['id']);
			}

			// in the process also look for a folder that has the name same as $folderName
			// if such a folder already exist, grab its ID. 
			if(isset($value['folder']) && intval($value['folder']) === 1  && $value['name'] === $folderName) {
				$folder_id = $value['id'];
			}
		}
		

		$processedStylesheets = [];

		// if a folder with the name of the $folderName does not exist
		// create a new folder in the incoming data, with the name of the source site.

		if($folder_id === false) {
			$folder_id = ++$id;
			$processedStylesheets[] = [ 'id' => $folder_id, 'name' => $folderName, 'folder' => 1, 'status' => 1 ];
		}

		//convert old style data and assign the new ID's
		foreach($stylesheets as $key => $value) {
			if(!is_array($value)) { // if it is the old style sheets data

				$processedStylesheets[] = [ 'id' => ++$id, 'name' => $key, 'css' => $value, 'parent' => $folder_id, 'status' => 1 ];

			} else {

				// if it is not a folder
				if(!isset($value['folder']) || intval($value['folder']) !== 1) {
					$value['id'] = ++$id; // replace the id in the new style data as well
					$value['parent'] = $folder_id; // make it the child of the new parent

					$processedStylesheets[] = $value;
				}
				
			}
		}

		// now if we are keeping the existing data
		if(is_array($existing) && !$overwrite) {
			// disable all existing folders
			foreach($existing as $key => $item) {
				if(isset($item['folder']) && intval($item['folder']) === 1) {
					// unless the folder has the same name as that of $folderName
					if($item['name'] !== $folderName) {
						$existing[$key]['status'] = 0;
					}
				}
				else { // if it is a stylesheet

					// if an incoming stylsheet has the same name as this one? delete this
					$remove = false;

					foreach($processedStylesheets as $incoming) {
						
						if(!$remove && (!isset($incoming['folder']) || intval($incoming['folder']) === 0) && $incoming['name'] === $item['name']) {
							
							$remove = true;
						}
					}

					if($remove) {
						unset($existing[$key]);
					} // else if it does not belong to a folder
					elseif(!isset($item['parent']) || intval($item['parent']) === 0) {

						$existing[$key]['parent'] = -1; // disable it
						
					}
				}
			}

			$processedStylesheets = array_merge($existing, $processedStylesheets);

		}

		update_option('ct_style_sheets', $processedStylesheets, get_option("oxygen_options_autoload"));

		wp_send_json_success( null );

		wp_die();
	}
	
	/**
	 * @see ct_setup_default_settings
	 */
	public function wizard_settings() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The license id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The term slug is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_string( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The term slug should string', 'asura-connector' )
				),
				400
			);
		}

		$provider_id = $_REQUEST['provider_id'];
		$license_id = $_REQUEST['license_id'];
		$term_slug = $_REQUEST['term_slug'];
		$overwrite = $_REQUEST['overwrite'] === 'true';

		$provider = (object) DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $provider_id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$license = (object) DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id',
			'hash',
		],
		[
			'provider_id' => $provider_id,
			'id'          => $license_id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$cache = Cache::remember( "settings_{$license->provider_id}_{$license->id}_{$term_slug}", Carbon::now()->addHour(), function () use ( $provider, $license, $term_slug ) {
			$response = Asura::oxygenbuilder_settings( $provider, $license->hash, $term_slug );

			if ( $response->status() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve settings for license id {$license->id} and term slug {$term_slug}. http error code: {$response->status()}" );

				return null;
			}

			return json_decode( $response->body(), true )['data'];
		} );

		if ( ! $cache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve settings, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		$siteName = sanitize_text_field($term_slug);
		$settings = $cache;
		
		if(!is_array($settings)) {
			wp_send_json_error(
				new WP_Error(
					'invalid_data_type',
					__( "Couldn't retrieve settings, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		// map colors
		global $ct_source_color_map;
		global $oxygen_vsb_classic_designsets;

		$ct_source_color_map = get_transient('oxygen_vsb_source_color_map', []);

		if(sizeof($ct_source_color_map) > 0) {
			// replace all global color values to match with the imported ones
			$settings = ct_map_source_colors($settings);

		}

		if(in_array($siteName, $oxygen_vsb_classic_designsets)) { // if it is an old design set
			// generate new variable colors
			global $oxygen_vsb_global_colors;

			// if a lookup table is avaibale?
			$lookupTable = get_transient('oxygen_vsb_source_color_lookup_table', []);

			$settings = ct_create_variable_colors($settings, $siteName, $lookupTable);
			update_option('oxygen_vsb_global_colors', $oxygen_vsb_global_colors);
		}

		// append to existing style sheets
		$existing = get_option('ct_global_settings', []);

		if(is_array($existing) && !$overwrite) {
			$settings = array_merge($existing, $settings);
		}

		update_option('ct_global_settings', $settings);

		wp_send_json_success( null );

		wp_die();
	}
	
	/**
	 * @see ct_setup_default_stylesets
	 */
	public function wizard_stylesets() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The license id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The term slug is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_string( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The term slug should string', 'asura-connector' )
				),
				400
			);
		}

		$provider_id = $_REQUEST['provider_id'];
		$license_id = $_REQUEST['license_id'];
		$term_slug = $_REQUEST['term_slug'];
		$overwrite = $_REQUEST['overwrite'] === 'true';

		$provider = (object) DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $provider_id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$license = (object) DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id',
			'hash',
		],
		[
			'provider_id' => $provider_id,
			'id'          => $license_id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$cache = Cache::remember( "stylesets_{$license->provider_id}_{$license->id}_{$term_slug}", Carbon::now()->addHour(), function () use ( $provider, $license, $term_slug ) {
			$response = Asura::oxygenbuilder_stylesets( $provider, $license->hash, $term_slug );

			if ( $response->status() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve stylesets for license id {$license->id} and term slug {$term_slug}. http error code: {$response->status()}" );

				return null;
			}

			return json_decode( $response->body(), true )['data'];
		} );

		if ( ! $cache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve stylesets, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		// given that the classes were processed earlier, the folder structure should already be in place
		// process the incoming sets and re-assign parent

		$folderName = sanitize_text_field($term_slug);
		$stylesets = $cache;
		
		if(!is_array($stylesets)) {
			wp_send_json_error(
				new WP_Error(
					'invalid_data_type',
					__( "Couldn't retrieve stylesets, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		foreach($stylesets as $key => $set) {
			$stylesets[$key]['parent'] = $folderName;
		}

		// append to existing style sheets
		$existing = get_option('ct_style_sets', []);

		// if there is an incoming 'uncategorized style set', remove it
		
		if(isset($stylesets['Uncategorized Custom Selectors'])) {
			unset($stylesets['Uncategorized Custom Selectors']);
		}
		
		// have all the custom selectors 
		
		if(is_array($existing) && !$overwrite) {

			// the existing folders are already deleted while processing the classes

			foreach($existing as $key => $set) {

				if( !isset($set['parent']) || empty($set['parent'])) {
					$existing[$key]['parent'] = -1;
				}
			}

			$stylesets = array_merge($existing, $stylesets);
		}

		update_option('ct_style_sets', $stylesets);

		wp_send_json_success( null );

		wp_die();
	}
	
	/**
	 * @see ct_setup_default_selectors
	 */
	public function wizard_selectors() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The license id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The term slug is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_string( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The term slug should string', 'asura-connector' )
				),
				400
			);
		}

		$provider_id = $_REQUEST['provider_id'];
		$license_id = $_REQUEST['license_id'];
		$term_slug = $_REQUEST['term_slug'];
		$overwrite = $_REQUEST['overwrite'] === 'true';

		$provider = (object) DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $provider_id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$license = (object) DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id',
			'hash',
		],
		[
			'provider_id' => $provider_id,
			'id'          => $license_id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$cache = Cache::remember( "selectors_{$license->provider_id}_{$license->id}_{$term_slug}", Carbon::now()->addHour(), function () use ( $provider, $license, $term_slug ) {
			$response = Asura::oxygenbuilder_selectors( $provider, $license->hash, $term_slug );

			if ( $response->status() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve selectors for license id {$license->id} and term slug {$term_slug}. http error code: {$response->status()}" );

				return null;
			}

			return json_decode( $response->body(), true )['data'];
		} );

		if ( ! $cache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve selectors, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		$site_name = sanitize_text_field($term_slug);
		$selectors = $cache;
		
		if(!is_array($selectors)) {
			wp_send_json_error(
				new WP_Error(
					'invalid_data_type',
					__( "Couldn't retrieve selectors, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		// map colors
		global $ct_source_color_map;
		global $oxygen_vsb_classic_designsets;

		$ct_source_color_map = get_transient('oxygen_vsb_source_color_map', []);

		if(sizeof($ct_source_color_map) > 0) {
			// replace all global color values to match with the imported ones
			$selectors = ct_map_source_colors($selectors);
		}

		// if selectors have their parent as a 'Uncategorized Custom Selectors', rename their parent to 
		if(in_array($site_name, $oxygen_vsb_classic_designsets)) { // if it is an old design set
			// generate new variable colors
			global $oxygen_vsb_global_colors;
			// if a lookup table is avaibale?
			$lookupTable = get_transient('oxygen_vsb_source_color_lookup_table', []);
			$selectors = ct_create_variable_colors($selectors, $site_name, $lookupTable);
			update_option('oxygen_vsb_global_colors', $oxygen_vsb_global_colors);
		}

		$create_default_styleset = false;

		foreach($selectors as $key => $item) {
			if($item['set_name'] === 'Uncategorized Custom Selectors') {
				$create_default_styleset = true;
				$selectors[$key]['set_name'] = $site_name.' style set';
			}
		}

		if($create_default_styleset) {
			$existing = get_option('ct_style_sets', []);
			$existing[$site_name.' style set'] = ['key' => $site_name.' style set', 'parent' => $site_name];
			update_option('ct_style_sets', $existing);
		}

		// append to existing style sheets
		$existing = get_option('ct_custom_selectors', []);
		
		if(is_array($existing) && !$overwrite) {
			$selectors = array_merge($existing, $selectors);
		}

		update_option('ct_custom_selectors', $selectors, get_option("oxygen_options_autoload"));

		wp_send_json_success( null );

		wp_die();
	}
	
	/**
	 * @see ct_setup_default_templates
	 */
	public function wizard_templates() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The license id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The term slug is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_string( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The term slug should string', 'asura-connector' )
				),
				400
			);
		}

		$provider_id = $_REQUEST['provider_id'];
		$license_id = $_REQUEST['license_id'];
		$term_slug = $_REQUEST['term_slug'];
		$overwrite = $_REQUEST['overwrite'] === 'true';

		$provider = (object) DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $provider_id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$license = (object) DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id',
			'hash',
		],
		[
			'provider_id' => $provider_id,
			'id'          => $license_id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$cache = Cache::remember( "templates_{$license->provider_id}_{$license->id}_{$term_slug}", Carbon::now()->addHour(), function () use ( $provider, $license, $term_slug ) {
			$response = Asura::oxygenbuilder_templates( $provider, $license->hash, $term_slug );

			if ( $response->status() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve templates for license id {$license->id} and term slug {$term_slug}. http error code: {$response->status()}" );

				return null;
			}

			return json_decode( $response->body(), true )['data'];
		} );

		if ( ! $cache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve templates, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		$siteName = sanitize_text_field($term_slug);
		$templates = $cache;
		
		if(!is_array($templates)) {
			wp_send_json_error(
				new WP_Error(
					'invalid_data_type',
					__( "Couldn't retrieve templates, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		global $wpdb;
		
		// take care of the existing templates
		$existing = $wpdb->get_results(
				"SELECT id, post_title
				FROM $wpdb->posts as post
				WHERE post_type = 'ct_template'
				AND post.post_status IN ('publish')"
			);

		foreach($existing as $template) {
			if($overwrite) {
				wp_delete_post($template->id);
			}
			else {
				// unset the template
				delete_post_meta($template->id, 'ct_template_single_all');
				delete_post_meta($template->id, 'ct_template_post_types');
				delete_post_meta($template->id, 'ct_use_template_taxonomies');
				delete_post_meta($template->id, 'ct_template_apply_if_post_of_parents');

				delete_post_meta($template->id, 'ct_template_all_archives');
				delete_post_meta($template->id, 'ct_template_apply_if_archive_among_taxonomies');
				delete_post_meta($template->id, 'ct_template_apply_if_archive_among_cpt');
				delete_post_meta($template->id, 'ct_template_apply_if_archive_among_authors');
				delete_post_meta($template->id, 'ct_template_date_archive');

				delete_post_meta($template->id, 'ct_template_front_page');
				delete_post_meta($template->id, 'ct_template_blog_posts');
				delete_post_meta($template->id, 'ct_template_search_page');
				delete_post_meta($template->id, 'ct_template_404_page');
				delete_post_meta($template->id, 'ct_template_index');

				// and rename
				if(strpos($template->post_title, 'inactive - ') === false) {
					wp_update_post([
						'ID' => $template->id,
						'post_title' => 'inactive - '.$template->post_title
					]);
				}
			}
		}

		$new_id_map = [];

		// save all class names to the transient. These will be used in the last step to download selective classes
		$selectiveClasses = [];

		$current_user = wp_get_current_user();

		// insert posts
		foreach($templates as $template) {
			
			$post_data = [
				'ID' => 0,
				'post_title' => $template['post_title'],
				'post_type' => 'ct_template',
				'post_status' => 'publish'
			];
		
			if($current_user && isset($current_user->ID)) {
				$post_data['post_author'] = $current_user->ID;
			}

			$new_id_map[$template['ID']] = wp_insert_post($post_data);
			
			foreach($template['applied_classes'] as $key => $val) {
				$selectiveClasses[$key] = $val;			
			}		

		}
		
		set_transient('oxygen-vsb-default-setup-classes', $selectiveClasses);

		set_transient('oxygen-vsb-templates-id-map', $new_id_map);

		global $ct_source_color_map;
		global $oxygen_vsb_classic_designsets;
		global $oxygen_vsb_global_colors;

		$ct_source_color_map = get_transient('oxygen_vsb_source_color_map', []);

		// update post meta
		foreach($templates as $template) {

			// TODO run through the shortcodes, if some re-usable in place, replace its id from the $new_id_map
			$shortcodes = parse_shortcodes($template['builder_shortcodes'], false, false);

			$shortcodes['content'] = ct_swap_reusable_view_ids( $shortcodes['content'], $new_id_map );

			// map colors
			if(sizeof($ct_source_color_map) > 0) {
				// replace all global color values to match with the imported ones
				$shortcodes['content'] = ct_map_source_colors($shortcodes['content']);
			}

			if(in_array($siteName, $oxygen_vsb_classic_designsets)) { // if it is an old design set
				// generate new variable colors
				// if a lookup table is avaibale?
				$lookupTable = get_transient('oxygen_vsb_source_color_lookup_table', []);
				$shortcodes['content'] = ct_create_variable_colors($shortcodes['content'], $siteName, $lookupTable);
			}

			$wrap_shortcodes = [];

			$wrap_shortcodes['children'] = $shortcodes['content'];
			// code tree back to JSON to pass into old function
			$components_tree_json = json_encode( $wrap_shortcodes );
			
			ob_start();
		
			// transform JSON to shortcodes
			$shortcodes = components_json_to_shortcodes( $components_tree_json );
		
			// we don't need anything to be output by custom shortcodes
			ob_clean();

			update_post_meta($new_id_map[$template['ID']], 'ct_builder_shortcodes', $shortcodes);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_type', $template['template_type']);

			// cache styles
			oxygen_vsb_cache_page_css($new_id_map[$template['ID']], $shortcodes);

			if(isset($template['template_type']) && $template['template_type'] == 'reusable_part') { // store the source parameters to check for redundancy while importing re-usables again
				update_post_meta($new_id_map[$template['ID']], 'ct_source_site', $siteName);
				update_post_meta($new_id_map[$template['ID']], 'ct_source_post', $template['ID']);
			}

			update_post_meta($new_id_map[$template['ID']], 'ct_template_order', $template['template_order']);
			update_post_meta($new_id_map[$template['ID']], 'ct_parent_template', $new_id_map[$template['parent_template']]);

			update_post_meta($new_id_map[$template['ID']], 'ct_template_single_all', $template['template_single_all']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_post_types', $template['template_post_types']);
			update_post_meta($new_id_map[$template['ID']], 'ct_use_template_taxonomies', $template['use_template_taxonomies']);
			
			// match id to slug for each taxonomy
			if(is_array($template['template_taxonomies'])) {
				foreach($template['template_taxonomies']['values'] as $key => $val) {
					// get id for the slug
					$term = get_term_by('slug', $val, $template['template_taxonomies']['names'][$key]);
					
					if($term) {
						$template['template_taxonomies']['values'][$key] = $term->term_id;
					}
					else {
						if(isset($template['template_taxonomies'])) {
							unset($template['template_taxonomies']['names'][$key]);
							unset($template['template_taxonomies']['values'][$key]);
						}
					}

				}
			}

			update_post_meta($new_id_map[$template['ID']], 'ct_template_taxonomies', $template['template_taxonomies']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_apply_if_post_of_parents', $template['template_apply_if_post_of_parents']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_post_of_parents', $template['template_post_of_parents']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_all_archives', $template['template_all_archives']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_apply_if_archive_among_taxonomies', $template['template_apply_if_archive_among_taxonomies']);

			// match id to slug for each taxonomy
			if(isset($template['template_archive_among_taxonomies']) && is_array($template['template_archive_among_taxonomies'])) {
				foreach($template['template_archive_among_taxonomies'] as $key => $val) {
					// get id for the slug
					if(is_array($val)) {
						$term = get_term_by('slug', $val['slug'], $val['taxonomy']);	
						if($term) {
							$template['template_archive_among_taxonomies'][$key] = $term->term_id;
						}
						else {
							unset($template['template_archive_among_taxonomies'][$key]);
						}
					}

				}
			}

			update_post_meta($new_id_map[$template['ID']], 'ct_template_archive_among_taxonomies', $template['template_archive_among_taxonomies']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_apply_if_archive_among_cpt', $template['template_apply_if_archive_among_cpt']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_archive_post_types', $template['template_archive_post_types']);
			// update_post_meta($new_id_map[$template['ID']], 'ct_template_apply_if_archive_among_authors', $template['template_apply_if_archive_among_authors']);
			// update_post_meta($new_id_map[$template['ID']], 'ct_template_authors_archives', $template['template_authors_archives']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_date_archive', $template['template_date_archive']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_front_page', $template['template_front_page']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_blog_posts', $template['template_blog_posts']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_search_page', $template['template_search_page']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_404_page', $template['template_404_page']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_index', $template['template_index']);
			update_post_meta($new_id_map[$template['ID']], 'ct_template_inner_content', $template['ct_template_inner_content']);
		}

		if(in_array($siteName, $oxygen_vsb_classic_designsets)) { // then new color variables must have been created
			update_option('oxygen_vsb_global_colors', $oxygen_vsb_global_colors);
		}

		wp_send_json_success( null );

		wp_die();
	}
	
	/**
	 * @see ct_setup_default_pages
	 */
	public function wizard_pages() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The license id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The term slug is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_string( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The term slug should string', 'asura-connector' )
				),
				400
			);
		}

		$provider_id = $_REQUEST['provider_id'];
		$license_id = $_REQUEST['license_id'];
		$term_slug = $_REQUEST['term_slug'];
		$overwrite = $_REQUEST['overwrite'] === 'true';

		$provider = (object) DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $provider_id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$license = (object) DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id',
			'hash',
		],
		[
			'provider_id' => $provider_id,
			'id'          => $license_id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$cache = Cache::remember( "pages_{$license->provider_id}_{$license->id}_{$term_slug}", Carbon::now()->addHour(), function () use ( $provider, $license, $term_slug ) {
			$response = Asura::oxygenbuilder_pages( $provider, $license->hash, $term_slug );

			if ( $response->status() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve pages for license id {$license->id} and term slug {$term_slug}. http error code: {$response->status()}" );

				return null;
			}

			return json_decode( $response->body(), true )['data'];
		} );

		if ( ! $cache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve pages, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		$siteName = sanitize_text_field($term_slug);
		$pages = $cache;
		
		if(!is_array($pages)) {
			wp_send_json_error(
				new WP_Error(
					'invalid_data_type',
					__( "Couldn't retrieve pages, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		$templates_id_map = get_transient('oxygen-vsb-templates-id-map');

		delete_transient('oxygen-vsb-templates-id-map');
	
		global $ct_source_color_map;
	
		$ct_source_color_map = get_transient('oxygen_vsb_source_color_map', []);
	
		$new_id_map = [];
	
		$selectiveClasses = get_transient('oxygen-vsb-default-setup-classes', []);
	
		$current_user = wp_get_current_user();
	
		// insert posts
		foreach($pages as $page) {
	
			$post_data = $page;
			
			unset($post_data['ID']);
	
			$post_data['post_type'] = 'page';
			$post_data['post_status'] = 'publish';
			
			if($current_user && isset($current_user->ID)) {
				$post_data['post_author'] = $current_user->ID;
			}
	
			$new_id_map[$page['ID']] = wp_insert_post($post_data);
			foreach($page['applied_classes'] as $key => $val) {
				$selectiveClasses[$key] = $val;			
			}
		}
		
		set_transient('oxygen-vsb-default-setup-classes', $selectiveClasses);
	
		global $oxygen_vsb_classic_designsets, $oxygen_vsb_global_colors;
	
		foreach($pages as $page) {

			// update parent status
			$post_data = [
				'ID' => $new_id_map[$page['ID']],
				'post_parent' => $new_id_map[$page['post_parent']],
			];
	
			wp_update_post($post_data);
	
			// TODO run through the shortcodes, if some re-usable in place, replace its id from the $new_id_map
			$shortcodes = parse_shortcodes($page['builder_shortcodes'], false, false);
	
			//$shortcodes['content'] = ct_swap_reusable_view_ids( $shortcodes['content'], $templates_id_map );
	
			// map colors
			if(sizeof($ct_source_color_map) > 0) {
				// replace all global color values to match with the imported ones
				$shortcodes['content'] = ct_map_source_colors($shortcodes['content']);
			}
	
			if(in_array($siteName, $oxygen_vsb_classic_designsets)) { // if it is an old design set
				// generate new variable colors
				// if a lookup table is avaibale?
				$lookupTable = get_transient('oxygen_vsb_source_color_lookup_table', []);
				$shortcodes['content'] = ct_create_variable_colors($shortcodes['content'], $siteName, $lookupTable);
			}
	
			$wrap_shortcodes = [];
	
			$wrap_shortcodes['children'] = $shortcodes['content'];
			// code tree back to JSON to pass into old function
			$components_tree_json = json_encode( $wrap_shortcodes );
			
			ob_start();
		
			// transform JSON to shortcodes
			$shortcodes = components_json_to_shortcodes( $components_tree_json );
		
			// we don't need anything to be output by custom shortcodes
			ob_clean();
	
			update_post_meta($new_id_map[$page['ID']], 'ct_builder_shortcodes', $shortcodes);
			update_post_meta($new_id_map[$page['ID']], 'ct_other_template', (isset($templates_id_map[$page['other_template']])?$templates_id_map[$page['other_template']]:$page['other_template']));
	
			// cache styles
			oxygen_vsb_cache_page_css($new_id_map[$page['ID']], $shortcodes);
		}
	
		update_option('oxygen_vsb_global_colors', $oxygen_vsb_global_colors);

		wp_send_json_success( null );

		wp_die();
	}
	
	/**
	 * @see ct_setup_default_classes
	 */
	public function wizard_classes() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The license id should numeric', 'asura-connector' )
				),
				400
			);
		}

		if ( empty( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The term slug is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_string( $_REQUEST['term_slug'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The term slug should string', 'asura-connector' )
				),
				400
			);
		}

		$provider_id = $_REQUEST['provider_id'];
		$license_id = $_REQUEST['license_id'];
		$term_slug = $_REQUEST['term_slug'];
		$overwrite = $_REQUEST['overwrite'] === 'true';

		$provider = (object) DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $provider_id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$license = (object) DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id',
			'hash',
		],
		[
			'provider_id' => $provider_id,
			'id'          => $license_id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$cache = Cache::remember( "classes_{$license->provider_id}_{$license->id}_{$term_slug}", Carbon::now()->addHour(), function () use ( $provider, $license, $term_slug ) {
			$response = Asura::oxygenbuilder_classes( $provider, $license->hash, $term_slug );

			if ( $response->status() !== 200 ) {
				error_log( "asura-connector [error]: couldn't retrieve classes for license id {$license->id} and term slug {$term_slug}. http error code: {$response->status()}" );

				return null;
			}

			return json_decode( $response->body(), true )['data'];
		} );

		if ( ! $cache ) {
			wp_send_json_error(
				new WP_Error(
					'asura_connection_error',
					__( "Couldn't retrieve classes, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}

		$folderName = sanitize_text_field($term_slug);
		$classes = $cache;
		
		if(!is_array($classes)) {
			wp_send_json_error(
				new WP_Error(
					'invalid_data_type',
					__( "Couldn't retrieve classes, please contact design set provider or plugin developer", 'asura-connector' )
				),
				500
			);
		}
		
		// if there are selective classes to be downloaded
		$selectiveClasses = get_transient('oxygen-vsb-default-setup-classes');
		$selectiveClasses = $selectiveClasses !== false ? $selectiveClasses : []; 

		delete_transient('oxygen-vsb-default-setup-classes');

		if(sizeof($selectiveClasses) > 0) {

			foreach($selectiveClasses as $key => $classItem) {
				if(isset($classes[$key])) {
					$selectiveClasses[$key] = $classes[$key];
				}
			}

			$classes = $selectiveClasses;
		}
		
		// map colors
		global $ct_source_color_map;
		global $oxygen_vsb_classic_designsets;

		$ct_source_color_map = get_transient('oxygen_vsb_source_color_map', []);

		delete_transient('oxygen_vsb_source_color_map');

		if(sizeof($ct_source_color_map) > 0) {
			// replace all global color values to match with the imported ones
			$classes = ct_map_source_colors($classes);
		}

		if(in_array($folderName, $oxygen_vsb_classic_designsets)) { // if it is an old design set
			// generate new variable colors
			global $oxygen_vsb_global_colors;
			// if a lookup table is avaibale?
			$lookupTable = get_transient('oxygen_vsb_source_color_lookup_table', []);
			$classes = ct_create_variable_colors($classes, $folderName, $lookupTable);
			update_option('oxygen_vsb_global_colors', $oxygen_vsb_global_colors);
		}

		// existing classes
		$existing = get_option('ct_components_classes', []);

		$folders = get_option('ct_style_folders', []);

		$newFolders = [];

		if(!isset($folders[$folderName])) {
			$newFolders[$folderName] = [
				'key' => $folderName,
				'status' => 1
			];
		}
		else {
			$newFolders[$folderName] = [
				'key' => $folderName,
				'status' => $folders[$folderName]['status']
			];
		}
		
		foreach($classes as $key => $incoming) {
			$classes[$key]['parent'] = $folderName;
		}

		if(is_array($existing) && !$overwrite) {

			// disable all folders, now this will also take care of stylesets folders

			foreach($folders as $key => $folder) {
				// unless the folder is that of the incoming site
				if($folder['name'] !== $folderName) {
					$folders[$key]['status'] = 0;
				}
			}

			// and disable the classes that dont belong to folders
			foreach($existing as $key => $class) {

				if( !isset($class['parent']) || empty($class['parent'])) {
					$existing[$key]['parent'] = -1;
				}
			}

			$classes = array_merge($existing, $classes); // this will overwrite existing classes
			$newFolders = array_merge($folders, $newFolders); // this will overwrite any exisiting folder with the same name
		}

		update_option('ct_components_classes', $classes, get_option("oxygen_options_autoload"));

		global $oxygen_vsb_css_classes; // in order to have the latest classes available for generating cache
		$oxygen_vsb_css_classes = $classes;

		update_option('ct_style_folders', $newFolders);

		wp_send_json_success( null );

		wp_die();
	}

	public function add_provider() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['connector'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The connector field is required', 'asura-connector' )
				),
				400
			);
		}

		$connector = $_REQUEST['connector'];

		if ( ! base64_decode( $connector ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'Connector should be encoded with base64.', 'asura-connector' )
				),
				422
			);
		}

		if ( ! is_scalar( $connector ) && ! method_exists( $connector, '__toString' ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'Connector should be json string. [1]', 'asura-connector' )
				),
				422
			);
		}

		$connector = json_decode( base64_decode( $connector ) );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'Connector should be json string. [2]', 'asura-connector' )
				),
				422
			);
		}

		if ( ! is_object( $connector )
		     || ! isset( $connector->site_title )
		     || ! isset( $connector->provider )
		     || ! isset( $connector->endpoint )
		     || ! isset( $connector->version )
		     || ! isset( $connector->api_key )
		     || ! isset( $connector->api_secret )
		) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'Connector doesn\'t contain valid API config.', 'asura-connector' )
				),
				422
			);
		}

		$insert = DB::insert( Provider::TABLE_NAME, [
			'api_key'    => $connector->api_key,
			'api_secret' => $connector->api_secret,
			'site_title' => $connector->site_title,
			'provider'   => $connector->provider,
			'endpoint'   => $connector->endpoint,
			'version'    => $connector->version,
			'created_at' => Carbon::now()->toDateTimeString(),
			'updated_at' => Carbon::now()->toDateTimeString(),
		] );

		if ( ! $insert ) {
			wp_send_json_error(
				new WP_Error(
					'internal_error',
					__( 'Failed to add the provider to database', 'asura-connector' )
				),
				500
			);
		}

		wp_send_json_success();

		wp_die();
	}

	public function list_providers() {
		check_ajax_referer( 'asura-connector-admin' );

		$providers = DB::select(
			Provider::TABLE_NAME,
			[
				'id [Int]',
				'provider',
				'site_title',
				'api_key',
				'api_secret',
				'endpoint',
				'version',
				'status [Bool]',
				'created_at',
				'updated_at'
			],
			[
				'ORDER' => [ 'id' => 'DESC' ],
			]
		);

		wp_send_json_success( $providers );

		wp_die();
	}

	public function delete_provider() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		$id = $_REQUEST['provider_id'];

		$exist = DB::get( Provider::TABLE_NAME, 'id', [
			'id' => $id,
		] );

		if ( ! $exist ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		DB::delete( Provider::TABLE_NAME, [
			'id' => $id,
		] );

		DB::delete( License::TABLE_NAME, [
			'provider_id' => $id
		] );

		wp_send_json_success();

		wp_die();
	}

	public function get_provider() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		$id = $_REQUEST['provider_id'];

		$exist = DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
			'created_at',
			'updated_at'
		], [
			'id' => $id,
		] );

		if ( ! $exist ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		wp_send_json_success( $exist );

		wp_die();
	}

	public function edit_provider() {
		check_ajax_referer( 'asura-connector-admin' );

		$validation = new WP_Error();

		if ( empty( $_REQUEST['provider_id'] ) ) {
			$validation->add( 'missing_field', __( 'The provider id field is required', 'asura-connector' ) );
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			$validation->add( 'missing_field', __( 'The provider id should numeric', 'asura-connector' ) );
		}
		if ( empty( $_REQUEST['provider'] ) ) {
			$validation->add( 'missing_field', __( 'The provider field is required', 'asura-connector' ) );
		}
		if ( empty( $_REQUEST['site_title'] ) ) {
			$validation->add( 'missing_field', __( 'The site title field is required', 'asura-connector' ) );
		}
		if ( empty( $_REQUEST['api_key'] ) ) {
			$validation->add( 'missing_field', __( 'The api key field is required', 'asura-connector' ) );
		}
		if ( empty( $_REQUEST['api_secret'] ) ) {
			$validation->add( 'missing_field', __( 'The api secret field is required', 'asura-connector' ) );
		}
		if ( empty( $_REQUEST['endpoint'] ) ) {
			$validation->add( 'missing_field', __( 'The endpoint field is required', 'asura-connector' ) );
		}
		if ( empty( $_REQUEST['version'] ) ) {
			$validation->add( 'missing_field', __( 'The version field is required', 'asura-connector' ) );
		}

		if ( $validation->has_errors() ) {
			wp_send_json_error(
				$validation,
				400
			);
		}

		$id = $_REQUEST['provider_id'];

		$exist = DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
		], [
			'id' => $id,
		] );

		if ( ! $exist ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}


		$exist = (object) $exist;

		$updatedData = [];

		if ( $exist->provider !== $_REQUEST['provider'] ) {
			$updatedData['provider'] = $_REQUEST['provider'];
		}
		if ( $exist->site_title !== $_REQUEST['site_title'] ) {
			$updatedData['site_title'] = $_REQUEST['site_title'];
		}
		if ( $exist->api_key !== $_REQUEST['api_key'] ) {
			$updatedData['api_key'] = $_REQUEST['api_key'];
		}
		if ( $exist->api_secret !== $_REQUEST['api_secret'] ) {
			$updatedData['api_secret'] = $_REQUEST['api_secret'];
		}
		if ( $exist->endpoint !== $_REQUEST['endpoint'] ) {
			$updatedData['endpoint'] = $_REQUEST['endpoint'];
		}
		if ( $exist->version !== $_REQUEST['version'] ) {
			$updatedData['version'] = $_REQUEST['version'];
		}

		if ( ! empty ( $updatedData ) ) {
			$updatedData['updated_at'] = Carbon::now()->toDateTimeString();

			$update = DB::update( Provider::TABLE_NAME, $updatedData, [ 'id' => $id, ] );

			if ( ! $update ) {
				wp_send_json_error(
					new WP_Error(
						'internal_error',
						__( 'Failed to add update the provider in database', 'asura-connector' )
					),
					500
				);
			}

			wp_send_json_success( $update );
		}

		wp_die();
	}

	public function list_licenses() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		$id = $_REQUEST['provider_id'];

		$exist = DB::get( Provider::TABLE_NAME, [
			'id [Int]',
		], [
			'id' => $id,
		] );

		if ( ! $exist ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$licenses = DB::select(
			License::TABLE_NAME,
			[
				'id [Int]',
				'provider_id [Int]',
				'license',
				'status [Bool]',
				'created_at',
				'updated_at'
			],
			[
				'provider_id' => $id,
				'ORDER'       => [ 'id' => 'DESC' ],
			]
		);

		wp_send_json_success( $licenses );

		wp_die();
	}

	public function add_license() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		$id = $_REQUEST['provider_id'];

		$provider = DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
			'created_at',
			'updated_at'
		], [
			'id' => $id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}


		if ( empty( $_REQUEST['license_key'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license key field is required', 'asura-connector' )
				),
				400
			);
		}

		$license_key = $_REQUEST['license_key'];

		$response = Asura::license_domains_register( (object) $provider, $license_key );

		$body = json_decode( $response->body(), true );

		if ( $response->status() !== 200 ) {
			wp_send_json_error(
				new WP_Error(
					key( $body ),
					array_values( $body )[0]
				),
				$response->status()
			);
		}

		$insert = DB::insert( License::TABLE_NAME, [
			'provider_id' => $id,
			'license'     => $license_key,
			'hash'        => $body['data'][0]['hash'],
			'status'      => 1,
			'created_at'  => Carbon::now()->toDateTimeString(),
			'updated_at'  => Carbon::now()->toDateTimeString(),
		] );

		if ( ! $insert ) {
			wp_send_json_error(
				new WP_Error(
					'internal_error',
					__( 'Failed to add the provider to database', 'asura-connector' )
				),
				500
			);
		}

		wp_send_json_success( $insert );

		wp_die();
	}

	public function delete_license() {
		check_ajax_referer( 'asura-connector-admin' );

		if ( empty( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The provider id is required', 'asura-connector' )
				),
				400
			);
		} else if ( ! is_numeric( $_REQUEST['provider_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'invalid_field',
					__( 'The provider id should numeric', 'asura-connector' )
				),
				400
			);
		}

		$id = $_REQUEST['provider_id'];

		$provider = DB::get( Provider::TABLE_NAME, [
			'id [Int]',
			'provider',
			'site_title',
			'api_key',
			'api_secret',
			'endpoint',
			'version',
			'status [Bool]',
			'created_at',
			'updated_at'
		], [
			'id' => $id,
		] );

		if ( ! $provider ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The provider id is not exist', 'asura-connector' )
				),
				404
			);
		}

		if ( empty( $_REQUEST['license_id'] ) ) {
			wp_send_json_error(
				new WP_Error(
					'missing_field',
					__( 'The license id is required', 'asura-connector' )
				),
				400
			);
		}

		$license_id = $_REQUEST['license_id'];

		$license = DB::get( License::TABLE_NAME, [
			'id [Int]',
			'provider_id [Int]',
			'license'
		], [
			'id'          => $license_id,
			'provider_id' => $id,
		] );

		if ( ! $license ) {
			wp_send_json_error(
				new WP_Error(
					'record_not_exist',
					__( 'The license id is not exist', 'asura-connector' )
				),
				404
			);
		}

		$response = Asura::license_domains_deregister( (object) $provider, $license['license'] );

		$body = json_decode( $response->body(), true );

		if ( $response->status() !== 200 ) {
			wp_send_json_error(
				new WP_Error(
					key( $body ),
					array_values( $body )[0]
				),
				$response->status()
			);
		}

		$delete = DB::delete( License::TABLE_NAME, [
			'id'          => $license_id,
			'provider_id' => $id
		] );

		wp_send_json_success( $delete );

		wp_die();
	}

	public function clean_cache() {
		check_ajax_referer( 'asura-connector-admin' );

		Cache::flush();

		wp_send_json_success( null );

		wp_die();
	}

}