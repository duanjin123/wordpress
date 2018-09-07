<?php
class WPJAM_TaxonomySetting extends WPJAM_Model {
	private static $handler;
	private static $field_post_ids_list;

	public static function get_handler(){
		global $wpdb;
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_taxonomies', 'tax');
		}
		return static::$handler;
	}

	public static function get_all(){
		$taxonomies = parent::get_all();

		if(!$taxonomies) return array();

		return array_map(function($args){ return array('object_type'=>$args['object_type'], 'args'=>$args); }, $taxonomies);
	}
	
	public static function parse_for_json($term, $taxonomy){
		$term	= get_term($term);

		if(is_wp_error($term) || empty($term)){
			return new WP_Error('illegal_'.$taxonomy.'_id', '非法 '.$taxonomy.'_id');
		}

		$term_json	= array();
		$term_id	= $term->term_id;

		$term_json['id']			= $term_id;
		$term_json['name']			= $term->name;
		// $term_json['slug']			= $term->slug;
		$term_json['count']			= (int)$term->count;
		$term_json['description']	= $term->description;
		$term_json['parent']		= $term->parent;
		
		if($term_fields = wpjam_get_term_options($taxonomy)){
			foreach ($term_fields as $term_key => $term_field) {
				$term_value				= get_term_meta($term_id, $term_key, true);
				$term_json[$term_key]	= wpjam_parse_field_value($term_value, $term_field);
			}
		}
		
		return apply_filters('wpjam_term_json', $term_json, $term_id, $taxonomy);
	}

	public static function update_field_post_ids_cache($term_id, $term_fields, $update_cache=true){
		if(!isset(static::$field_post_ids_list)) {
			static::$field_post_ids_list = array();
		}

		if(empty(static::$field_post_ids_list[$term_id])){

			$cache_post_ids	= array();

			foreach ($term_fields  as $field_key => $term_field) {
				$term_value	= get_term_meta($term_id, $field_key, true);
				if($field_post_ids	= wpjam_get_field_post_ids($term_value, $term_field)){
					$cache_post_ids	= array_merge_recursive($cache_post_ids, $field_post_ids);
				}
			}
			
			static::$field_post_ids_list[$term_id]	= $cache_post_ids;
		}

		$cache_post_ids	= static::$field_post_ids_list[$term_id];

		if($update_cache){
			foreach ($cache_post_ids as $post_type => $the_post_ids) {
				$update_term_cache	= ($post_type == 'attachment')?false:true;
				wpjam_get_posts($the_post_ids, compact('post_type', 'update_term_cache'));
			}
		}

		return $cache_post_ids;
	}
}

add_filter('get_terms', function($terms, $taxonomies, $query_vars, $term_query){
	global $wp;

	if(!is_wpjam_json() || !$terms)	return $terms;

	if(isset($wp->query_vars['template_type']) && $wp->query_vars['template_type'] != 'taxonomy')	return $terms;

	$field_post_ids	= array();
	$term_ids		= array_column($terms, 'term_id');	
	
	foreach ($taxonomies as $taxonomy) {
		if($term_fields = wpjam_get_term_options($taxonomy)){
			foreach ($term_ids as $term_id) {
				$field_post_ids	= array_merge_recursive($field_post_ids, WPJAM_TaxonomySetting::update_field_post_ids_cache($term_id, $term_fields, $update_cache=false));
			}
		}
	}

	foreach ($field_post_ids as $post_type => $post_ids) {
		$update_term_cache	= ($post_type == 'attachment')?false:true;
		wpjam_get_posts($post_ids, compact('post_type', 'update_term_cache'));
	}
	
	return $terms;
},10,4);

