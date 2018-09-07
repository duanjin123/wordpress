<?php

include(WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-cron.php');

add_filter('wpjam_crons_list_table', function(){
	return array(
		'title'			=> '定时作业',
		'plural'		=> 'crons',
		'singular' 		=> 'cron',
		'primary_column'=> 'hook',
		'fixed'			=> false,
		'model'			=> 'WPJAM_Cron',
		'actions'		=> array(
			'do'		=> array('title'=>'立即执行',	'direct'=>true),
			'delete'	=> array('title'=>'删除',	'direct'=>true,	'bulk'=>true)
		),
		'fields'		=> array(
			'hook'		=> array('title'=>'Hook',	'type'=>'text',	'show_admin_column'=>true),
			'args'		=> array('title'=>'参数',	'type'=>'text',	'show_admin_column'=>true),
			'timestamp'	=> array('title'=>'下次运行',	'type'=>'text',	'show_admin_column'=>true),
			'interval'	=> array('title'=>'频率',	'type'=>'text',	'show_admin_column'=>true),
		)
	);
});