<?php


class FC_Conditional_Plugin_Loading {

	/**
	 * @var array
	 */
	protected $rules = [];

	/**
	 * @var bool
	 */
	protected $init = false;

	/**
	 * @var string|null
	 */
	protected $env;

	/**
	 * @var array
	 */
	protected $deactivated = [];

	/**
	 * FC_Conditional_Plugin_Loading constructor.
	 */
	public function __construct() {

		add_filter( 'option_active_plugins', [ $this, 'load_plugins' ] );

		add_filter('option_active_plugins', [ $this, 'lazy_init_values'], 1 );

		add_filter( 'plugin_row_meta', [ $this, 'notice_for_plugin_table'], 30, 4 );

		$this->env = FC()->get_current_environment();

	}

	/**
	 * Acts as a JIT loader for the conditions
	 *
	 * @param $plugins
	 *
	 * @return mixed
	 */

	public function lazy_init_values( $plugins ){

		if ( $this->init ) return $plugins;

		$this->rules = $this->setInitialRules();

		$this->init = true;

		// Return this array as we're using a filter like an action
		return $plugins;
	}

	protected function setInitialRules(){

		$conditions = [
			'on_live' => [],
			'on_staging' => [],
			'on_dev' => [],
			'not_live' => [],
			'not_staging' => [],
			'not_dev' => [],
		];

		$conditions['not_dev'] = [
			'simple-login-log/simple-login-log.php',
			'wordfence/wordfence.php',
			'wordpress-seo/wp-seo.php',
			'mailgun/mailgun.php',
			'wp-security-audit-log/wp-security-adit-log.php',
			'sparkpost/wordpress-spark-post.php',
			'worker/init.php',
			'mailchimp-for-woocommerce/mailchimp-woocommerce.php',
			'efinterface/efinterface.php'
		];

		return apply_filters( 'frame/core/conditional_plugin_loading_rules', $conditions, $this );

	}

	/**
	 * @param string $plugin_string
	 *
	 * @return string|null
	 */
	private function get_folder_name( $plugin_string = '' ){
		$bits = explode('/', $plugin_string );

		return isset( $bits[0] ) ? (string)$bits[0] : null;
	}



	public function load_plugins( $plugins ){

		$rules = $this->rules;

		$activate = ( ! empty( $rules[ 'on_' . $this->env ] ) ) ? $rules[ 'on_' . $this->env ] : [];

		$deactivate = ( ! empty( $rules[ 'not_' . $this->env ] ) ) ? $rules[ 'not_' . $this->env ] : [];

		$plugins = array_merge( $plugins, $activate );

		foreach( $deactivate as $plugin_to_deactivate ){

			$folder_name = $this->get_folder_name( $plugin_to_deactivate );

			/*
			 * This allows us to bail on a per plugin basis using the env file, good for quick testing
			 *
			 * EG: FC_ACTIVATE_MAILGUN="true"
			 * Only works to STOP deactivating plugins
			 */
			if ( FC()->get_configuration_value( 'FC_ACTIVATE_' . strtoupper( $folder_name), false ) ) continue;

			$key = array_search( $plugin_to_deactivate, $plugins );

			if ( ! $key ) continue;

			unset( $plugins[ $key ] );

			$this->deactivated[] = $plugin_to_deactivate;

		}

		// In case we've added plugins that were already active
		$plugins = array_unique( $plugins );

		return $plugins;

	}

	function notice_for_plugin_table( $meta, $file, $data, $status ){

		if ( false === array_search( $file, $this->deactivated ) ) return $meta;

		$meta[] = '<span style="font-family: monospace; padding: 4px 2px; background-color: #f14242; color: #ffffff;">Plugin deactivated in "' . WP_ENV . '" by Frame Core</span>';

		return $meta;
	}



}
