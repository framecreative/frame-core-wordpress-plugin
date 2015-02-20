<?php
/*

Plugin Name: F / R / A / M / E / Core
Plugin URI: http://framecreative.com
Version: 1.0.0
Author: Daniel Bitzer
Author URI: http://framecreative.com
Description: Designed to run with a fairly specific git workflow and wp-config.php


Enable password protection using constants
define('FC_PASSWORD_PROTECT_ENABLE', true);
define('FC_PASSWORD_PROTECT_PASSWORD', 'frame123');


 */




class Frame_Core
{

	/**
	 * Hides ACF from the admin menu of the live site as fields should only be added to the development install.
	 *
	 * @var array
	 */
	public $admin_menu_hidden_items = array( 'edit.php?post_type=acf-field-group' );


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


		if ( WP_ENV == 'live' or WP_ENV == 'staging' )
		{
			add_action( 'admin_menu', array( $this, 'filter_admin_menu' ), 20 );

			// Hide update messages
			add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );
		}

		$this->load_components();
	}



	/**
	 * Include components
	 */
	function load_components()
	{
		require_once $this->dir . 'components/password-protect.php';


		$this->password_protect = new FC_Password_Protected();
	}



	/**
	 * Function to hide elements from the admin menu on the live site only
	 */
	function filter_admin_menu()
	{
		global $menu;
		foreach ( $menu as $key => $item )
			if ( in_array($item[2], $this->admin_menu_hidden_items ) )
				unset($menu[$key]);
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
