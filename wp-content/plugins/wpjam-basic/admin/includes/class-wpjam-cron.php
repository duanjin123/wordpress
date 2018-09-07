<?php
class WPJAM_Cron{
	public static function get_primary_key(){
		return 'cron_id';
	}

	public static function get($id){
		list($timestamp, $hook, $key)	= explode('--', $id);

		$wp_crons = _get_cron_array();

		if(isset($wp_crons[$timestamp][$hook][$key])){
			$data	= $wp_crons[$timestamp][$hook][$key];
			$data['hook']		= $hook;	
			$data['timestamp']	= $timestamp;	
			return $data;
		}else{
			return new WP_Error('cron_not_exist', '该定时作业不存在');
		}
	}

	public static function do($id){
		$data = self::get($id);

		if(is_wp_error($data)){
			return $data;
		}else{
			do_action_ref_array($data['hook'], $data['args']);
			return true;
		}
	}

	public static function delete($id){
		$data = self::get($id);
		
		if(is_wp_error($data)){
			return $data;
		}else{
			wp_unschedule_event($data['timestamp'], $data['hook'], $data['args'] );
			return true;
		}
	}

	// 后台 list table 显示
	public static function list($limit, $offset){
		$items	= array();

		foreach (_get_cron_array() as $timestamp => $wp_cron) {
			foreach ($wp_cron as $hook => $dings) {
				foreach( $dings as $key=>$data ) {
					if(!has_filter($hook)){
						wp_unschedule_event($timestamp, $hook, $data['args']);	// 系统不存在的定时作业，自动清理
						continue;
					}
					
					$items[] = array(
						'cron_id'		=> $timestamp.'--'.$hook.'--'.$key,
						'timestamp'		=> get_date_from_gmt( date('Y-m-d H:i:s', $timestamp) ),
						'hook'			=> $hook,
						'args'			=> $data['args']?implode(',', $data['args']):'',
						'interval'		=> isset($data['interval'])?$data['schedule'].'（'.$data['interval'].'）':'',
					);
				}
			}
		}

		$total = count($items);

		return compact('items', 'total');
	}
}