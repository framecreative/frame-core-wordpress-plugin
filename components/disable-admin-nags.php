<?php


/*
 * Some plugins require you to override their notification function
 * - eg: woocommerce
 *
 * place these functions before the class definition so they are in the global namespace
 */


/*
 * Will remove the 'install woothemes updater' nag as we use composer
 */
if ( ! function_exists( 'woothemes_updater_notice' ) ) {

  function woothemes_updater_notice() {
    return '';
  }

}

/**
 * Remove the Extension Works Framework plugin nag
 */
if ( ! function_exists( 'ew_framework_notice' ) ) {
    function ew_framework_notice(){
        return '';
    }
}
if ( ! function_exists( 'inactived_plugin_notification' ) ) {
    function inactived_plugin_notification(){
        return '';
    }
}

/**
 * Remove the WC CBA update plugin nag
 */
class FC_Disable_Admin_Nags {

  public function __construct() {
      add_action( 'admin_notices', [ $this, 'remove_extensionworks_activation_notice'], 1);

      add_filter( 'woocommerce_helper_suppress_connect_notice', '__return_true' );

  }

  public function remove_extensionworks_activation_notice(){
      if ( ! array_key_exists( 'ew_updater', $GLOBALS ) )  return;
      $instance = $GLOBALS['ew_updater']->admin;
      remove_action( 'admin_notices', [ $instance, 'inactived_plugin_notification'], 10 );
  }
}

