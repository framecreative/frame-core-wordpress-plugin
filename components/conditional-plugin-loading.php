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

        add_filter( 'option_active_plugins', [ $this, 'lazy_init_values'], 1 );
        add_filter( 'site_option_active_sitewide_plugins', [ $this, 'lazy_init_values' ], 1 );

		add_filter( 'option_active_plugins', [ $this, 'load_plugins' ] );
        add_filter( 'site_option_active_sitewide_plugins', [ $this, 'network_load_plugins' ] );

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
			'not_live' => [],
			'not_staging' => [],
			'not_dev' => [],
		];

		$conditions['not_dev'] = [
			'simple-login-log',
			'wordfence',
			'mailgun',
			'wp-security-audit-log',
			'sparkpost',
			'worker',
			'sendgrid-email-delivery-simplified',
			'mailchimp-for-woocommerce',
			'efinterface',
            'wp-mail-smtp',
            'woocommerce-product-feeds',
            'facebook-for-woocommerce'
		];

		$conditions['not_staging'] = [
			'wordfence',
			'mailgun',
			'wp-security-audit-log',
			'sparkpost',
			'worker',
			'sendgrid-email-delivery-simplified',
			'mailchimp-for-woocommerce',
			'efinterface',
            'wp-mail-smtp',
            'woocommerce-product-feeds',
            'facebook-for-woocommerce'
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

    public function network_load_plugins( $plugins ){

		if ( ! $plugins || empty( $plugins ) ) return $plugins;

        $deactivate = $this->get_deactivations();

        foreach ( array_keys($plugins) as $plugin_path ) {

            if ( !in_array( $this->get_folder_name( $plugin_path ), $deactivate ) )
                continue;

            unset( $plugins[$plugin_path] );

            $this->deactivated[] = $plugin_path;

        }

        return $plugins;

    }

	public function load_plugins( $plugins ){

		if ( ! $plugins || empty( $plugins ) ) return $plugins;

		$deactivate = $this->get_deactivations();

		foreach ( $plugins as $index => $plugin_path ) {

            if ( !in_array( $this->get_folder_name( $plugin_path ), $deactivate ) )
                continue;

            unset( $plugins[$index] );

            $this->deactivated[] = $plugin_path;

        }

		return $plugins;

	}

	function notice_for_plugin_table( $meta, $file, $data, $status ){

		if ( false === array_search( $file, $this->deactivated ) ) return $meta;

		$meta[] = '<span style="font-family: monospace; padding: 4px 2px; background-color: #f14242; color: #ffffff;">Plugin deactivated in "' . WP_ENV . '" by Frame Core</span>';

		return $meta;
	}

	function get_deactivations() {

        $rules = $this->rules;

        $deactivate = ( ! empty( $rules[ 'not_' . $this->env ] ) ) ? $rules[ 'not_' . $this->env ] : [];

        foreach( $deactivate as $index => $plugin_to_deactivate ){

            $folder_name = $this->get_folder_name( $plugin_to_deactivate );

            /*
			 * This allows us to bail on a per plugin basis using the env file, good for quick testing
			 *
			 * EG: FC_ACTIVATE_MAILGUN="true"
			 * Only works to STOP deactivating plugins
			 */

            if ( FC()->get_configuration_value( 'FC_ACTIVATE_' . strtoupper( $folder_name), false ) ) {
                unset($deactivate[$index]);
            } else {
                $deactivate[$index] = $folder_name;
            }

        }

        return $deactivate;

    }


}
