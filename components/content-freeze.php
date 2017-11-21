<?php


class FC_Content_Freeze {

	function __construct()
	{

		$active = FC()->get_configuration_value('FC_CONTENT_FREEZE');

		if ( !$active ) return;

		add_action( 'admin_init', array( $this, 'logout_users' ), 20);
		add_filter( 'wp_authenticate_user', array( $this, 'authenticate_user') );
		add_action( 'admin_notices', array( $this, 'content_frozen_notice' ) );
		add_filter( 'login_message', array($this, 'login_warning_message'));

	}

	function logout_users() {

		if ( FC()->is_dev_user ) return;

		wp_logout();
		wp_redirect(wp_login_url());

	}

	function authenticate_user( $user ) {

		if ( is_wp_error($user) ) return $user;

		if ( $user->user_login === FC()->dev_user ) return $user;

		return new WP_Error( 'content-frozen', "<b>Error:</b> You cannot access the site while content is frozen." );

	}

	function content_frozen_notice() {
	?>

		<div class="update-nag" style="display: block;">
			<p style="margin: 0;"><b>Warning!</b> Content is currently frozen for other admins.</p>
		</div>

	<?php
	}

	function login_warning_message() {
	?>

		<div id="login_error">Content for this site is currently frozen.</div>

	<?php
	}

}