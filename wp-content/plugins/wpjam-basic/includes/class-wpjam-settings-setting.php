<?php
class WPJAM_SettingsSetting extends WPJAM_Model {
	private static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_settings', 'option_name');
		}
		return static::$handler;
	}

	public static function get_all(){
		$settings = parent::get_all();

		if(!$settings) return array();

		foreach ($settings as $option_name => &$args) {
			$args['fields']	= wpjam_parse_fields_setting($args['fields']);
		}

		return $settings;
	}

	public static function parse_for_json($option_name, $setting=''){
		$option			= get_option($option_name);
		$option_setting	= wpjam_get_option_setting($option_name);

		if(empty($option_setting)){
			return new WP_Error('option_not_exists', $option_name.'设置不存在');
		}

		$option_fields	= current($option_setting['sections'])['fields'];

		if($setting){

			if(!isset($option_fields[$setting])){
				return new WP_Error('setting_not_exists', $setting.'设置不存在');
			}

			$setting_field	= $option_fields[$setting];
			$setting_value	= ($option[$setting])??'';

			if($setting_field['type'] == 'fieldset'){
				$fieldset_type	= ($setting_field['fieldset_type'])??'single';
				if($setting_field['fieldset_type'] = 'single'){
					$setting_value		= array();
					foreach ($setting_field['fields'] as $sub_key => $sub_field) {
						$setting_value[$sub_key]	= ($option[$sub_key])??'';
					}
				}
			}

			return wpjam_parse_field_value($setting_value, $setting_field);
		}else{
			$value	= array();
			if($option){
				foreach ($option_fields as $setting => $setting_field) {
					$setting_value	= ($option[$setting])??'';

					if($setting_field['type'] == 'fieldset'){
						$fieldset_type	= ($setting_field['fieldset_type'])??'single';
						if($setting_field['fieldset_type'] = 'single'){
							$setting_value		= array();
							foreach ($setting_field['fields'] as $sub_key => $sub_field) {
								$setting_value[$sub_key]	= ($option[$sub_key])??'';
							}
						}
					}

					$value[$setting]	= wpjam_parse_field_value($setting_value, $setting_field);
				}
			}

			return $value;
		}
	}
}