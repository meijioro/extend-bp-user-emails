<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



class UserRegistrationEmailAdmin {

  public function __construct() {
    // Hook into the admin menu
    add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
    add_action( 'admin_init', array( $this, 'setup_sections' ) );
    add_action( 'admin_init', array( $this, 'setup_fields' ) );
  }

  /**
   * Create the admin page and add link to menu
   */
  public function create_plugin_settings_page() {
    // Add the menu item and page
    $page_title = 'BP User Registration Email Plugin';
    $menu_title = 'User Registration Email Settings';
    $capability = 'manage_options';
    $slug = 'bp-user-registration-email';
    $callback = array( $this, 'plugin_settings_page_content' ); // the html

    add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
  }

  /**
   * Create section to attach fields to on admin page
   */
  public function setup_sections() {
    add_settings_section( 'email_settings', 'Email Settings', false, 'bp-user-registration-email' );
  }


  public function setup_fields() {
    $fields = array(
      array(
        'uid' => 'approved_email',
        'label' => 'Approved Email',
        'section' => 'email_settings',
        'help_text' => 'Email will be sent to the user registering if approved by administrator.',
      ),
      array(
        'uid' => 'declined_email',
        'label' => 'Declined Email',
        'section' => 'email_settings',
        'help_text' => 'Email will be sent to the user registering if declined by administrator.',
      ),
    );
    
    foreach( $fields as $field ){
        add_settings_field( 
          $field['uid'], 
          $field['label'], 
          array( $this, 'field_callback' ), 
          'bp-user-registration-email', 
          $field['section'], 
          $field 
        );
        register_setting( 'bp-user-registration-email', $field['uid'] ); // save data into db
    }
    
  }


  public function field_callback($arguments) {
    $value = get_option( $arguments['uid'] ); // Get the current value, if there is one

    echo "<div style='width:75%;'>";
    
    wp_editor($value, $arguments['uid'], array(
      'textarea_name' => $arguments['uid'],
      'wpautop' => false,
      'default_editor' => true,
      'textarea_rows' => 8,
      'media_buttons' => false,
      'teeny' => false,
      'dfw' => false,
      'tinymce' => array(
        'wp_autoresize_on' =>  true,
        'quicktags' => true,
        'toolbar1' =>  'formatselect,bold,italic,underline,bullist,numlist,link,unlink',
      )
    ));
    echo '</div>';
    
    // If there is help_text text
    if ( $supplimental = $arguments['help_text'] ){
      printf( '<p class="description">%s</p>', $supplimental ); // Show it
    }
    
  }


  public function plugin_settings_page_content() {
?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e( 'User Registration Emails', 'bp-user-registration-emails-plugin' ); ?></h1>
        <p>This is where you can change the email copy to send whenever a pending registration is either accepted or declined.</p>
        <p><strong>Flowchart:</strong></p>
        <ol>
          <li>New user registers -> email notification to CDM Master Email (set in General Settings) and Geri</li>
          <li>Admin Approves -> email new user is approved and can now login</li>
          <li>Admin Declines -> email new user is declined and deleted from the database</li>
        </ol>

        <hr />

        <form method="post" action="options.php">
            <?php
                settings_fields( 'bp-user-registration-email' ); // param is the slug
                do_settings_sections( 'bp-user-registration-email' ); // param is the slug
                submit_button();
            ?>
        </form>
    </div> 
<?php
  }

} //close Class

if ( class_exists('UserRegistrationEmailAdmin') ) {
  $cdm_userRegistrationEmailAdmin = new UserRegistrationEmailAdmin();
}

?>
