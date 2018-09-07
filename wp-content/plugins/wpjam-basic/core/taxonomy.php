<?php
// 注册自定义分类
add_action('init', 'wpjam_taxonomy_init', 11);
function wpjam_taxonomy_init(){
	$wpjam_taxonomies = apply_filters('wpjam_taxonomies', WPJAM_TaxonomySetting::get_all());

	// wpjam_print_R(get_option('wpjam_apis'));

	if(!$wpjam_taxonomies) return;

	foreach ($wpjam_taxonomies as $taxonomy=>$wpjam_taxonomy) {
		$object_type	= $wpjam_taxonomy['object_type'];
		$taxonomy_args	= wp_parse_args($wpjam_taxonomy['args'], array(
			'label'				=> '', 
			'hierarchical'		=> true, 
			'public'			=> false,
			'show_ui'			=> true,
			'show_in_nav_menus'	=> false,
			'show_admin_column'	=> true,
			'query_var'			=> false, 
			'rewrite'			=> false
		));

		$label	= ($taxonomy_args['label'])??'';
		$labels	= ($taxonomy_args['labels'])??[];

		if(is_admin() && $label && (empty($labels) || is_string($labels))) {
			$label_name		= $label;
			$current_labels	= $labels;
			
			add_filter('taxonomy_labels_'.$taxonomy, function($labels) use ($label_name, $current_labels){
				$labels	= array_map(function($label) use ($label_name){
					if($label == $label_name) return $label;
					return str_replace(
						array('目录', '分类', '标签', 'categories', 'Categories', 'Category', 'Tag', 'tag'), 
						array('', $label_name, $label_name, $label_name, ucfirst($label_name).'s', ucfirst($label_name), ucfirst($label_name), $label_name), 
						$label
					);
				}, (array)$labels);

				return array_merge($labels, $current_labels);
			});
		}

		register_taxonomy($taxonomy, $object_type, $taxonomy_args);
	}
}