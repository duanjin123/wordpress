<?php
if(!empty($args['option_name'])){
	$option_name	= $args['option_name']??'';
	$setting		= $args['setting']??'';
	$output			= $args['output']??'';

	if($setting){
		$setting_value	= wpjam_get_setting($option_name, $setting, $configurator=true);
		if(is_wp_error($setting_value)){
			wpjam_send_json($setting_value);
		}
		$output	= $output?:$setting; 
		$response[$output]		= $setting_value;
	}else{
		$option_value	= wpjam_get_option($option_name, $configurator=true);
		if(is_wp_error($option_value)){
			wpjam_send_json($option_value);
		}
		$output	= $output?:$option_name; 
		$response[$output]	= $option_value;
	}
}