<?php

namespace TheLostAsura\Connector\Ajax;

use Illuminate\Support\Carbon;
use TheLostAsura\Connector\Lib\Asura;
use TheLostAsura\Connector\Models\License;
use TheLostAsura\Connector\Models\Provider;
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
			$priority  = $ajax['priority'] ?? 10;
			$args      = $ajax['args'] ?? 1;

            add_action( "wp_ajax_asura_connector_{$tag}", $ajax['handler'], $priority, $args );
        }
    }

    private function get_ajaxs() : array {
        $ajaxs = [
            'add_provider' => [
                'handler' => [ $this, 'add_provider' ]
            ],
            'delete_provider' => [
                'handler' => [ $this, 'delete_provider' ]
            ],
            'list_providers' => [
                'handler' => [ $this, 'list_providers' ]
            ],
            'get_provider' => [
                'handler' => [ $this, 'get_provider' ]
            ],
            'edit_provider' => [
                'handler' => [ $this, 'edit_provider' ]
            ],
            'list_licenses' => [
                'handler' => [ $this, 'list_licenses' ]
            ],
            'add_license' => [
                'handler' => [ $this, 'add_license' ]
            ],
            'delete_license' => [
                'handler' => [ $this, 'delete_license' ]
            ],
        ];

        return $ajaxs;
    }

    public function add_provider() {
        check_ajax_referer( 'asura-connector-admin' );

        if ( empty( $_REQUEST[ 'connector' ] ) ) {
            wp_send_json_error( 
                new WP_Error( 
                    'missing_field', 
                    __( 'The connector field is required', 'asura-connector' ) 
                ),
                400
            );
        }

        $connector = $_REQUEST[ 'connector' ];

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

        $insert = DB::insert(Provider::TABLE_NAME, [
            'api_key' => $connector->api_key,
            'api_secret' => $connector->api_secret,
            'site_title' => $connector->site_title,
            'provider' => $connector->provider,
            'endpoint' => $connector->endpoint,
            'version' => $connector->version,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

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

        wp_send_json_success($providers);
    
        wp_die();
    }

    public function delete_provider() {
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

        $exist = DB::get(Provider::TABLE_NAME, 'id', [
            'id' => $id,
        ]);

        if ( ! $exist ) {
            wp_send_json_error( 
                new WP_Error( 
                    'record_not_exist', 
                    __( 'The provider id is not exist', 'asura-connector' ) 
                ),
                404
            );
        }

        DB::delete(Provider::TABLE_NAME, [
            'id' => $id,
        ]);

        DB::delete(License::TABLE_NAME, [
            'provider_id' => $id
        ]);
            
        wp_send_json_success();
    
        wp_die();
    }

    public function get_provider() {
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

        $exist = DB::get(Provider::TABLE_NAME, [
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
        ]);

        if ( ! $exist ) {
            wp_send_json_error( 
                new WP_Error( 
                    'record_not_exist', 
                    __( 'The provider id is not exist', 'asura-connector' ) 
                ),
                404
            );
        }

        wp_send_json_success($exist);
    
        wp_die();
    }

    public function edit_provider() {
        check_ajax_referer( 'asura-connector-admin' );

        $validation = new WP_Error();

        if ( empty( $_REQUEST[ 'provider_id' ] ) ) {
            $validation->add('missing_field', __( 'The provider id field is required', 'asura-connector' ) );
        } else if ( ! is_numeric( $_REQUEST[ 'provider_id' ] ) ) {
            $validation->add('missing_field', __( 'The provider id should numeric', 'asura-connector' ) );
        }
        if ( empty( $_REQUEST[ 'provider' ] ) ) {
            $validation->add('missing_field', __( 'The provider field is required', 'asura-connector' ) );
        }
        if ( empty( $_REQUEST[ 'site_title' ] ) ) {
            $validation->add('missing_field', __( 'The site title field is required', 'asura-connector' ) );
        }
        if ( empty( $_REQUEST[ 'api_key' ] ) ) {
            $validation->add('missing_field', __( 'The api key field is required', 'asura-connector' ) );
        }
        if ( empty( $_REQUEST[ 'api_secret' ] ) ) {
            $validation->add('missing_field', __( 'The api secret field is required', 'asura-connector' ) );
        }
        if ( empty( $_REQUEST[ 'endpoint' ] ) ) {
            $validation->add('missing_field', __( 'The endpoint field is required', 'asura-connector' ) );
        }
        if ( empty( $_REQUEST[ 'version' ] ) ) {
            $validation->add('missing_field', __( 'The version field is required', 'asura-connector' ) );
        }

        if ($validation->has_errors()) {
            wp_send_json_error( 
                $validation,
                400
            );
        }
        
        $id = $_REQUEST[ 'provider_id' ];

        $exist = DB::get(Provider::TABLE_NAME, [
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

        if ( $exist->provider !== $_REQUEST[ 'provider' ] ) {
            $updatedData['provider'] = $_REQUEST['provider'];
        }
        if ( $exist->site_title !== $_REQUEST[ 'site_title' ] ) {
            $updatedData['site_title'] = $_REQUEST['site_title'];
        }
        if ( $exist->api_key !== $_REQUEST[ 'api_key' ] ) {
            $updatedData['api_key'] = $_REQUEST['api_key'];
        }
        if ( $exist->api_secret !== $_REQUEST[ 'api_secret' ] ) {
            $updatedData['api_secret'] = $_REQUEST['api_secret'];
        }
        if ( $exist->endpoint !== $_REQUEST[ 'endpoint' ] ) {
            $updatedData['endpoint'] = $_REQUEST['endpoint'];
        }
        if ( $exist->version !== $_REQUEST[ 'version' ] ) {
            $updatedData['version'] = $_REQUEST['version'];
        }

        if ( ! empty ($updatedData) ) {
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
    
            wp_send_json_success($update);
        }

        wp_die();
    }

    public function list_licenses() {
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

        $exist = DB::get(Provider::TABLE_NAME, [
            'id [Int]',
        ], [
            'id' => $id,
        ]);

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
                'ORDER' => [ 'id' => 'DESC' ],
            ]
        );

        wp_send_json_success($licenses);

        wp_die();
    }

    public function add_license() {
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
            'created_at',
            'updated_at'
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

        
        if ( empty( $_REQUEST[ 'license_key' ] ) ) {
            wp_send_json_error( 
                new WP_Error( 
                    'missing_field', 
                    __( 'The license key field is required', 'asura-connector' ) 
                ),
                400
            );
        }

        $license_key = $_REQUEST[ 'license_key' ];

        $response = Asura::license_domains_register((object) $provider, $license_key);

        $body = json_decode($response->body(), true);

        if( $response->status() !== 200 ) {
            wp_send_json_error( 
                new WP_Error( 
                    key($body), 
                    array_values($body)[0] 
                ),
                $response->status()
            );
        }

        $insert = DB::insert(License::TABLE_NAME, [
            'provider_id' => $id,
            'license' => $license_key,
            'hash' => $body['data'][0]['hash'],
            'status' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        if ( ! $insert ) {
            wp_send_json_error( 
                new WP_Error( 
                    'internal_error', 
                    __( 'Failed to add the provider to database', 'asura-connector' ) 
                ),
                500
            );
        }

        wp_send_json_success($insert);
    
        wp_die();
    }

    public function delete_license() {
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
            'created_at',
            'updated_at'
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
        
        if ( empty( $_REQUEST[ 'license_id' ] ) ) {
            wp_send_json_error( 
                new WP_Error( 
                    'missing_field', 
                    __( 'The license id is required', 'asura-connector' ) 
                ),
                400
            );
        }

        $license_id = $_REQUEST[ 'license_id' ];

        $license = DB::get(License::TABLE_NAME, [
            'id [Int]',
            'provider_id [Int]',
            'license'
        ], [
            'id' => $license_id,
            'provider_id' => $id,
        ]);

        if ( ! $license ) {
            wp_send_json_error( 
                new WP_Error( 
                    'record_not_exist', 
                    __( 'The license id is not exist', 'asura-connector' ) 
                ),
                404
            );
        }

        $response = Asura::license_domains_deregister((object) $provider, $license['license']);

        $body = json_decode($response->body(), true);

        if( $response->status() !== 200 ) {
            wp_send_json_error( 
                new WP_Error( 
                    key($body), 
                    array_values($body)[0] 
                ),
                $response->status()
            );
        }

        $delete = DB::delete(License::TABLE_NAME, [
            'id' => $license_id,
            'provider_id' => $id
        ]);

        wp_send_json_success($delete);
    
        wp_die();
    }

}