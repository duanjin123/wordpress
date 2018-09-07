<?php
class WPJAM_Verify{
	public static function verify(){
		$weixin_user	= self::get_weixin_user();

		if($weixin_user && $weixin_user['subscribe']){
			if(time() - $weixin_user['last_update'] < WEEK_IN_SECONDS*2) {
				return true;
			}else{
				$weixin_user	= self::update_weixin_user($weixin_user['openid']);
				if(!is_wp_error($weixin_user) && $weixin_user && $weixin_user['subscribe']){
					return true;
				}else{
					return false;
				}
			}
		}

		return false;
	}

	public static function get_weixin_user(){
		return get_user_meta(get_current_user_id(), 'wpjam_weixin_user', true);
	}

	public static function update_weixin_user($openid){
		$weixin_user	= wpjam_remote_request('http://jam.wpweixin.com/api/get_user.json?openid='.$openid);

		if(is_wp_error($weixin_user)){
			return $weixin_user;
		}

		$weixin_user['last_update']	= time();

		update_user_meta(get_current_user_id(), 'wpjam_weixin_user', $weixin_user);

		return $weixin_user;
	}

	public static function get_openid(){
		$weixin_user	= self::get_weixin_user();

		return $weixin_user?$weixin_user['openid']:'';
	}

	public static function get_qrcode($key=''){
		$key	= $key?:md5(home_url().'_'.get_current_user_id());

		return wpjam_remote_request('http://jam.wpweixin.com/api/get_qrcode.json?key='.$key);
	}

	public static function bind_user($data){
		$response	= wpjam_remote_request('http://jam.wpweixin.com/api/bind_user.json', [
			'method'	=>'POST',
			'body'		=> $data
		]);

		if(is_wp_error($response)){
			return $response;
		}

		$weixin_user =	$response['user']; 

		$weixin_user['last_update']	= time();

		update_user_meta(get_current_user_id(), 'wpjam_weixin_user', $weixin_user);

		return $weixin_user;
	}
}