<?php

namespace TheLostAsura\Connector;

use TheLostAsura\Connector\Utils\Access;

/**
 * Admin Pages Handler
 */
class Admin
{
    public function __construct()
    {
        if ( Access::can() ) {
            add_action('admin_menu', [$this, 'admin_menu']);
        }
    }

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu()
    {
        global $submenu;

        $capability = 'manage_options';
        $slug       = 'asura-connector';

        $hook = add_menu_page(
            __('Asura Connector', 'asura-connector'),
            __('Asura Connector', 'asura-connector'),
            $capability,
            $slug,
            [
                $this,
                'plugin_page'
            ],
            'data:image/svg+xml;base64,' . base64_encode(@file_get_contents(ASURA_CONNECTOR_PATH . '/public/img/icon.svg')),
            2
        );

        if (current_user_can($capability)) {
            $submenu[$slug][] = [ __('Dashboard', 'asura-connector'), $capability, "admin.php?page={$slug}#/" ];
        }

        add_action('load-' . $hook, [$this, 'init_hooks']);
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('connector-style');
        wp_enqueue_script('asura-connector-admin');
        wp_set_script_translations('asura-connector-admin', 'asura-connector', ASURA_CONNECTOR_PATH . '/languages/');
        wp_localize_script(
            'asura-connector-admin',
            'thelostasura',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'asura-connector-admin' ),
            ]
        );
    }

    /**
     * Render our admin page
     *
     * @return void
     */
    public function plugin_page()
    {
        echo '<div id="thelostasura-app"></div>';
    }
}
