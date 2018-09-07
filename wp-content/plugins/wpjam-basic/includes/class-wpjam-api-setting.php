<?php
Class WPJAM_APISetting extends WPJAM_Model{
	private static $handler;

	public static function get_handler(){
		global $wpdb;
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_apis', 'json');
		}
		return static::$handler;
	}

	public static function insert($data){
		$data['json']	= str_replace('mag.', '', $data['json']);
		return parent::insert($data);
	}

	public static function get($json){
		if($api	= parent::get($json)){
			$api['json']	= 'mag.'.$api['json'];
		}
		return $api;
	}

	public static function item_callback($item){
		$item['json']	= 'mag.'.$item['json'];
		return $item;
	}
}