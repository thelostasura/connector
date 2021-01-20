<?php

namespace TheLostAsura\Connector;

/**
 * Frontend Pages Handler
 */
class Frontend {

	public function __construct() {
		if( Utils::is_oxygen_editor() )
		{
			add_action( 'wp_footer', [$this, 'render_frontend'] );
			add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		}

	}

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('asura-connector-frontend');
        wp_enqueue_script('asura-connector-frontend');
        wp_set_script_translations('asura-connector-frontend', 'asura-connector', ASURA_CONNECTOR_PATH . '/languages/');
        wp_localize_script(
            'asura-connector-frontend',
            'thelostasura',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'asura-connector-admin' ),
            ]
        );
    }

	/**
	 * Render frontend app
	 *
	 * @return string
	 */
	public function render_frontend( ) 
	{
        echo '<div id="thelostasura-app"></div>';
	}

}
