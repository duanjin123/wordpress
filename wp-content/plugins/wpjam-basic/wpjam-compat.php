<?php
// 兼容代码
if(!function_exists('wp_cache_delete_multi')){
	function wp_cache_delete_multi( $keys, $group = '' ) {
		foreach ($keys as $key) {
			wp_cache_delete($key, $group);
		}

		return true;
	}
}
	
if(!function_exists('wp_cache_get_multi')){	
	function wp_cache_get_multi( $keys, $group = '' ) {

		$datas = [];

		foreach ($keys as $key) {
			$datas[$key] = wp_cache_get($key, $group);
		}

		return $datas;
	}
}

if(!function_exists('wp_cache_get_with_cas')){
	function wp_cache_get_with_cas( $key, $group = '', &$cas_token = null ) {
		return wp_cache_get($key, $group);
	}
}

if(!function_exists('wp_cache_cas')){
	function wp_cache_cas( $cas_token, $key, $data, $group = '', $expire = 0  ) {
		return wp_cache_set($key, $data, $group, $expire);
	}
}