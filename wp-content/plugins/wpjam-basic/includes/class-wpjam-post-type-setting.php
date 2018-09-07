<?php
class WPJAM_PostTypeSetting extends WPJAM_Model {
	protected static $handler;
	protected static $field_post_ids_list;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_post_types', 'pt');
		}
		return static::$handler;
	}

	Public static function get_all(){
		$all	= parent::get_all();
		unset($all['post']);
		unset($all['page']); 

		return $all;
	}

	public static function get_default_setting($post_type){
		if($post_type == 'post'){
			return array(
				'label'				=> '文章',
				'menu_position'		=> 5,
				'menu_icon'			=> 'dashicons-admin-post',
				'hierarchical'		=> false,
				'supports'			=> ['title', 'editor', 'excerpt', 'thumbnail'],
				'taxonomies'		=> ['category','post_tag'],
				'capability_type'	=> 'post',
				'pt'			=> 'post'
			);
		}elseif($post_type == 'page'){
			return array(
				'label'			=> '页面',
				'menu_position'	=> 5,
				'menu_icon'		=> 'dashicons-admin-page',
				'hierarchical'	=> true,
				'supports'		=> ['title', 'editor', 'excerpt'],
				'taxonomies'	=> [],
				'pt'			=> 'page'
			);
		}
	}

	public static function parse_for_json($post_id, $args=[]){
		if(empty($post_id))	return [];

		extract(wp_parse_args($args, array(
			'thumbnail_size'		=> is_singular()?'750x0':'200x200',
			// 'content_image_width'	=> '750',
			'require_content'		=> false,
			'parsed'				=> true,
			'basic'					=> false
		)));

		global $post;

		$post	= get_post($post_id);

		if(empty($post)) return []; 

		$post_json	= [];

		$post_type	= $post->post_type;

		$post_json['id']		= (int)$post_id;
		$post_json['timestamp']	= (int)strtotime(get_gmt_from_date($post->post_date));
		$post_json['time']		= wpjam_human_time_diff($post_json['timestamp']);
		$post_json['post_type']	= $post_type;
		$post_json['status']	= $post->post_status;
		$post_json['title']		= '';
		
		$post_type_setting	= self::get($post_type);

		if($post_type_setting && $post_type_setting['supports']){
			foreach ($post_type_setting['supports'] as $support) {
				if($support == 'title'){
					$post_json['title']		= html_entity_decode(apply_filters('the_title', $post->post_title, $post_id));
				}elseif($support == 'excerpt'){
					$post_json['excerpt']	= wp_strip_all_tags(apply_filters('the_excerpt', $post->post_excerpt));
				}elseif($support == 'editor'){
					if(is_singular($post_type) || $require_content){
						// $post_json['content']	= wpjam_content_images(apply_filters('the_content', $post->post_content), $content_image_width);
						$post_json['content']	= apply_filters('the_content', $post->post_content);
					}
				}elseif($support == 'thumbnail'){
					if($post_thumbnail_id = get_post_thumbnail_id($post_id)){
						$post_json['thumbnail']	= wpjam_get_thumbnail(wp_get_attachment_url($post_thumbnail_id), $thumbnail_size);
					}else{
						$post_json['thumbnail']	= '';
					}
				}
			}	
		}

		// if(is_single()){
		// 	$post_json['related']	= self::get_related($post_id, $args);
		// }

		if($taxonomies = get_object_taxonomies($post_type)){
			foreach ($taxonomies as $taxonomy) {
				if($terms	= get_the_terms($post_id, $taxonomy)){
					array_walk($terms, function(&$term) use ($taxonomy){ $term 	= wpjam_get_term($term, $taxonomy);});
					$post_json[$taxonomy]	= $terms;
				}else{
					$post_json[$taxonomy]	= [];
				}
			}
		}

		if(is_singular($post_type)){
			self::update_views($post_id);
		}

		$post_json['views']	= self::get_views($post_id);

		if($basic) return apply_filters('wpjam_post_json', $post_json, $post_id, $post_type_setting);;

		if($post_fields = wpjam_get_post_fields($post_type)){
			foreach ($post_fields  as $field_key => $post_field) {
				$field_type		= $post_field['type']??'';

				if($field_type == 'fieldset'){
					if($sub_fields	= $post_field['fields']??[]){
						$fieldset_type	= $post_field['fieldset_type']??'single';

						if($fieldset_type == 'single'){
							foreach ($sub_fields as $sub_key => $sub_field) {
								$post_meta_value		= get_post_meta($post_id, $sub_key, true);
								$post_json[$sub_key]	= wpjam_parse_field_value($post_meta_value, $sub_field);
							}
						}else{
							$post_meta_value	= get_post_meta($post_id, $field_key, true);
							foreach ($sub_fields as $sub_key => $sub_field) {
								if(isset($post_meta_value[$sub_key])){
									$post_json[$field_key][$sub_key]	= wpjam_parse_field_value($post_meta_value[$sub_key], $sub_field);
								}
							}
						}
					}
				}else{
					$post_meta_value		= get_post_meta($post_id, $field_key, true);
					$post_json[$field_key]	= wpjam_parse_field_value($post_meta_value, $post_field);
				}

				if(!empty($post_field['data-type'])){
					$post_field['data_type']	= $post_field['data-type'];
				}

				if(!empty($post_field['data_type'])){
					if($post_field['data_type'] == 'vote'){
						$post_json['__vote_field']	= $field_key;
					}
				}
			}
		}

		return apply_filters('wpjam_post_json', $post_json, $post_id, $post_type_setting);
	}

	public static function update_field_post_ids_cache($post_id, $post_fields, $update_cache=true){
		if(!isset(static::$field_post_ids_list)) {
			static::$field_post_ids_list = [];
		}

		if(empty(static::$field_post_ids_list[$post_id])){
			$cache_post_ids	= [];

			foreach ($post_fields  as $field_key => $post_field) {
				$post_meta_value	= get_post_meta($post_id, $field_key, true);
				if($field_post_ids	= wpjam_get_field_post_ids($post_meta_value, $post_field)){
					$cache_post_ids	= array_merge_recursive($cache_post_ids, $field_post_ids);
				}
			}

			static::$field_post_ids_list[$post_id]	= $cache_post_ids;
		}

		$cache_post_ids	= static::$field_post_ids_list[$post_id];

		if($update_cache){
			foreach ($cache_post_ids as $post_type => $the_post_ids) {
				$update_term_cache	= ($post_type == 'attachment')?false:true;
				wpjam_get_posts($the_post_ids, compact('post_type', 'update_term_cache'));
			}
		}

		return $cache_post_ids;
	}

	public static function update_field_post_ids_caches($post_ids, $post_type){
		$cache_post_ids	= [];

		if($post_type_setting = self::get($post_type)){
			if($post_type_setting['supports'] && in_array('thumbnail', $post_type_setting['supports'])){
				foreach ($post_ids as $post_id) {
					$cache_post_ids['attachment'][]	= get_post_thumbnail_id($post_id);
				}
			}
		}

		if($post_fields = wpjam_get_post_fields($post_type)){
			foreach ($post_ids as $post_id) {
				if($field_post_ids	= self::update_field_post_ids_cache($post_id, $post_fields, $update_cache=false)){
					$cache_post_ids	= array_merge_recursive($cache_post_ids, $field_post_ids);
				}
			}
		}

		if($taxonomies = get_object_taxonomies($post_type)){
			foreach ($taxonomies as $taxonomy) {
				if($term_fields = wpjam_get_term_options($taxonomy)){
					$term_ids = [];
					foreach ($post_ids as $post_id) {
						if($terms	= get_the_terms($post_id, $taxonomy)){
							$term_ids = array_merge($term_ids, array_column($terms, 'term_id'));
						}
					}

					$term_ids	= array_unique(array_filter($term_ids));

					if($term_ids){
						update_termmeta_cache($term_ids);
						foreach ($term_ids as $term_id) {
							if($field_post_ids	= WPJAM_TaxonomySetting::update_field_post_ids_cache($term_id, $term_fields, $update_cache=false)){
								$cache_post_ids	= array_merge_recursive($cache_post_ids, $field_post_ids);
							}
						}
					}
				}
			}
		}

		// wpjam_print_r($cache_post_ids);
		
		foreach ($cache_post_ids as $post_type => $post_ids) {
			$update_term_cache	= ($post_type == 'attachment')?false:true;
			wpjam_get_posts($post_ids, compact('post_type', 'update_term_cache'));
		}	

		return $cache_post_ids;
	}

	public static function validate($post_id, $post_type='', $action=''){
		$the_post	= get_post($post_id);
		if(!$the_post){
			return new WP_Error('post_not_exists', '日志不存在');
		}

		if($post_type && $post_type != 'any' && $post_type != $the_post->post_type){
			return new WP_Error('post_type_error', '日志类型错误');
		
			if($action){
				$post_type_setting	= self::get($post_type);

				if(!$post_type_setting['actions'] || !in_array($action, $post_type_setting['actions'])){
					return new WP_Error('action_not_support', '操作不支持');
				}
			}
		}

		return $the_post;
	}

	public static function get_views($post_id, $type='views'){
		$views = wp_cache_get($post_id, $type);
		if($views === false){
			$views = get_post_meta($post_id, $type, true);
			if(!$views) $views = 0;
		}
		return $views;
	}

	public static function update_views($post_id, $type='views'){
		$views	= self::get_views($post_id, $type)+1;

		if(wp_using_ext_object_cache()){
			wp_cache_set($post_id, $views, $type);
			if($views%10 == 0){
				update_post_meta($post_id, $type, $views);   
			}
		}else{
			update_post_meta($post_id, $type, $views);
		}
	}

	public static function related_query($post_id=null, $number=5){
		$the_post	= get_post($post_id);

		if(empty($the_post)){
			return false;
		}

		$related_query = wp_cache_get($the_post->ID,'related_posts_query');
		if( $related_query === false) {

			$term_taxonomy_ids = [];
			if($taxonomies = get_object_taxonomies($the_post->post_type)){
				foreach ($taxonomies as $taxonomy) {
					if($terms	= get_the_terms($the_post->ID, $taxonomy)){
						$term_taxonomy_ids = array_merge($term_taxonomy_ids, array_column($terms, 'term_taxonomy_id'));
					}

					$term_taxonomy_ids	= array_unique(array_filter($term_taxonomy_ids));
				}
			}

			if($term_taxonomy_ids){
				add_filter('posts_join', function($posts_join, $wp_query){
					if(!empty($wp_query->query_vars['related_query'])){
						global $wpdb;
						return "INNER JOIN {$wpdb->term_relationships} AS tr ON {$wpdb->posts}.ID = tr.object_id";
					}

					return $posts_join;
				},10,2);

				add_filter('posts_where', function($posts_where, $wp_query) use($term_taxonomy_ids){
					if(!empty($wp_query->query_vars['related_query'])){
						global $wpdb;
						return $posts_where . " AND tr.term_taxonomy_id IN (".implode(",",$term_taxonomy_ids).")";
					}

					return $posts_where;
				},10,2);

				add_filter('posts_groupby',	function($posts_groupby, $wp_query){
					if(!empty($wp_query->query_vars['related_query'])){
						return " tr.object_id";
					}

					return $posts_groupby;
				},10,2);

				add_filter('posts_orderby',function($posts_orderby, $wp_query){
					if(!empty($wp_query->query_vars['related_query'])){
						global $wpdb;
						return " cnt DESC, {$wpdb->posts}.post_date_gmt DESC";
					}

					return $posts_orderby;
				},10,2);

				add_filter('posts_fields',function($posts_fields, $wp_query){
					if(!empty($wp_query->query_vars['related_query'])){
						return $posts_fields.", count(tr.object_id) as cnt";
					}	

					return $posts_fields;
				},10,2);	
			}

			$related_query = new WP_Query(array(
				'post_type'		=>apply_filters('wpjam_related_posts_post_types',array($the_post->post_type)),
				'no_found_rows'	=>true,
				'posts_per_page'=>$number,
				'related_query'	=>true,
				'related_to_id'	=>$post_id
			));

			wp_cache_set($the_post->ID, $related_query, 'related_posts_query', HOUR_IN_SECONDS*10);
		}
		return $related_query;
	}

	public static function get_related($post_id=null, $args=[]){
		extract(wp_parse_args($args, array(
			'related_number'			=> 5,
			'related_thumbnail_size'	=> ['width'=>200, 'height'=>200]
		)));

		$the_post	= get_post($post_id);

		if(empty($the_post)) return [];

		$related_json	= [];

		$related_query	= self::related_query($post_id, $related_number+1);

		if($related_query->have_posts()){
			$i = 0;

			foreach ($related_query->posts as $related_post) {

				if($related_post->ID == $post_id){
					continue;
				}

				if($i >= $related_number){
					break;
				}

				$i++;

				$post_type_setting	= self::get($related_post->post_type);

				$post_json	= [];

				$post_json['id']		= (int)$related_post->ID;
				$post_json['timestamp']	= (int)strtotime(get_gmt_from_date($related_post->post_date));
				$post_json['time']		= wpjam_human_time_diff($post_json['timestamp']);
				$post_json['title']		= '';

				if($post_type_setting && $post_type_setting['supports']){
					foreach ($post_type_setting['supports'] as $support) {
						if($support == 'title'){
							$post_json['title']		= html_entity_decode(apply_filters('the_title', $related_post->post_title, $related_post->ID));
						}elseif($support == 'excerpt'){
							$post_json['excerpt']	= wp_strip_all_tags(apply_filters('the_excerpt', $related_post->post_excerpt));
						}elseif($support == 'thumbnail'){
							if($post_thumbnail_id = get_post_thumbnail_id($related_post->ID)){
								$post_json['thumbnail']	= wpjam_get_thumbnail(wp_get_attachment_url($post_thumbnail_id), $related_thumbnail_size);
							}else{
								$post_json['thumbnail']	= '';
							}
						}
					}	
				}

				$related_json[]	= apply_filters('wpjam_related_post_json', $post_json, $related_post->ID);
			}
		}

		return $related_json;
	}

	public static function item_callback($item){
		$item['menu_icon']	= '<span class="dashicons-before '.$item['menu_icon'].'"></span>';
		return $item;
	}
}

add_filter('the_posts', function($posts, $wp_query){
	if(!is_wpjam_json() || !$posts)	return $posts;

	if(!empty($wp_query->query['related_query'])) return $posts;

	$post_type	= $posts[0]->post_type;

	if(count($posts) >= 1){
		update_post_caches($posts, $post_type, $update_post_term_cache=true, $update_post_meta_cache=true);
	}

	wpjam_update_post_caches($posts, $post_type);

	return $posts;
}, 10, 2);

function wpjam_update_post_caches($posts, $post_type){
	WPJAM_PostTypeSetting::update_field_post_ids_caches(array_column($posts, 'ID'), $post_type);
	do_action_ref_array('wpjam_posts', array($posts, $post_type));
}

add_action('save_post',function($post_id){
	wp_cache_delete($post_id,'related_posts_query');
});


// 支持默认的 post 
add_filter('default_option_wpjam_post_types', function($value){
	$value	= $value?:[];
	$value['post']	= WPJAM_PostTypeSetting::get_default_setting('post');
	$value['page']	= WPJAM_PostTypeSetting::get_default_setting('page');
	return $value;
});

add_filter('option_wpjam_post_types', function($value){
	$value['post']	= $value['post']??WPJAM_PostTypeSetting::get_default_setting('post');
	$value['page']	= $value['page']??WPJAM_PostTypeSetting::get_default_setting('page');
	return $value;
});
