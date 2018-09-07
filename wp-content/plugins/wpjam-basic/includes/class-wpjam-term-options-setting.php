<?php
class WPJAM_TermOptionsSetting extends WPJAM_Model {
	private static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_term_options', 'field_key');
		}
		return static::$handler;
	}

	public static function get_all(){

		$settings = parent::get_all();

		if(!$settings) return array();

		foreach($settings as $field_key => $args){
			if(empty($field_key))	return;

			$field	= wpjam_parse_fields_setting($args['field']);
			$field['taxonomies']	= array_values($args['taxonomies']);

			$settings[$field_key]	= $field;
		}
		
		return $settings;
	}
}


