<?php

class FC_Google_Tag_Manager {

	private $gtmID;

	function __construct() {

		 $this->gtmID = FC()->get_configuration_value('FC_GTM_ID');

		if ( !$this->gtmID ) return;

		add_action( 'wp_head', array( $this, 'install_gtm' ) );
		add_action( 'wp_footer', array( $this, 'install_gtm_noscript' ) );

	}

	function install_gtm() {
	?>

		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
						'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo $this->gtmID ?>');</script>
		<!-- End Google Tag Manager -->

	<?php
	}

	function install_gtm_noscript() {
	?>

		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $this->gtmID ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->

	<?php
	}

}