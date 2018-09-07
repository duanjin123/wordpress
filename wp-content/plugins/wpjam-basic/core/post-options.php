<?php
// 获取自定义字段设置
function wpjam_get_post_options($post_type=''){
	static $post_options;

	if(!isset($post_options)){
		$post_options = apply_filters('wpjam_post_options', []);
	}
	
	if(!$post_type)	{
		return $post_options;
	}

	static $post_type_options_list;

	if(empty($post_type_options_list)) {
		$post_type_options_list = [];
	}

	if(isset($post_type_options_list[$post_type])) {
		return $post_type_options_list[$post_type];
	}

	$post_type_options_list[$post_type]	= apply_filters('wpjam_'.$post_type.'_post_options', WPJAM_PostOptionsSetting::get_by_post_type($post_type));

	if(empty($post_options)){
		return $post_type_options_list[$post_type];
	}
	
	foreach($post_options as $meta_key => $post_option){
		$post_option = wp_parse_args($post_option, array(
			'priority'		=> 'high',
			'post_types'	=> 'all',
			'post_type'		=> '',
			'title'			=> ' ',
			'fields'		=> []
		));

		if($post_option['post_type'] && $post_option['post_types'] == 'all'){
			$post_option['post_types'] = [$post_option['post_type']];
		}

		if($post_option['post_types'] == 'all' || in_array($post_type, $post_option['post_types'])){
			$post_type_options_list[$post_type][$meta_key] = $post_option;
		}
	}
	
	return $post_type_options_list[$post_type];
}

function wpjam_get_post_fields($post_type=''){
	if($post_options = wpjam_get_post_options($post_type)) {
		if($post_type){
			static $post_type_fields_list;

			if(!isset($post_type_fields_list)) {
				$post_type_fields_list = [];
			}

			if(isset($post_type_fields_list[$post_type])) {
				return $post_type_fields_list[$post_type];
			}

			$post_type_fields_list[$post_type]	= call_user_func_array('array_merge', array_column(array_values($post_options), 'fields'));

			return $post_type_fields_list[$post_type];
		}else{
			return call_user_func_array('array_merge', array_column(array_values($post_options), 'fields'));
		}
	}else{
		return array();
	}
}