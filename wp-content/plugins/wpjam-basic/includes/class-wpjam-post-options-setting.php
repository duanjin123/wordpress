<?php
class WPJAM_PostOptionsSetting extends WPJAM_Model {
	private static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_post_options', 'meta_box');
		}
		return static::$handler;
	}

	public static function get_all(){

		$settings = parent::get_all();

		if(!$settings) return [];
		
		foreach ($settings as $meta_box => &$args) {
			$args['fields']	= wpjam_parse_fields_setting($args['fields']);	
							
			$args = wp_parse_args($args, array(
				'priority'		=> 'high',
				'title'			=> '',
				'post_types'	=> []
			));
		}

		return $settings;
	}

	public static function get_by_post_type($post_type){

		$settings = parent::get_all();

		if(!$settings) return [];

		$post_type_settings	= [];
		
		foreach ($settings as $meta_box => $args) {
			if($args['post_types'] && in_array($post_type, $args['post_types'])){
				$args['fields']		= wpjam_parse_fields_setting($args['fields']);
				$args['priority']	= 'high';
				$post_type_settings[$meta_box]	= $args;
			}			
		}

		return $post_type_settings;
	}
}
