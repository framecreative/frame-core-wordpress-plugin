<?php

class FC_Env_Tag
{
	function __construct()
	{
		add_action('wp_after_admin_bar_render', array($this,'js'));
		add_action( 'wp_head', array($this,'css') );
	}

	function css()
	{
	?>
		<style type="text/css">
			#wp-admin-bar-site-name .ab-item .fc-env-tag {
				background: #9EA3A8;
				font-size: 10px;
				padding: 0 2px;
				text-transform: uppercase;
			}
		</style>
	<?php
	}

	function js()
	{
	?>
		<script type="text/javascript">
			(function($){
				$('#wp-admin-bar-site-name .ab-item').append(' <span class="fc-env-tag"><?php echo WP_ENV ?></span>')
			}(jQuery));
		</script>
	<?php
	}
}