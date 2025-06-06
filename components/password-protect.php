<?php
/*

Password Protected

The idea is to password protect the staging site.

Based on https://github.com/benhuson/password-protected

*/


class FC_Password_Protected
{

	var $password;

	var $version = '2.0.1';
	var $admin   = null;
	var $errors  = null;


	/**
	 * Constructor
	 */
	function __construct()
	{

        if ( is_multisite() ) {
			$site = get_site();
			$password = FC()->config( 'FC_PASSWORD_PROTECT_PASSWORD_' . $site->id);

			if (!$password) {
				$site_path = strtoupper( str_replace('/', '_', trim( $site->path, '/' ) ) );
				$password = FC()->config( 'FC_PASSWORD_PROTECT_PASSWORD_' . $site_path);
			}
			
			$this->password = $password;
		}

        if ( !$this->password )
            $this->password = FC()->get_configuration_value( 'FC_PASSWORD_PROTECT_PASSWORD' );

        if ( !$this->password ) {
            return;
        }

		$this->errors = new WP_Error();

		add_action( 'init', array( $this, 'disable_caching' ), 1 );
		add_action( 'init', array( $this, 'maybe_process_login' ), 1 );
		add_action( 'template_redirect', array( $this, 'maybe_show_login' ), -1 );
		add_action( 'password_protected_login_messages', array( $this, 'login_messages' ) );


	}


	/**
	 * Disable Page Caching
	 */
	function disable_caching()
	{
		if ( ! defined( 'DONOTCACHEPAGE' ) )
		{
			define( 'DONOTCACHEPAGE', true );
		}
	}

	/**
	 * Maybe Process Login
	 */
	function maybe_process_login() {

		if ( isset( $_REQUEST['password_protected_pwd'] ) )
		{
			$password_input = $_REQUEST['password_protected_pwd'];

			// If correct password...
			if ( $password_input == $this->password )
			{
				$this->set_auth_cookie();
				$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

				if ( ! empty( $redirect_to ) )
				{
					$this->safe_redirect( $redirect_to );
					exit;
				}

			}
			else
			{
				// ... otherwise incorrect password
				$this->clear_auth_cookie();
				$this->errors->add( 'incorrect_password', __( 'Incorrect Password', 'password-protected' ) );
			}
		}

		// Log out
		if ( isset( $_REQUEST['password-protected'] ) && $_REQUEST['password-protected'] == 'logout' )
		{
			$this->logout();

			if ( isset( $_REQUEST['redirect_to'] ) )
			{
				$redirect_to = esc_url_raw( $_REQUEST['redirect_to'], array( 'http', 'https' ) );
				wp_redirect( $redirect_to );
				exit();
			}

			$redirect_to = remove_query_arg( array( 'password-protected', 'redirect_to' ), ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			$query = array(
				'password-protected' => 'login',
				'redirect_to' => urlencode( $redirect_to )
			);

			wp_redirect( add_query_arg( $query, home_url() ) );
			exit();

		}

	}


	/**
	 * Maybe Show Login
	 */
	function maybe_show_login() {

		// Logged in
		if ( $this->validate_auth_cookie() )
		{
			return;
		}

		if ( is_robots() ) {
		    return;
        }

		// Show login form
		if ( isset( $_REQUEST['password-protected'] ) && 'login' == $_REQUEST['password-protected'] )
		{
			if ( empty( $default_theme_file ) )
			{
				$theme_file = dirname( __FILE__ ) . '/password-protect-login.php';
			}

			load_template( $theme_file );
			exit();
		}
		else
		{

			$redirect_to = add_query_arg( 'password-protected', 'login', home_url() );

			// URL to redirect back to after login
			$redirect_to_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			if ( ! empty( $redirect_to_url ) ) {
				$url = parse_url($redirect_to_url);
				$query_parts = explode('&', $url['query']);

				foreach($query_parts as $part) {
					[$key, $value] = str_contains($part, '=') ? explode('=', $part) : [$part, ''];

					$redirect_to = add_query_arg( $key, $value, $redirect_to );
				}
				
				$redirect_to = add_query_arg( 'redirect_to', urlencode( $redirect_to_url ), $redirect_to );
			}

			wp_redirect( $redirect_to );
			exit();

		}
	}


	/**
	 * Get Site ID
	 *
	 * @return  string  Site ID.
	 */
	function get_site_id()
	{

		global $blog_id;
		return 'bid_' . $blog_id;

	}


	/**
	 * Logout
	 */
	function logout()
	{
		$this->clear_auth_cookie();
		do_action( 'password_protected_logout' );
	}


	/**
	 * Validate Auth Cookie
	 *
	 * @param   string   $cookie  Cookie string.
	 * @param   string   $scheme  Cookie scheme.
	 * @return  boolean           Validation successful?
	 */
	function validate_auth_cookie( $cookie = '', $scheme = '' ) {

		if ( ! $cookie_elements = $this->parse_auth_cookie( $cookie, $scheme ) )
		{
			do_action( 'password_protected_auth_cookie_malformed', $cookie, $scheme );
			return false;
		}

		extract( $cookie_elements, EXTR_OVERWRITE );

		$expired = $expiration;

		// Allow a grace period for POST and AJAX requests
		if ( defined( 'DOING_AJAX' ) || 'POST' == $_SERVER['REQUEST_METHOD'] )
		{
			$expired += 3600;
		}

		// Quick check to see if an honest cookie has expired
		if ( $expired < current_time( 'timestamp' ) )
		{
			do_action('password_protected_auth_cookie_expired', $cookie_elements);
			return false;
		}

		$pass = md5( $this->password );
		$pass_frag = substr( $pass, 8, 4 );

		$key = md5( $this->get_site_id() . $pass_frag . '|' . $expiration );
		$hash = hash_hmac( 'md5', $this->get_site_id() . '|' . $expiration, $key);

		if ( $hmac != $hash )
		{
			do_action( 'password_protected_auth_cookie_bad_hash', $cookie_elements );
			return false;
		}

		if ( $expiration < current_time( 'timestamp' ) )
		{ // AJAX/POST grace period set above
			$GLOBALS['login_grace_period'] = 1;
		}

		return true;

	}


	/**
	 * Generate Auth Cookie
	 *
	 * @param   int     $expiration  Expiration time in seconds.
	 * @param   string  $scheme      Cookie scheme.
	 * @return  string               Cookie.
	 */
	function generate_auth_cookie( $expiration, $scheme = 'auth' )
	{
		$pass = md5( $this->password );
		$pass_frag = substr( $pass, 8, 4 );

		$key = md5( $this->get_site_id() . $pass_frag . '|' . $expiration );
		$hash = hash_hmac( 'md5', $this->get_site_id() . '|' . $expiration, $key );
		$cookie = $this->get_site_id() . '|' . $expiration . '|' . $hash;

		return $cookie;

	}


	/**
	 * Parse Auth Cookie
	 *
	 * @param   string  $cookie  Cookie string.
	 * @param   string  $scheme  Cookie scheme.
	 * @return  string           Cookie string.
	 */
	function parse_auth_cookie( $cookie = '', $scheme = '' )
	{

		if ( empty( $cookie ) ) {
			$cookie_name = $this->cookie_name();
	
			if ( empty( $_COOKIE[$cookie_name] ) ) {
				return false;
			}
			$cookie = $_COOKIE[$cookie_name];
		}

		$cookie_elements = explode( '|', $cookie );
		if ( count( $cookie_elements ) != 3 ) {
			return false;
		}

		list( $site_id, $expiration, $hmac ) = $cookie_elements;

		return compact( 'site_id', 'expiration', 'hmac', 'scheme' );

	}


	/**
	 * Set Auth Cookie
	 */
	function set_auth_cookie()
	{
		$expire = current_time( 'timestamp' ) + ( 365 * DAY_IN_SECONDS );

		$secure_password_protected_cookie = is_ssl();
		$password_protected_cookie = $this->generate_auth_cookie( $expire, 'password_protected' );

		setcookie( $this->cookie_name(), $password_protected_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_password_protected_cookie, true );

		if ( COOKIEPATH != SITECOOKIEPATH )
		{
			setcookie( $this->cookie_name(), $password_protected_cookie, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_password_protected_cookie, true );
		}

	}


	/**
	 * Clear Auth Cookie
	 */
	function clear_auth_cookie()
	{
		setcookie( $this->cookie_name(), ' ', current_time( 'timestamp' ) - 31536000, COOKIEPATH, COOKIE_DOMAIN );
		setcookie( $this->cookie_name(), ' ', current_time( 'timestamp' ) - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN );
	}


	/**
	 * Cookie Name
	 *
	 * @return  string  Cookie name.
	 */
	function cookie_name()
	{

		return $this->get_site_id() . '_password_protected_auth';

	}



	/**
	 * Login Messages
	 * Outputs messages and errors in the login template.
	 */
	public function login_messages()
	{

		// Add message
		$message = apply_filters( 'password_protected_login_message', '' );
		if ( ! empty( $message ) ) {
			echo $message . "\n";
		}

		if ( $this->errors->get_error_code() )
		{

			$errors = '';
			$messages = '';

			foreach ( $this->errors->get_error_codes() as $code )
			{
				$severity = $this->errors->get_error_data( $code );
				foreach ( $this->errors->get_error_messages( $code ) as $error )
				{
					if ( 'message' == $severity ) {
						$messages .= '	' . $error . "<br />\n";
					} else {
						$errors .= '	' . $error . "<br />\n";
					}
				}
			}

			if ( ! empty( $errors ) )
			{
				echo '<div id="login_error">' . apply_filters( 'password_protected_login_errors', $errors ) . "</div>\n";
			}
			if ( ! empty( $messages ) )
			{
				echo '<p class="message">' . apply_filters( 'password_protected_login_messages', $messages ) . "</p>\n";
			}

		}

	}


	/**
	 * Safe Redirect
	 *
	 * Ensure the redirect is to the same site or pluggable list of allowed domains.
	 * If invalid will redirect to ...
	 * Based on the WordPress wp_safe_redirect() function.
	 */
	function safe_redirect( $location, $status = 302 )
	{

		$location = wp_sanitize_redirect( $location );
		$location = wp_validate_redirect( $location, home_url() );

		wp_redirect( $location, $status );
	}

}
