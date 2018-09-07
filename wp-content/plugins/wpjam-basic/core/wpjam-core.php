<?php
include(WPJAM_BASIC_PLUGIN_DIR.'core/post-type.php');
include(WPJAM_BASIC_PLUGIN_DIR.'core/taxonomy.php');
include(WPJAM_BASIC_PLUGIN_DIR.'core/post-options.php');
include(WPJAM_BASIC_PLUGIN_DIR.'core/term-options.php');
include(WPJAM_BASIC_PLUGIN_DIR.'core/options.php');

if(is_admin()){
	include(WPJAM_BASIC_PLUGIN_DIR.'core/admin/admin.php');
}

function wpjam_get_filter_name($name='', $type=''){
	global $plugin_page;

	$filter	= str_replace('-', '_', $name);
	$filter	= str_replace('wpjam_', '', $filter);

	return 'wpjam_'.$filter.'_'.$type;
}