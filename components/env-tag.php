<?php

class FC_Env_Tag
{
	function __construct()
	{
		add_action( 'wp_after_admin_bar_render', array($this,'js') );
		add_action( 'wp_before_admin_bar_render', array($this,'css') );
	}

	function css()
	{
	?>
		<style type="text/css">
			#wp-admin-bar-site-name .ab-item .fc-env-tag {
				background: #9EA3A8;
				color: #23282D;
				font-weight: bold;
				font-size: 10px;
				padding: 0 3px;
				text-transform: uppercase;
				margin-left: 1px;
				border-radius: 2px;
				position: relative;
				top: -1px;
			}

			#wp-admin-bar-site-name:hover .ab-item .fc-env-tag,
			#wp-admin-bar-site-name.hover .ab-item .fc-env-tag {
				background: #00B8ED;
			}
		</style>
	<?php
	}

	function js()
	{
	?>
		<script type="text/javascript">
			(function($){
				$('#wp-admin-bar-site-name > .ab-item').append(' <span class="fc-env-tag"><?php echo WP_ENV ?></span>')
			}(jQuery));
		</script>
	<?php
	}
}