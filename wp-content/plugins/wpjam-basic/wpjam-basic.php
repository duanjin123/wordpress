<?php
/*
Plugin Name: WPJAM BASIC
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: WPJAM 常用的函数和 Hook，屏蔽所有 WordPress 所有不常用的功能。
Version: 3.1.3
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if (version_compare(PHP_VERSION, '7.2.0') < 0) {
	include(plugin_dir_path(__FILE__).'php5/wpjam-basic.php');
}else{
	define('WPJAM_BASIC_PLUGIN_URL', plugins_url('', __FILE__));
	define('WPJAM_BASIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
	define('WPJAM_BASIC_PLUGIN_FILE',  __FILE__);

	if(!defined('SAVEQUERIES')) define('SAVEQUERIES', true);

	include(WPJAM_BASIC_PLUGIN_DIR.'wpjam-compat.php');		// 兼容代码 

	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-model.php');	// WPJAM Model 和 WPDB class 操作类
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-api.php');		// API接口处理 class
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-notice.php');	// 消息通知  class
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-cache.php');	// 缓存  class

	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-post-type-setting.php');		// Post Type 设置  class
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-post-options-setting.php');	// Post Options 设置  class
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-taxonomy-setting.php');		// Taxonomy 设置  class
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-term-options-setting.php');	// Term Options 设置  class
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-settings-setting.php');		// Settings 设置  class
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-api-setting.php');				// API 设置 class
	include(WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-count-limit.php');				// 数量上限

	include(WPJAM_BASIC_PLUGIN_DIR.'wpjam-functions.php');	// 常用函数

	include(WPJAM_BASIC_PLUGIN_DIR.'core/wpjam-core.php');	// 加载 WPJAM 基础类库

	include(WPJAM_BASIC_PLUGIN_DIR.'wpjam-hooks.php');		// 基本优化
	include(WPJAM_BASIC_PLUGIN_DIR.'wpjam-route.php');		// Module Action 路由
	include(WPJAM_BASIC_PLUGIN_DIR.'wpjam-shortcode.php');	// Shortcode
	include(WPJAM_BASIC_PLUGIN_DIR.'wpjam-cdn.php');		// CDN
	include(WPJAM_BASIC_PLUGIN_DIR.'wpjam-thumbnail.php');	// 缩略图
	include(WPJAM_BASIC_PLUGIN_DIR.'wpjam-posts.php');		// 日志列表

	if(is_admin()) {
		include(WPJAM_BASIC_PLUGIN_DIR.'admin/admin.php');
	}

	wpjam_include_extends();	// 加载扩展

	do_action('wpjam_loaded');
}