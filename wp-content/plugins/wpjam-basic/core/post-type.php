<?php
// 注册自定义日志类型
add_action('init', 'wpjam_post_type_init', 11);
function wpjam_post_type_init(){
	// 获取要注册自定义日志类型参数
	$wpjam_post_types = apply_filters('wpjam_post_types', WPJAM_PostTypeSetting::get_all());

	if(!$wpjam_post_types) return;

	global $wp_post_types;

	foreach ($wpjam_post_types as $post_type => $post_type_args) {
		$post_type_args	= wp_parse_args($post_type_args, array(
			'label'					=> '',
			'public'				=> false,
			'exclude_from_search'	=> false,
			'show_ui'				=> true,
			'has_archive'			=> false,
			'rewrite'				=> false,
			'hierarchical'			=> false,
			'query_var'				=> true,
			'permastruct'			=> false,
			// 'capability_type'		=> $post_type,
			// 'map_meta_cap'			=> true,
			'supports'				=> array('title'),
			'taxonomies'			=> array(),
			'thumbnail_size'		=> '',
		));

		if(empty($post_type_args['taxonomies'])){
			unset($post_type_args['taxonomies']);
		}

		if($post_type_args['hierarchical']){
			$post_type_args['supports'][]	= 'page-attributes';
		}

		$post_type_rewrite = ($post_type_args['rewrite'])??(isset($post_type_args['permastruct'])?true:false);

		if (is_array($post_type_rewrite)) {
			$post_type_args['rewrite']	= wp_parse_args($post_type_rewrite, array('slug'=>$post_type, 'with_front'=>false, 'pages'=>true, 'feeds'=>false) );
		}else{
			$post_type_args['rewrite']	= array('slug'=>$post_type, 'with_front'=>false, 'pages'=>true, 'feeds'=>false);
		}

		$label	= ($post_type_args['label'])??'';
		$labels	= ($post_type_args['labels'])??[];

		if(is_admin() && $label) {
			$label_name		= $label;
			$current_labels	= $labels;
			add_filter("post_type_labels_".$post_type, function($labels) use($label_name, $current_labels){
				$labels = array_map(function($label) use ($label_name){
					if($label == $label_name) return $label;

					return str_replace(
						array('文章', 'post', 'Post', '撰写新', '写新', '写'), 
						array($label_name, $label_name, ucfirst($label_name), '新增', '新增', '新增'), 
						$label
					);
				}, (array)$labels);

				return array_merge($labels, $current_labels);
			});
		}

		register_post_type($post_type, $post_type_args);

		if($permastruct = $post_type_args['permastruct']){
			if(strpos($permastruct, "%post_id%") || strpos($permastruct, "%{$post_type}_id%")){
				$wp_post_type	= $wp_post_types[$post_type];

				$permastruct_args			= $wp_post_type->rewrite;
				$permastruct_args['feed']	= $permastruct_args['feeds'];

				$permastruct	= str_replace('%post_id%', '%'.$post_type.'_id%', $permastruct); 

				add_rewrite_tag('%'.$post_type.'_id%', '([0-9]+)', "post_type=$post_type&p=" );

				add_permastruct($wp_post_type->name, $permastruct, $permastruct_args);
			}
		}

		if(is_admin() && ($thumbnail_size = $post_type_args['thumbnail_size'])){	// 在后台特色图片下面显示最佳图片大小
			add_filter('admin_post_thumbnail_html', function($content) use($thumbnail_size){
				return $content.'<p>大小：'.$thumbnail_size.'</p>';
			});
		}
	}
}

// 设置自定义日志的链接
add_filter('post_type_link', function($post_link, $post){
	$post_type	= $post->post_type;
	$post_id	= $post->ID;

	global $wp_post_types;

	if(empty($wp_post_types[$post_type]->permastruct)){
		return $post_link;
	}

	$post_link	= str_replace( '%'.$post_type.'_id%', $post_id, $post_link );

	$taxonomies = get_taxonomies(array('object_type'=>array($post_type)), 'objects');

	if(!$taxonomies){
		return $post_link;
	}

	foreach ($taxonomies as $taxonomy=>$taxonomy_object) {
		if($taxonomy_rewrite = $taxonomy_object->rewrite){
			$terms = get_the_terms( $post_id, $taxonomy );
			if($terms){
				$term = current($terms);
				$post_link	= str_replace( '%'.$taxonomy_rewrite['slug'].'%', $term->slug, $post_link );
			}else{
				$post_link	= str_replace( '%'.$taxonomy_rewrite['slug'].'%', $taxonomy, $post_link );
			}
		}
	}

	return $post_link;
}, 1, 2);


