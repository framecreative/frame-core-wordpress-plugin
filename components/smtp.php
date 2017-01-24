<?php

class FC_SMTP {

	function __construct() {

		add_action( 'phpmailer_init', array( $this, 'configure' ) );

	}

	function configure( $phpmailer ) {

		$smtpHost = getenv('SMTP_HOST');
		$smtpUser = getenv('SMTP_USER');
		$smtpPassword = getenv('SMTP_PASSWORD');
		$smtpPort = getenv('SMTP_PORT');

		if ( $smtpHost && $smtpUser && $smtpPassword ) {

			$phpmailer->isSMTP();
			$phpmailer->Host = $smtpHost;
			$phpmailer->SMTPAuth = TRUE;
			$phpmailer->Port = $smtpPort ? $smtpPort : 25;
			$phpmailer->Username = $smtpUser;
			$phpmailer->Password = $smtpPassword;
			$phpmailer->SMTPSecure = FALSE;


		} elseif ( WP_ENV == 'dev' ) {

			$phpmailer->isSMTP();
			$phpmailer->Port = $smtpPort ? $smtpPort : 1025;
			$phpmailer->SMTPSecure = FALSE;

		}

	}

}