<?php
/*
Plugin Name: Xian Liao Me
Plugin URI:  https://developer.wordpress.org/plugins/the-basics/
Description: Xian Liao Me - Wordpress integration plugin
Version:     0.0.3
Author:      Seedspace
Author URI:  https://http://www.xianliao.me/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Create options upon activation
function xianliao_activate() {
    add_option( 'xlm_wid', '', '', 'yes' );
    add_option( 'xlm_key', '', '', 'yes' );
    add_option( 'xlm_url', 'https://www.xianliao.me/', '', 'yes' );
}
register_activation_hook( __FILE__, 'xianliao_activate' );

// Remove options when deactivated
function xianliao_deactivate() {
    delete_option('xlm_wid');
    delete_option('xlm_key');
    delete_option('xlm_url');
}
register_deactivation_hook( __FILE__, 'xianliao_deactivate' );

// -----------------------------------------------------------------
// Add script to WP
// -----------------------------------------------------------------

//
function xlm_enqueue_scripts() {
    wp_enqueue_script(
        'xlmjs',
        plugins_url( 'xianliao.js', __FILE__)
    );

    $wid = get_option( 'xlm_wid' );
    $key = get_option( 'xlm_key');
    $url = get_option( 'xlm_url' );

    $current_user = wp_get_current_user();
    $uid = $current_user->ID;
    $name = $current_user->user_login;
    $time = current_time( 'timestamp' );
    $hash = hash('sha512', $wid.'_'.$uid.'_'.$time.'_'.$key);

    // Enable xianliao to get default WordPress user avatar (or gravatar)
    // If other avatar management plugin is used, please replace 'get_avatar(...)'
    // with corresponding user avatar accessing function (eg, 'get_wp_user_avatar(...)').
    $avatar = get_avatar( $uid, 50, '', '', '' );

    $scriptData = array(
        'wid' => $wid,
        'url' => $url,
        'uid' => $uid,
        'name' => $name,
        'time' => $time,
        'hash' => $hash,
        'avatar' => $avatar,
    );

    wp_localize_script('xlmjs', 'xlm_options', $scriptData); //  now we can call xlm_options.xlm_wid to get the value

}
add_action( 'wp_enqueue_scripts', 'xlm_enqueue_scripts' );

// Embed xianliao in footer
function xlm_script() {
?>
    <script type='text/javascript' charset='UTF-8' src='https://xianliao.me/embed.js'></script>
<?php
}
add_action( 'wp_footer', 'xlm_script', 9999 );


// ------------------------------------------------------------------
// Add all your sections, fields and settings during admin_init
// ------------------------------------------------------------------
//

 function xlm_settings_init() {
 	// Add the section to reading settings so we can add our
 	// fields to it
 	add_settings_section(
		'xlm_setting_section', // ID
		'Enter Xian Liao Me WID and Secret Key here:', // Title
		'xlm_setting_section_callback_function', // Callback
		'general' // Page
	);

 	// Add the field with the names and function to use for our new
 	// settings, put it in our new section
 	add_settings_field(
		'xlm_wid', //ID
		'Xian Liao Me WID', // Title
		'xlm_wid_callback_function', // Callback
		'general', // Page
		'xlm_setting_section' // Section
	);

    add_settings_field(
		'xlm_key', //ID
		'Xian Liao Me Key', // Title
		'xlm_key_callback_function', // Callback
		'general', // Page
		'xlm_setting_section' // Section
	);

 	// Register our setting so that $_POST handling is done for us and
 	// our callback function just has to echo the <input>
 	register_setting( 'general', 'xlm_wid' );
    register_setting( 'general', 'xlm_key' );
 } //-- xlm_settings_init

 add_action( 'admin_init', 'xlm_settings_init' );


 // ------------------------------------------------------------------
 // Settings section callback function
 // ------------------------------------------------------------------
 //
 // This function is needed if we added a new section. This function
 // will be run at the start of our section

 function xlm_setting_section_callback_function() {
 	echo '<p>Add Xian Liao Me WID and Key in the fields below:</p>';
 }

 // ------------------------------------------------------------------
 // Callback function for our xlm wid and key settings
 // ------------------------------------------------------------------

 // Create a text field for WID
 function xlm_wid_callback_function() {
    if( !get_option('xlm_wid') ){
        $wid = '';
    }else{
        $wid = get_option('xlm_wid');
    }
    echo '<input type="text" id="xlm_wid" name="xlm_wid" value="' . $wid . '" />';
 }

// Create a text field for Key
 function xlm_key_callback_function() {
    if( !get_option('xlm_key') ){
        $key = '';
    }else{
        $key = get_option('xlm_key');
    }
    echo '<input type="text" id="xlm_key" name="xlm_key" value="' . $key . '" />';
 }
