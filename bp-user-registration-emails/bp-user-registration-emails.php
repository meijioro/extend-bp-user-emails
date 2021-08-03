<?php
/**
 * Plugin Name: BP User Registration Emails
 * Version: 1.0
 * Author: Kim Le
 * Author URI: https://www.infina.net/
 * Description: This is a Buddypress extension that sends a email notification to a specific email instead of all administrators nor webmaster and user on new registration pending/approval/declined. There is a settings section to edit the copy for the email. Has been tested up to Buddypress v6.
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class UserRegistrationEmailPlugin {

  function __construct() {
    require_once( plugin_dir_path( __FILE__ ) . 'includes/admin-settings.php' );
    require_once( plugin_dir_path( __FILE__ ) . 'includes/bp-extension.php' );
  }

  public function activate() {
    flush_rewrite_rules();
  }

  public function deactivate() {
    flush_rewrite_rules();
  }

}

/**
 * Make sure class exists
 */
if ( class_exists('UserRegistrationEmailPlugin') ) {
  $userRegistrationEmailPlugin = new UserRegistrationEmailPlugin();
}


/**
 * Activation hook for the plugin.
 */
register_activation_hook( __FILE__, array($userRegistrationEmailPlugin, 'activate') );

/**
 * Deactivation hook for the plugin.
 */
register_deactivation_hook( __FILE__, array($userRegistrationEmailPlugin, 'deactivate') );


?>