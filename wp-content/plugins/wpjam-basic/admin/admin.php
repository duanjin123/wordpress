<?php
include(WPJAM_BASIC_PLUGIN_DIR.'admin/admin-menus.php');	// 后台菜单
include(WPJAM_BASIC_PLUGIN_DIR.'admin/custom.php');			// 自定义后台
include(WPJAM_BASIC_PLUGIN_DIR.'admin/stats.php');			// 后台统计基础函数
include(WPJAM_BASIC_PLUGIN_DIR.'admin/users.php');			// 用户

include(WPJAM_BASIC_PLUGIN_DIR.'admin/verify.php');			// 验证
include(WPJAM_BASIC_PLUGIN_DIR.'admin/topic.php');			// 讨论组

wpjam_include_extends($admin=true);	// 加载扩展，获取扩展的后台设置文件

register_activation_hook( WPJAM_BASIC_PLUGIN_FILE, function(){
	global $wpdb;

	flush_rewrite_rules();

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$wpdb->messages		= $wpdb->base_prefix . 'messages';
	
	if($wpdb->get_var("show tables like '{$wpdb->messages}'") != $wpdb->messages) {
		$sql = "
		CREATE TABLE IF NOT EXISTS `{$wpdb->messages}` (
			`id` bigint(20) NOT NULL auto_increment,
			`sender` bigint(20) NOT NULL,
			`receiver` bigint(20) NOT NULL,
			`content` longtext NOT NULL,
			`status` int(1) NOT NULL,
			`time` int(10) NOT NULL,
			PRIMARY KEY  (`id`)
		) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
 
		dbDelta($sql);
	}

	// if($wpdb->get_var("show tables like '{$wpdb->devices}'") != $wpdb->devices) {
	// 	$sql = "
	// 	CREATE TABLE IF NOT EXISTS `{$wpdb->devices}` (
	// 		`id` bigint(20) NOT NULL auto_increment,
	// 		`device` varchar(32) NOT NULL,
	// 		`name` varchar(255) NOT NULL,
	// 		`brand` varchar(32) NOT NULL,
	// 		`size` varchar(32) NOT NULL,
	// 		PRIMARY KEY  (`id`),
	// 		UNIQUE KEY `device` (`device`)
	// 	) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
	// 	";
 
	// 	dbDelta($sql);
	// }

	// if($wpdb->get_var("show tables like '{$wpdb->activities}'") != $wpdb->activities) {
	// 	$sql = "
	// 	CREATE TABLE IF NOT EXISTS `{$wpdb->activities}` (
	// 		`id` bigint(20) NOT NULL auto_increment,
	// 		`blog_id` bigint(20) NOT NULL,
	// 		`user_id` bigint(20) NOT NULL,
	// 		`type` VARCHAR( 64 ) NOT NULL ,
	// 		`hook` VARCHAR( 255 ) NOT NULL ,
	// 		`object` VARCHAR(255) NOT NULL,
	// 		`note` TEXT NOT NULL,
	// 		`credit_user` bigint(20) NOT NULL default '0',
	// 		`credit_change` int(10) NOT NULL ,
	// 		`credit` int(10) NOT NULL ,
	// 		`exp_change` int(10) NOT NULL ,
	// 		`exp` int(10) NOT NULL ,
	// 		`limit` int(1) NOT NULL default '0',
	// 		`url` varchar(255) NOT NULL,
	// 		`time` datetime NOT NULL,
	// 		`ip` varchar(23) NOT NULL,
	// 		`ua` varchar(255) NOT NULL,
	// 		PRIMARY KEY  (`id`)
	// 	) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
	// 	";
 
	// 	dbDelta($sql);
	// }
});

add_action('plugins_loaded', function(){
	if(!wpjam_is_scheduled_event('wpjam_remove_invild_crons')) {
		wp_schedule_event(time(), 'daily', 'wpjam_remove_invild_crons');
	}

	// if(wp_next_scheduled('wp_scheduled_auto_draft_delete')){
	// 	wp_clear_scheduled_hook('wp_scheduled_auto_draft_delete');
	// }	
	if(!wpjam_is_scheduled_event('wpjam_scheduled_auto_draft_delete')) {
		wp_schedule_event(time(), 'hourly', 'wpjam_scheduled_auto_draft_delete');
	}

	// if(wp_next_scheduled('wp_scheduled_delete')){
	// 	wp_clear_scheduled_hook('wp_scheduled_delete');
	// }
	if(!wpjam_is_scheduled_event('wpjam_scheduled_delete')) {
		wp_schedule_event(time(), 'daily', 'wpjam_scheduled_delete');
	}
});

// 给测试版插件加上测试版标签
add_filter('all_plugins',function ($all_plugins){
	foreach($all_plugins as $plugin_file => $plugin_data){
		if(strpos($plugin_file, 'test') !== false || strpos($plugin_file, 'beta') !== false){
			$all_plugins[$plugin_file]['Name'] = $plugin_data['Name'].'《测试版》';
		}
	}
	return $all_plugins;
});

// add_filter('pre_get_blogs_of_user', '__return_empty_array');
// add_filter('get_blogs_of_user', function($sites){
// 	wpjam_print_r($sites);
// });

