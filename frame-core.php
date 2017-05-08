<?php
/*

Plugin Name: F / R / A / M / E / Core
Plugin URI: http://framecreative.com.au
Version: 1.0.7
Author: Frame
Author URI: http://framecreative.com.au
Description: Designed to run with a fairly specific git workflow and wp-config.php

Bitbucket Plugin URI: https://bitbucket.org/framecreative/frame-core
Bitbucket Branch: master

Enable password protection using constants
define('FC_PASSWORD_PROTECT_ENABLE', true);
define('FC_PASSWORD_PROTECT_PASSWORD', 'frame123');

// Force URL options
define('FC_FORCE_DOMAIN', 'lexstobie.com');
define('FC_FORCE_SSL', true);
define('FC_PREFER_SSL', true);

 */


class Frame_Core
{

	/**
	 * @var string
	 */
	public $dir;


	/**
	 * @var string
	 */
	public $uri;


	/**
	 * @var FC_Password_Protected
	 */
	public $password_protect;



	/**
	 * The single instance of the class
	 */
	protected static $_instance = null;



	/**
	 * Init
	 */
	function __construct()
	{
		/**
		 * Useful...
		 */
		$this->dir = plugin_dir_path( __FILE__ );
		$this->uri = plugins_url( '', __FILE__ );


		/*
		 * Disable automatic updates these should be managed through git
		 */
		if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) )
			define( 'AUTOMATIC_UPDATER_DISABLED', true );

		if ( ! defined( 'WP_AUTO_UPDATE_CORE' ) )
			define( 'WP_AUTO_UPDATE_CORE', false );


		if ( ! defined( 'FC_FORCE_SSL' ) )
			define( 'FC_FORCE_SSL', false );

		if ( ! defined( 'FC_PREFER_SSL' ) )
			define( 'FC_PREFER_SSL', false );


		add_action( 'admin_menu', array( $this,'admin_remove_menu_pages'), 999 );
		add_action( 'admin_menu', array( $this,'remove_update_nag'), 999 );
		add_action( 'wp_before_admin_bar_render', array( $this, 'before_admin_bar_render') );

		add_action( 'template_redirect', array( $this, 'force_url') );

		$this->load_components();
	}



	/**
	 * Include components
	 */
	function load_components()
	{
		require_once $this->dir . 'components/password-protect.php';
		require_once $this->dir . 'components/env-tag.php';
		require_once $this->dir . 'components/disable-emojis.php';
		require_once $this->dir . 'components/proxy-uploads.php';
		require_once $this->dir . 'components/smtp.php';
		require_once $this->dir . 'components/helpers.php';

		if ( is_admin() ) {
			require_once $this->dir . 'components/disable-admin-nags.php';
		}

		$this->password_protect = new FC_Password_Protected();
		new FC_Env_Tag();
		new FC_Proxy_Uploads();
		new FC_SMTP();

	}



	/**
	 * Clean up admin menu
	 */
	function admin_remove_menu_pages()
	{
		remove_submenu_page( 'themes.php', 'theme-editor.php' );
		remove_submenu_page( 'plugins.php', 'plugin-editor.php' );

		if ( WP_ENV !== 'dev' )
		{
			// Hide ACF on live and staging
			remove_menu_page( 'edit.php?post_type=acf-field-group' );

			// Hide updates menu item
			remove_submenu_page( 'index.php', 'update-core.php' );
		}
	}


	/**
	 * Remove core update message
	 */
	function remove_update_nag()
	{
		if ( WP_ENV !== 'dev' )
		{
			remove_action('admin_notices', 'update_nag', 3);
		}
	}


	/**
	 * Clean up
	 */
	function before_admin_bar_render()
	{
		global $wp_admin_bar;

		if ( WP_ENV !== 'dev' )
		{
			$wp_admin_bar->remove_node('updates');
		}
	}




	/**
	 * Force site URL based on constants
	 */
	function force_url()
	{
		if ( ! defined( 'FC_FORCE_DOMAIN' ) )
			return;

		if ( $_SERVER['HTTP_HOST'] != FC_FORCE_DOMAIN )
		{
			$ssl = FC_FORCE_SSL || FC_PREFER_SSL || is_ssl();

			$url = 'http' . ( $ssl ? 's' : '' ) . '://' . FC_FORCE_DOMAIN . $_SERVER['REQUEST_URI'];

			wp_redirect( esc_url( $url ), 301 );
			exit();
		}

		if ( FC_FORCE_SSL && ! is_ssl() )
		{
			wp_redirect( esc_url( "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ), 301 );
			exit();
		}
	}



	/**
	 * @return Frame_Core
	 */
	public static function instance()
	{
		if ( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}


function FC()
{
	return Frame_Core::instance();
}

FC();
