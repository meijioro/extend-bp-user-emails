<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class ExtendBuddyPressFunctions {

  public function __construct() {
    add_filter( 'bp_members_ms_signup_row_actions', array($this,'override_pending_users_action_links'), 10, 2 );

    add_action( 'user_register', array( $this, 'user_registered_email') );
    add_action( 'bp_core_activated_user', array($this, 'core_activated_user'), 1, 3);
    add_action('bp_core_signup_before_delete', array($this, 'email_delete_pending') );
  }


  /**
   * Send an email to the administrator email upon new user registration.
   *   
   * @param int $user_id User ID.
   */
  public function user_registered_email($user_id) {

    // Admin email.
    $message = 'A new user has registered. To accept or decline their request, please go to the <a href="' . get_bloginfo('url') . '/wp-admin/users.php?page=bp-signups" target="_blank">list of pending users</a>.' ;

    $args = array(
      'admin_email' => get_option('my_custom_email_field'), // my custom email field in general settings
      'subject_line' => __('New Pending Registration Request', 'bp-user-registration-emails-plugin'),
      'message' =>  $this->email_html_wrapper($message),
    );

    $post_log = get_stylesheet_directory() . '/post_log.txt';
    if (file_exists($post_log)) {
      $file = fopen($post_log,'a');
      fwrite($file, print_r($args,true));
    } else {
      $file = fopen($post_log,'w');
      fwrite($file, print_r($args,true));
    }
    fclose($file);

    add_filter( 'wp_mail_content_type', array($this, 'email_content_type') );
    add_filter( 'wp_mail_from_name', array($this, 'email_from_name') );
      
    wp_mail( $args['admin_email'], $args['subject_line'], $args['message'] );

    remove_filter( 'wp_mail_content_type', array($this,'email_content_type') );
    remove_filter( 'wp_mail_from_name', array($this,'email_from_name') );
  }

  /**
   * Send an email to user upon registration approval by admin from wp-admin.
   * The email was deactivated in the BP emai admin so it won't trigger the core BP code.
   * 
   * @param string $content_type Content type.
   * @return string $value new content type to use
   */
  function core_activated_user( $user_id, $key, $args = array() ) {
    
    $user = get_userdata($user_id);
    $message = get_option('approved_email');

    $args = wp_parse_args( $args, array(
      'user_email' => $user->data->user_email,
      'subject_line' => __('Approved Registration', 'bp-user-registration-emails-plugin'),
      'message' => $this->email_html_wrapper($message),
    ) );
    
    add_filter( 'wp_mail_content_type', array($this,'email_content_type') );
    add_filter( 'wp_mail_from_name', array($this,'email_from_name') );

    wp_mail( $args['user_email'], $args['subject_line'], $args['message'] );

    remove_filter( 'wp_mail_content_type', array($this,'email_content_type') );
    remove_filter( 'wp_mail_from_name', array($this,'email_from_name') );
  }


  /**
   * Hook into BP's function of deleting pending users.
   * Sends an email to user if registration was declined.
   * 
   * @param array $signup_ids
   */
  public function email_delete_pending($signup_ids) {

    if ( empty($signup_ids) || !is_array($signup_ids) ) {
      return false;
    }

    // call get() inside BP_Signup class
    // returns $signups
    $to_delete = BP_Signup::get( array(
      'include' => $signup_ids,
    ) );

    if ( !$signups = $to_delete['signups'] ) {
      return false;
    }

    add_filter( 'wp_mail_content_type', array($this,'email_content_type') );
    add_filter( 'wp_mail_from_name', array($this,'email_from_name') );

    foreach ($signups as $signup) {
      $user_id = username_exists($signup->user_login);

      if ( ! empty($user_id) && $signup->activation_key === bp_get_user_meta($user_id, 'activation_key', true) ) {

          $message = get_option('declined_email');
          
          $args = wp_parse_args( $args, array(
            'user_email' =>  $signup->user_email,
            'subject_line' =>  __('Declined Registration', 'bp-user-registration-emails-plugin'),
            'message'    =>  $this->email_html_wrapper($message),
          ) );

          wp_mail( 
            $args['user_email'], 
            $args['subject_line'], 
            $args['message'] 
          );

      }
    }

    remove_filter('wp_mail_content_type', array($this,'email_content_type') );
    remove_filter('wp_mail_from_name', array($this,'email_from_name') );
  }


  /**
   * Callback function for HTML email from name.
   * 
   * @param string $name 
   * @return string $value name of website
   */
  public function email_from_name($name) {
    return '[' . get_bloginfo('name') . ']';
  }

  /**
   * Callback function for HTML email purposes.
   * 
   * @param string $content_type Content type.
   * @return string $value new content type to use
   */
  public function email_content_type($content_type) {
    return 'text/html';
  }

  /**
   * Email html wrapper
   * 
   * @param string $message
   * @return string 
   */

  public function email_html_wrapper($message) {
    $html  = '<html><body>';
    $html .= '<div style="font-size:16px;">';
    $html .= $message;        
    $html .= '</div>';
    $html .= '</body></html>';

    return $html;
  }

  /**
   * Changing the copy for the actions on user pending page
   * 
   * src/bp-members/classes/class-bp-members-list-table.php (line 331)
   */
  public function override_pending_users_action_links($actions, $signup_object) {
    //Remove Email resend action
    unset($actions['resend']);

    // Delete link.
    $delete_link = add_query_arg(
      array(
        'page'      => 'bp-signups',
        'signup_id' => $signup_object->id,
        'action'    => 'delete',
      ),
      bp_get_admin_url( 'users.php' )
    );

    // Change text
    $actions['delete'] = sprintf( '<a href="%1$s" class="delete">%2$s</a>', esc_url($delete_link), __( 'Decline', 'buddypress' ) );
    return $actions;
  }


} //close Class

if ( class_exists('ExtendBuddyPressFunctions') ) {
  new ExtendBuddyPressFunctions();
}

?>
