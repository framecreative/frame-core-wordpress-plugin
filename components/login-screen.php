<?php

class FC_Login_Screen {

	var $_options = null;

	public function __construct()
	{

		/* Always run these hooks */

		add_action( 'login_head', [ $this, 'inline_styles' ] );

		add_filter( 'login_headerurl', [ $this, 'login_header_url' ], 1, 10 );
		add_filter( 'login_headertext', [ $this, 'login_header_text' ], 1 , 10 );

		add_filter( 'login_body_classes', [ $this, 'login_body_classes' ], 2 , 10 );

		add_filter( 'login_message', [ $this, 'env_warning' ] );

		add_filter( 'init', [ $this, 'register_brand_login' ] );

	}

	protected function option( $key )
	{
		if ( ! isset( $this->_options[ $key ] ) ) return false;

		if ( ! $this->_options[ $key ] || empty( $this->_options[ $key ] ) ) return false;

		return $this->_options[ $key ];
	}

	public function inline_styles()
	{
		$logo = false;

		if ( file_exists( get_stylesheet_directory() . '/built/images/logo.svg'  ) ){
			$logo = get_stylesheet_directory_uri() . '/built/images/logo.svg';
		}

		if ( file_exists( get_stylesheet_directory() . '/built/images/logo.png'  ) ){
			$logo = get_stylesheet_directory_uri() . '/built/images/logo.png';
		}

		$logo = apply_filters( 'frame/core/login_logo', $logo );
		?>
	<style id="fc-login-inline-styles">
		.fc__login-env-warning {
			padding: 12px 24px;
			background-color: #ffffff;
			margin-bottom: 20px;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13)
		}

		.fc__login-env-warning span {
			font-family: monospace;
			display: inline-block;
			padding: .162em .33em;
			background-color: #444;
			color: #f1f1f1;
		}
		/* Makes the core login elements easier to style / replace */
		.wp-core-ui #login .button-primary {
			border: none;
			border-radius: 0;
			box-shadow: none;
			text-shadow: none;
		}

		#login h1 a {
			width: 100%;
			background-size: contain;
			background-position: center;
			<?php if ( $logo ): ?>
			background-image: url("<?php echo $logo; ?>");
			<?php endif; ?>
		}
	</style>
	<?php
	}

	public function login_header_url( $url )
	{
		return WP_HOME;
	}

	public function login_header_text( $text )
	{
		return get_bloginfo('name');
	}

	public function env_warning( $message )
	{
		return "<p class='fc__login-env-warning'>Your are logging into the <span>" . WP_ENV . "</span> environment</p>" . $message;
	}

	public function register_brand_login(){
		/* Conditional Hooks - based on theme support */
		if (  ! $options = get_theme_support('custom-login')[0] ?? false ) return;

		$this->_options = $options;
		add_action( 'login_head', [ $this, 'brand_inline_styles' ] );
	}

	public function brand_inline_styles()
	{
		$logo_url = $this->option( 'logo' );
		$background = $this->option( 'background' );
		$text_color = $this->option( 'text_color' );
		$button_color = $this->option( 'button_text_color' ) ?: '#ffffff';
		$accent_color = $this->option( 'accent_color' );
		$footer_text_color = $this->option( 'footer_text_color' ) ?: $text_color;
		$footer_text_color_hover = $this->option( 'footer_text_color_hover' ) ?: $accent_color;
		?>
		<style id="fc-login-brand-styles">
			<?php if( $logo_url ): ?>
			#login h1 a {
				background-image: url("<?php echo $logo_url; ?>");
			}
			<?php endif; ?>
			<?php if( $background ): ?>
			body.login {
				background: <?php echo $background; ?>;
			}
			<?php endif; ?>
			<?php if( $text_color ): ?>
			body.login {
				color: <?php echo $text_color; ?>;
			}
			<?php endif; ?>
			<?php if( $accent_color ): ?>
			.wp-core-ui #login .button-primary {
				border: solid 1px <?php echo $accent_color; ?>;
				background-color: <?php echo $accent_color; ?>;
				color: <?php echo $button_color; ?>;
			}
			#login h1 a svg { fill: <?php echo $accent_color; ?>; }

			.wp-core-ui #login .button-primary:hover,
			.wp-core-ui #login .button-primary:focus {
				border: solid 1px <?php echo $accent_color; ?>;
				background-color: transparent;
				color: <?php echo $accent_color; ?>;
			}

			#login #nav a:hover,
			#login #nav a:focus,
			#login #backtoblog a:hover,
			#login #backtoblog a:focus {
				color: <?php echo $accent_color; ?>;
			}
			input[type="text"]:focus, input[type="password"]:focus, input[type="color"]:focus, input[type="date"]:focus, input[type="datetime"]:focus, input[type="datetime-local"]:focus, input[type="email"]:focus, input[type="month"]:focus, input[type="number"]:focus, input[type="search"]:focus, input[type="tel"]:focus, input[type="text"]:focus, input[type="time"]:focus, input[type="url"]:focus, input[type="week"]:focus, input[type="checkbox"]:focus, input[type="radio"]:focus, select:focus, textarea:focus {
				border-color: <?php echo $accent_color; ?>;
				box-shadow: 0 0 2px<?php echo $accent_color; ?>;
			}
			<?php endif; ?>
			<?php if( $footer_text_color ): ?>
			#login #nav a,
			#login #backtoblog a {
				color: <?php echo $footer_text_color; ?>;
			}
			<?php endif; ?>

			<?php if( $footer_text_color_hover ): ?>
			#login #nav a:hover,
			#login #nav a:focus,
			#login #backtoblog a:hover,
			#login #backtoblog a:focus {
				color: <?php echo $footer_text_color_hover; ?>;
			}
			<?php endif; ?>
		</style>
		<?php
	}

}

/**
 *
 * Sample config for custom login..
 *
 */
 /*
 	$args = [
		// Path to logo (can use asset_url etc)
		'logo' => '',
		// Full CSS background property accepted, not just color;
		'background' => '#fff',
		// Main text color of the page
		'text_color' => '#444',
		// Used in place of the WP Blue (buttons etc)
		'accent_color' => '#ff0000',
		// Ensure the button text can be seen against the accent color;
		'button_text_color' => '#fff',
		// Defaults to `text_color` if not set
		'footer_text_color' => false,
		// Useful if you use the accent color as background too
		'footer_text_color_hover' => false,
	];

	add_theme_support( 'custom-login', $args );
 
   */
