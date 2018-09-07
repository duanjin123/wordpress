<?php
// 获取某个选项的所有设置
function wpjam_get_option_setting($option_name){
	$wpjam_setting	= apply_filters(wpjam_get_filter_name($option_name,'setting'), []);

	if(!$wpjam_setting){
		$wpjam_settings = apply_filters('wpjam_settings', WPJAM_SettingsSetting::get_all());

		if(!$wpjam_settings) return false;

		if(empty($wpjam_settings[$option_name])) return false;

		$wpjam_setting	= $wpjam_settings[$option_name];
	}

	if(empty($wpjam_setting['sections'])){	// 支持简写
		if(isset($wpjam_setting['fields'])){
			$fields		= $wpjam_setting['fields'];
			$summary	= $wpjam_setting['summary']??'';
			unset($wpjam_setting['fields']);
			$wpjam_setting['sections']	= array($option_name => compact('fields','summary'));
		}else{
			$wpjam_setting['sections']	= $wpjam_setting;
		}
	}

	return wp_parse_args($wpjam_setting, array(
		'option_group'	=> $option_name, 
		'option_page'	=> $option_name, 
		'option_type'	=> 'array', 	// array：设置页面所有的选项作为一个数组存到 options 表， single：每个选项单独存到 options 表。
		'capability'	=> 'manage_options',
		'sections'		=> array()
	) );
}