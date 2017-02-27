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

class FC_Disable_Admin_Nags {

  public function __construct() {
      //add other plugins we commonly use (Yoast?)
  }


}
