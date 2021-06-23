<?php

class FC_Rollbar_Error_Logging {

	function __construct(){

		$rollbar_key = Frame_Core::config('FC_ROLLBAR_API_KEY', null );

		if ( ! $rollbar_key  ) return;

		if( ! class_exists( '\Rollbar\Rollbar' ) ) return;

		$settings = [
			'access_token' => $rollbar_key,
			'environment' => Frame_Core::env(),
		];

		Rollbar\Rollbar::init( $settings );

		if ( isset( $_GET['rollbar_test'] ) && intval( $_GET['rollbar_test'] ) === 1 ){

			$message = 'Testing message from ' . ( defined( WP_HOME ) ? WP_HOME : 'Site URL not defined' );

			Rollbar\Rollbar::log( Rollbar\Payload\Level::INFO, $message );

		}

	}


}
