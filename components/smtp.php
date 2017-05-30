<?php

class FC_SMTP {

	function __construct() {

		add_action( 'phpmailer_init', array( $this, 'configure' ) );

	}

	function configure( $phpmailer ) {

		$smtpHost = 		FC()->get_configuration_value('FC_SMTP_HOST');
		$smtpUser = 		FC()->get_configuration_value('FC_SMTP_USER');
		$smtpPassword = 	FC()->get_configuration_value('FC_SMTP_PASSWORD');
		$smtpPort = 		FC()->get_configuration_value('FC_SMTP_PORT');
		$smtpFrom = 		FC()->get_configuration_value('FC_SMTP_FROM');
		$smtpFromName = 	FC()->get_configuration_value('FC_SMTP_FROM_NAME');

		if (!$smtpHost) 		$smtpHost = 		FC()->get_configuration_value('SMTP_HOST');
		if (!$smtpUser) 		$smtpUser = 		FC()->get_configuration_value('SMTP_USER');
		if (!$smtpPassword) 	$smtpPassword = 	FC()->get_configuration_value('SMTP_PASSWORD');
		if (!$smtpPort) 		$smtpPort = 		FC()->get_configuration_value('SMTP_PORT');
		if (!$smtpFrom) 		$smtpFrom = 		FC()->get_configuration_value('SMTP_FROM');
		if (!$smtpFromName) 	$smtpFromName = 	FC()->get_configuration_value('SMTP_FROM_NAME');

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

		if ( $smtpFrom ) 		$phpmailer->From = $smtpFrom;
		if ( $smtpFromName ) 	$phpmailer->FromName = $smtpFromName;

	}

}