<?php
if( !defined('WPCOM_ADMIN_PATH') ) {
    define( 'WPCOM_ADMIN_VERSION', '1.7' );
    define( 'WPCOM_ADMIN_PATH', plugin_dir_path( __FILE__ ) );
    define( 'WPCOM_ADMIN_URI', plugins_url( '/', __FILE__ ) );

    require WPCOM_ADMIN_PATH . '/panel.php';
}