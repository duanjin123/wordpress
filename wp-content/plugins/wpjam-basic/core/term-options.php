<?php
// 获取 Term Meta Options 
function wpjam_get_term_options($taxonomy=''){
	static $term_options;

	if(!isset($term_options)){
		$term_options = apply_filters('wpjam_term_options', WPJAM_TermOptionsSetting::get_all());	// 防止多次重复处理		
	}

	if(!$taxonomy){
		return $term_options;
	}

	static $taxonomy_options_list;

	if(empty($taxonomy_options_list)) {
		$taxonomy_options_list = [];
	}

	if(isset($taxonomy_options_list[$taxonomy])) {
		return $taxonomy_options_list[$taxonomy];
	}

	$taxonomy_options_list[$taxonomy]	= apply_filters('wpjam_'.$taxonomy.'_term_options', []);

	if(empty($term_options)){
		return $taxonomy_options_list[$taxonomy];
	}

	foreach ($term_options as $key => $term_option) {
		$term_option	= wp_parse_args( $term_option, array(
			'taxonomies'	=> 'all',
			'taxonomy'		=> ''
		));

		if($term_option['taxonomy'] && $term_option['taxonomies'] == 'all'){
			$term_option['taxonomies'] = array($term_option['taxonomy']);
		}

		if($term_option['taxonomies'] == 'all' || in_array($taxonomy, $term_option['taxonomies'])){
			$taxonomy_options_list[$taxonomy][$key]	= $term_option;
		}
	}

	return $taxonomy_options_list[$taxonomy];
}