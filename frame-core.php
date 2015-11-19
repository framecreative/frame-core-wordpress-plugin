<?php
/*

Plugin Name: F / R / A / M / E / Core
Plugin URI: http://framecreative.com.au
Version: 1.0.0
Author: Frame
Author URI: http://framecreative.com.au
Description: Designed to run with a fairly specific git workflow and wp-config.php

Enable password protection using constants
define('FC_PASSWORD_PROTECT_ENABLE', true);
define('FC_PASSWORD_PROTECT_PASSWORD', 'frame123');

// Force URL options
define('FC_FORCE_URL', 'lexstobie.com');
define('FC_FORCE_SSL', true);

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
	 * @var instance of Password Protect component
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


		add_action( 'admin_menu', array( &$this,'admin_remove_menu_pages'), 999 );
		add_action( 'wp_before_admin_bar_render', array( $this, 'before_admin_bar_render') );


		if ( WP_ENV !== 'dev' )
		{
			// Hide update messages
			add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );
		}


		$this->force_url();

		$this->load_components();
	}



	/**
	 * Include components
	 */
	function load_components()
	{
		require_once $this->dir . 'components/password-protect.php';
		require_once $this->dir . 'components/env-tag.php';

		$this->password_protect = new FC_Password_Protected();
		new FC_Env_Tag();
	}



	/**
	 * Clean up admin menu
	 */
	function admin_remove_menu_pages()
	{
		remove_submenu_page( 'themes.php', 'theme-editor.php' );
		remove_submenu_page( 'plugins.php', 'plugin-editor.php' );

		// Remove customize.php
		global $submenu;
		unset($submenu['themes.php'][6]);

		if ( WP_ENV !== 'dev' )
		{
			// Hide ACF on live and staging
			remove_menu_page( 'edit.php?post_type=acf-field-group' );

			// Hide updates menu item
			remove_submenu_page( 'index.php', 'update-core.php' );
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
		if ( ! defined( 'FC_FORCE_URL' ) )
			return;

		if ( ! defined( 'FC_FORCE_SSL' ) )
			define( 'FC_FORCE_SSL', false );


		if ( $_SERVER['HTTP_HOST'] != FC_FORCE_URL )
		{
			header( 'Location: http' . ( FC_FORCE_SSL ? 's' : '' ) . '://' . FC_FORCE_URL . $_SERVER['REQUEST_URI'] );
			exit();
		}

		if( FC_FORCE_SSL && $_SERVER['HTTPS'] != 'on' )
		{
			header( "Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
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
