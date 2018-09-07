<?php
class WPJAM_CountLimit{
	const OPTION_NAME = 'wpjam_count_limit';

	public static function get_limits(){
		return get_option(self::OPTION_NAME);
	}

	public static function get_settings(){
		return apply_filters('wpjam_count_limit_settings', []);
	}

	public static function get_default($key){
		$settings	= static::get_settings();
		$setting 	= $settings[$key]??[];

		if($setting){
			return $setting['default'];
		}else{
			return 0;
		}
	}

	public static function get_limit($key){
		$limits		= self::get_limits();
		$value		= $limits[$key]??0;

		if($value){
			return $value;
		}

		return self::get_default($key);
	}

	public static function get_error($key){
		$settings	= static::get_settings();
		$setting 	= $settings[$key]??[];

		if($setting){
			$errmsg	= $setting['title'].'上限为：'.self::get_limit($key);

			if(!empty($setting['errmsg'])){
				$errmsg	= $errmsg.'，'.$setting['errmsg'];
			}

			return new WP_Error('too_much_'.$key, $errmsg);
		}else{
			return new WP_Error('too_much_'.$key, '已超上限');
		}
	}
}
