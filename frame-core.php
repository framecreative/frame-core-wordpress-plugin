<?php
/*
  Plugin Name: F / R / A / M / E / Core
  Plugin URI: http://framecreative.com
  Version: 1.0.0
  Author: Daniel Bitzer
  Author URI: http://framecreative.com
  Description: Designed to run with a fairly specific git workflow and wp-config.php
 */


class FrameCore
{

	/**
	 * Hides ACF from the admin menu of the live site as fields should only be added to the development install.
	 *
	 * @var array
	 */
	public $admin_menu_hidden_items = array( 'edit.php?post_type=acf-field-group' );



	/**
	 * Init
	 */
	function __construct()
	{
		/*
		 * Disable automatic updates these should be managed through git
		 */
		if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) )
			define( 'AUTOMATIC_UPDATER_DISABLED', true );

		if ( ! defined( 'WP_AUTO_UPDATE_CORE' ) )
			define( 'WP_AUTO_UPDATE_CORE', false );


		if ( WP_ENV == 'live' )
		{
			$this->live();
		}
	}



	/**
	 * Live site only
	 */
	function live()
	{
		add_action( 'admin_menu', array( $this, 'filter_admin_menu' ), 20 );

		// Hide update messages
		add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );
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


}


new FrameCore();