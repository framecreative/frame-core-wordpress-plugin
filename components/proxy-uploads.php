<?php

class FC_Proxy_Uploads {

	var $uploadsInfo;
	var $response;
	var $currentUrl;
	var $proxyUrl;
	var $displayOnly;

	function __construct() {

		$this->proxyUrl = FC()->get_configuration_value( 'FC_PROXY_UPLOADS_URL' );
		$this->displayOnly = FC()->get_configuration_value( 'FC_PROXY_DISPLAY_ONLY', false );

		if ( $this->proxyUrl ) {
			add_filter( '404_template', array( $this, 'proxy_upload' ) );
		}

	}

	function proxy_upload( $currentTemplate ) {
		global $wp;

		$this->currentUrl = home_url( $wp->request );
		$this->uploadsInfo = wp_upload_dir();

		if ( !stristr( $this->currentUrl, $this->uploadsInfo['baseurl'] ) ) {
			return $currentTemplate;
		}

		$proxyUrl = str_replace( $this->uploadsInfo['baseurl'], $this->proxyUrl, $this->currentUrl );

		$this->response = wp_remote_get( $proxyUrl );

		if ( is_wp_error($this->response) || 200 != $this->response['response']['code'] ) {
			return $currentTemplate;
		}

		if ( !$this->displayOnly ) {
			$this->attempt_download();
		} else {
			$this->display_and_exit();
		}



	}

	function display_and_exit() {

		global $wp_query;
		status_header( 200 );
		$wp_query->is_404 = false;

		foreach( $this->response['headers'] as $name => $value ){
			header( "$name: $value" );
		}

		echo $this->response['body'];
		exit;

	}

	function attempt_download() {

		if ( !function_exists('WP_Filesystem')) require ABSPATH.'wp-admin/includes/file.php';

		global $wp_filesystem;
		WP_Filesystem();

		$pathname = str_replace( $this->uploadsInfo['baseurl'], $this->uploadsInfo['basedir'], $this->currentUrl );
		$dir = dirname( $pathname );

		if ( !is_dir( $dir ) && !wp_mkdir_p( $dir ) ) {
			$this->display_and_exit();
		}

		$savedImage = $wp_filesystem->put_contents( $pathname, $this->response['body'], FS_CHMOD_FILE );

		$this->display_and_exit();

	}

}
