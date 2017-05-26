<?php
/*

Plugin Name: F / R / A / M / E / Core
Plugin URI: http://framecreative.com.au
Version: 1.0.9
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



	public $is_dev_user;



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

		if ( ! defined( 'WP_AUTO_UPDATE_CORE' ) )
			define( 'WP_AUTO_UPDATE_CORE', 'minor' );

		if ( ! defined( 'DISALLOW_FILE_EDIT' ) )
			define( 'DISALLOW_FILE_EDIT', true );

		if ( ! defined( 'FC_FORCE_SSL' ) )
			define( 'FC_FORCE_SSL', false );

		if ( ! defined( 'FC_PREFER_SSL' ) )
			define( 'FC_PREFER_SSL', false );

		if ( !defined( 'FC_CODE_MANAGED' ) )
			define( 'FC_CODE_MANAGED', true );

		if ( !defined( 'FC_SITE_MAINTAINED' ) )
			define( 'FC_SITE_MAINTAINED', false );


		add_action( 'init', array( $this, 'check_for_dev_user' ) );

		add_filter( 'user_has_cap', array( $this, 'modify_user_capabilities' ), 10, 3 );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'admin_menu', array( $this,'admin_remove_menu_pages'), 999 );

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


	function check_for_dev_user() {

		if ( WP_ENV == 'dev' ) {

			// all are devs in the dev environment
			$this->is_dev_user = true;

		} else {

			$current_user = wp_get_current_user();
			$this->is_dev_user = ( defined('FC_DEV_USER') && FC_DEV_USER == $current_user->user_login );

		}


	}

	function modify_user_capabilities( $allcaps ) {

		if ( $this->is_dev_user || !is_admin() ) {
			return $allcaps;
		}

		if ( FC_CODE_MANAGED || FC_SITE_MAINTAINED ) {

			$allcaps['install_themes'] = false;
			$allcaps['switch_themes'] = false;
			$allcaps['install_plugins'] = false;
			$allcaps['delete_plugins'] = false;

		}

		if ( FC_SITE_MAINTAINED ) {

			$allcaps['update_plugins'] = false;
			$allcaps['update_core'] = false;
			$allcaps['update_themes'] = false;

		}

		return $allcaps;

	}


	function admin_notices() {

		if ( $this->is_dev_user || get_current_screen()->id != 'plugins' ) {
			return;
		}

		if ( FC_CODE_MANAGED || FC_SITE_MAINTAINED ) {

			?>
			<div class="notice notice-warning">
				<p>
					<strong>Plugin Installation Disabled</strong> - Dependencies for this site are version controlled.
					Please contact Frame to discuss new functionality so that the correct process can be followed.
				</p>
			</div>
			<?php

		}

	}



	/**
	 * Clean up admin menu
	 */
	function admin_remove_menu_pages()
	{

		if ( !$this->is_dev_user )
		{
			// Hide ACF
			remove_menu_page( 'edit.php?post_type=acf-field-group' );

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
