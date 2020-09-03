<?php
/*

Plugin Name: F / R / A / M / E / Core
Plugin URI: http://framecreative.com.au
Version: 1.11.1
Author: Frame
Author URI: http://framecreative.com.au
Description: Tools & Helpers to take WordPress to the next level. Works best on Frame Servers, and in projects built using Frame's WP-Boilerplate.

Bitbucket Plugin URI: https://bitbucket.org/framecreative/frame-core
Bitbucket Branch: master

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

    public $dev_user;

    public $is_dev_user;

    public $is_code_managed;

    public $is_site_maintained;

    public $is_live_env;



    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Init
     */
    public function __construct()
    {
        self::$_instance = $this;

        /**
         * Useful...
         */

        $this->dir = plugin_dir_path(__FILE__);
        $this->uri = plugins_url('', __FILE__);

        /*
         * Disable automatic updates these should be managed through git
         */

        if (! defined('WP_AUTO_UPDATE_CORE')) {
            define('WP_AUTO_UPDATE_CORE', 'minor');
        }

        if (! defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }


        $this->is_live_env        = ( self::env() === 'live' );
        $this->is_code_managed    = self::config('FC_CODE_MANAGED', true);
        $this->is_site_maintained = self::config('FC_SITE_MAINTAINED', false);
        $this->dev_user           = self::config('FC_DEV_USER', 'frame');

        add_action('init', [ $this, 'check_for_dev_user' ] );

        add_action('init', [ $this, 'prevent_robots' ] );

        add_filter('user_has_cap', [ $this, 'modify_user_capabilities' ], 10, 3);

        add_action('admin_notices', [ $this, 'admin_notices' ]);

        add_action('admin_menu', [ $this,'admin_remove_menu_pages'], 999);

        add_action('template_redirect', [ $this, 'force_url']);

        add_action('customize_register', [$this, 'prefix_remove_css_section'], 15);

        add_filter('redirect_canonical', [$this, 'prevent_author_enum'], 10, 2 );

        $this->remove_headers();
        $this->load_components();
    }

    // Remove the additional CSS section
    public function prefix_remove_css_section($wp_customize) {
        $wp_customize->remove_section('custom_css');
    }

    /**
     * Include components
     */
    public function load_components()
    {
        require_once $this->dir . 'components/password-protect.php';
        require_once $this->dir . 'components/env-tag.php';
        require_once $this->dir . 'components/disable-emojis.php';
        require_once $this->dir . 'components/proxy-uploads.php';
        require_once $this->dir . 'components/smtp.php';
        require_once $this->dir . 'components/helpers.php';
        require_once $this->dir . 'components/google-tag-manager.php';
        require_once $this->dir . 'components/content-freeze.php';
        require_once $this->dir . 'components/conditional-plugin-loading.php';
        require_once $this->dir . 'components/login-screen.php';
        require_once $this->dir . 'components/hide-site-health.php';

        if ( is_admin() ) {
            require_once $this->dir . 'components/disable-admin-nags.php';
            new FC_Disable_Admin_Nags();
        }

        $this->password_protect = new FC_Password_Protected();
        new FC_Env_Tag();
        new FC_Proxy_Uploads();
        new FC_SMTP();
        new FC_Google_Tag_Manager();
        new FC_Content_Freeze();
		new FC_Login_Screen();
        new FC_Conditional_Plugin_Loading();
        new FC_Hide_Site_Health();

    }

    public function check_for_dev_user()
    {
        if (WP_ENV == 'dev') {

            // all are devs in the dev environment
            $this->is_dev_user = true;
        } else {
            $this->is_dev_user = ($this->dev_user == wp_get_current_user()->user_login);
        }
    }

    public function prevent_robots()
    {
		if ( $this->is_live_env && ! self::is_staging_domain() ) return;

		add_action( 'send_headers', function(){
			header("X-Robots-Tag: noindex", true);
		} );

		add_action( 'wp_head', function(){
			echo '<!-- NoIndex Added by Frame Core MU Plugin -->';
			echo '<meta name="robots" content="noindex">';
		}, 99 );

        add_filter( 'robots_txt', function(){

            $output = "User-agent: *\n";
            $output  .= "Disallow: /\n";

            return $output;

        } );

    }

    public function modify_user_capabilities($allcaps)
    {
        if ($this->is_dev_user || !is_admin()) {
            return $allcaps;
        }

        if ($this->is_code_managed || $this->is_site_maintained) {
            $allcaps['install_themes'] = false;
            $allcaps['switch_themes'] = false;
            $allcaps['install_plugins'] = false;
            $allcaps['delete_plugins'] = false;
        }

        if ($this->is_site_maintained) {
            $allcaps['update_plugins'] = false;
            $allcaps['update_core'] = false;
            $allcaps['update_themes'] = false;
        }

        return $allcaps;
    }

    public function admin_notices()
    {
        if ($this->is_dev_user || get_current_screen()->id != 'plugins') {
            return;
        }

        if ($this->is_code_managed || $this->is_site_maintained) {
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
    public function admin_remove_menu_pages()
    {
        if (!$this->is_dev_user) {
            // Hide ACF
            remove_menu_page('edit.php?post_type=acf-field-group');
        }
    }

    /**
     * Force site URL based on constants
     */
    public function force_url()
    {
        $forceDomain = 	$this->get_configuration_value('FC_FORCE_DOMAIN', false);
        $forceSSL = 	$this->get_configuration_value('FC_FORCE_SSL', false);
        $preferSSL = 	$this->get_configuration_value('FC_PREFER_SSL', false);

        if (! $forceDomain) {
            return;
        }

        if ($_SERVER['HTTP_HOST'] != $forceDomain) {
            $ssl = $forceSSL || $preferSSL || is_ssl();

            $url = 'http' . ($ssl ? 's' : '') . '://' . $forceDomain . $_SERVER['REQUEST_URI'];

            wp_redirect(esc_url($url), 301);
            exit();
        }

        if ($forceSSL && ! is_ssl()) {
            wp_redirect(esc_url("https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]), 301);
            exit();
        }
    }

    /**
     * Left for backwards compatibility, use FrameCore::config()
	 * @deprecated 1.10.0
	 *
     * */
    public function get_configuration_value($name, $default = null)
	{
		return self::config( $name, $default );
	}

	/**
	 * Get a configuration value from a constant or .env file
	 *
	 * New static method for better flexibility
	 *
	 * @since 1.10.0
	 * @param string $name
	 * @param string|int|bool|null $default
	 * @return string|int|bool|null
	 */
	public static function config( $name, $default = null )
	{

        if (defined($name)) {
            return constant($name);
		}

		if ($envValue = getenv($name)) {
            switch ($envValue) {
                case 'true':
                    return true;

                case 'false':
                    return false;

                default:
                    return $envValue;
            }
		}

        return $default;

	}

    public function remove_headers()
    {
        remove_action('wp_head', 'wp_generator');
        // Hides version of Yoast if premium
        add_filter('wpseo_hide_version', '__return_true');
    }

	/**
	 * Get's the env and allows for some flexibility
	 * @deprecated 1.10.0
	 * @return string|null
	 */
	public function get_current_environment(){
		return self::env();
	}

	/**
	 * Get's the env and allows for some flexibility
	 *
	 *  New Static Method, allows for mre use in other places.
	 *
	 * @since 1.10.0
	 * @return string|null
	 */
	public static function env(){

		if ( ! defined( 'WP_ENV') ){
			$environment = self::config( 'WP_ENV', null );
			define( 'WP_ENV', $environment );
		}

		/* Synonyms for the 3 ENV we use */
		$env_names = [
			'dev'        => 'dev',
			'local'        => 'dev',
			'staging'    => 'staging',
			'uat'        => 'staging',
			'feature'    => 'staging',
			'live'       => 'live',
			'production' => 'live',
        ];

		$env_names = apply_filters( 'frame/core/env_names', $env_names );

		if ( array_key_exists( WP_ENV, $env_names) ) return $env_names[ WP_ENV ];

		return WP_ENV;
	}

	static function is_staging_domain(){

		$staging_domains = apply_filters( 'frame/core/staging_domains', [ 'frmdv.com, frame.hosting' ] );
		$staging_prefixes = apply_filters( 'frame/core/staging_prefixes', [ 'dev', 'staging', 'uat', ] );

		$httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

		if ( ! $httpHost ) return false;

		if ( stristr( join( ' ', $staging_domains), $httpHost ) ) return true;

		$domain_pieces = (array) explode( '.', $httpHost );

		if ( empty( $domain_pieces ) ) return false;

		return !! array_search( $domain_pieces[0], $staging_prefixes );
	}

    public function prevent_author_enum( $redirect, $request ) {

        if ( is_admin() )
            return $redirect;

        if ( preg_match('/\?author=([0-9]*)(\/*)/i', $request) )
            return false;

        return $redirect;

    }

    /**
     * @return Frame_Core
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
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
