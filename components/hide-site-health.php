<?php

class FC_Hide_Site_Health {

    function __construct() {

        $hideDashboard =     FC()->get_configuration_value( 'FC_HIDE_SITE_HEALTH_DASHBOARD', true );
        $hidePage =          FC()->get_configuration_value( 'FC_HIDE_SITE_HEALTH_PAGE', false );

        if ( $hideDashboard || $hidePage ) {
            add_action('wp_dashboard_setup', array($this, 'hide_dashboard_widget'));
        }

        if ( $hidePage ) {
            add_action('admin_menu', array($this, 'hide_menu_item'));
            add_action('current_screen', array($this, 'disable_page'));
        }

    }

    function hide_dashboard_widget() {

        global $wp_meta_boxes;

        unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health'] );

    }

    function hide_menu_item() {

        remove_submenu_page( 'tools.php', 'site-health.php' );

    }

    function disable_page() {

        if ( is_admin() ) {

            $screen = get_current_screen();

            // if screen id is site health
            if ( 'site-health' == $screen->id ) {
                wp_redirect( admin_url() );
                exit;
            }

        }

    }

}
