<?php

namespace TheLostAsura\Connector\Ajax;

use Illuminate\Support\Carbon;
use TheLostAsura\Connector\Lib\Asura;
use TheLostAsura\Connector\Models\License;
use TheLostAsura\Connector\Models\Provider;
use TheLostAsura\Connector\Utils\Cache;
use TheLostAsura\Connector\Utils\DB;
use WP_Error;

class Frontend {

    public function __construct() {
        $this->register();
    }

    public function register() {
		$this->register_ajaxs( $this->get_ajaxs() );
    }

    private function register_ajaxs( array $ajaxs ) {
        foreach ( $ajaxs as $tag => $ajax ) {
			$priority  = $ajax['priority'] ?? 10;
			$args      = $ajax['args'] ?? 1;

            add_action( "wp_ajax_asura_connector_{$tag}", $ajax['handler'], $priority, $args );
        }
    }

    private function get_ajaxs() : array {
        $ajaxs = [
            'list_designsets' => [
                'handler' => [ $this, 'list_designsets' ]
            ],
        ];

        return $ajaxs;
    }

    public function list_designsets() {
        check_ajax_referer( 'asura-connector-admin' );

        if ( empty( $_REQUEST[ 'provider_id' ] ) ) {
            wp_send_json_error( 
                new WP_Error( 
                    'missing_field', 
                    __( 'The provider id is required', 'asura-connector' ) 
                ),
                400
            );
        } else if ( ! is_numeric( $_REQUEST[ 'provider_id' ] ) ) {
            wp_send_json_error( 
                new WP_Error( 
                    'invalid_field', 
                    __( 'The provider id should numeric', 'asura-connector' ) 
                ),
                400
            );
        }
        
        $id = $_REQUEST[ 'provider_id' ];

        $provider = DB::get(Provider::TABLE_NAME, [
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
        ]);

        if ( ! $provider ) {
            wp_send_json_error( 
                new WP_Error( 
                    'record_not_exist', 
                    __( 'The provider id is not exist', 'asura-connector' ) 
                ),
                404
            );
        }

        $licenses = (object) DB::select(
            License::TABLE_NAME,
            [
                'id [Int]',
                'provider_id',
                'hash',
            ],
            [
                'provider_id' => $id,
            ]
        );

        $designsets = [];

        foreach ($licenses as $license) {
            $licenseObj = (object) $license;
            $termsCache = Cache::remember("terms_{$licenseObj->provider_id}_{$licenseObj->id}", Carbon::now()->addHour(), function () use ($provider, $licenseObj) {
                $response = Asura::license_terms_index((object) $provider, $licenseObj->hash);

                if( $response->status() !== 200 ) {
                    error_log( "asura-connector [error]: couldn't retrieve terms for license id {$licenseObj->id}. http error code: {$response->status()}" );
                    return [];
                }

                return json_decode($response->body(), true)['data'];
            });

            foreach ($termsCache as $term) {
                array_push($designsets, $term);
            }
        }

        wp_send_json_success($designsets);

        wp_die();
    }
}