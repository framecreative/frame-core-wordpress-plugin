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

	}


}
