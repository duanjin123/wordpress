<?php
class WPJAM_Cache{
	private $cache_key	= '';
	private $cache_it	= true;

	public function __construct() {
		add_filter( 'posts_pre_query',	array( &$this, 'posts_pre_query' ), 10, 2 ); 
		add_filter( 'posts_results',	array( &$this, 'posts_results' ), 10, 2 );
		add_filter( 'found_posts',		array( &$this, 'found_posts' ), 10, 2 );
		// add_action( 'clean_term_cache',	array( &$this, 'cache_flush') );
		// add_action( 'clean_post_cache',	array( &$this, 'cache_flush') );
	}

	public function cache_key(){
		$last_changed	= wp_cache_get_last_changed('posts');
		return 'wpjam_cache:'.$this->cache_key.':'.$last_changed;
	}

	public function cache_get($cache_group){
		$cache_key	= $this->cache_key();

		return wp_cache_get($cache_key , $cache_group);
	}

	public function cache_set($cache_group, $value){
		$cache_key	= $this->cache_key();

		wp_cache_set($cache_key, $value, $cache_group, DAY_IN_SECONDS);
	}

	// public function cache_flush(){
	// 	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
	// 		return;
	// 	}

	// 	wp_cache_delete('post_ids', 'wpjam_cache');
	// 	wp_cache_delete('found_posts', 'wpjam_cache');
	// }

	public function posts_pre_query($posts, $wp_query){
		if(!$wp_query->is_main_query()){	// 只缓存主循环
			$this->cache_it = false;
			return $posts;
		}

		$query_vars = $wp_query->query_vars;

		if(isset($query_vars['orderby']) && $query_vars['orderby'] == 'rand'){	// 随机排序就不能缓存了
			$this->cache_it = false;
			return $posts;
		}

		// if(isset($query_vars['paged']) && $query_vars['paged'] > 1){	// 只缓存首页
		// 	$this->cache_it = false; 
		// 	return $posts;
		// }

		// if(isset($_GET['cursor'])){
		// 	$this->cache_it = false; 
		// 	return $posts;
		// }

		$this->cache_key	= md5(maybe_serialize($query_vars));

		$post_ids	= $this->cache_get('post_ids');

		if($post_ids){
			if ((empty($query_vars['nopaging']) && !$wp_query->is_singular) && empty($query_vars['no_found_rows'])) {	// 如果需要缓存总数
				$found_posts	= $this->cache_get('found_posts');

				if($found_posts === false){
					return $posts;
				}

				$wp_query->found_posts		= $found_posts;
				$wp_query->max_num_pages	= ceil($found_posts/$query_vars['posts_per_page']);
			}

			return self::get_posts($post_ids);
		}else{
			return $posts;
		}
	}

	public function posts_results($posts, $wp_query) {
		if($this->cache_it){
			$post_ids	= $this->cache_get('post_ids');

			if($post_ids == false){
				$post_ids	= array_column($posts, 'ID');
				$this->cache_set('post_ids',$post_ids);
			}
		}

		return $posts;
	}

	// public function found_posts_query( $sql, $wp_query ) {
	// 	if ($this->cache_it && $this->cache_get('found_posts')) {
	// 		return '';
	// 	}else{
	// 		return $sql;
	// 	}
	// }

	public function found_posts( $found_posts, $wp_query ) {
		// if ($this->cache_it && $this->cache_get('found_posts')) {
		// 	return $this->cache_get('found_posts');
		// }else{
		//	 $this->cache_set('found_posts', $found_posts);
		//	 return $found_posts;
		// }

		if ($this->cache_it){
			$this->cache_set('found_posts', $found_posts);
		}
			
		return $found_posts;
	}

	public static function init(){
		return new WPJAM_Cache();
	}

	public static function get_posts($post_ids, $args=array()){
		if($post_ids){
			$post_ids 	= array_filter($post_ids);
			$post_ids 	= array_unique($post_ids);
		}

		if(empty($post_ids)) return array();

		$non_cached_ids = _get_non_cached_ids( $post_ids, 'posts' );
		if ( !empty( $non_cached_ids ) ) {

			extract(wp_parse_args($args, array(
				'post_type'			=> 'any',
				'update_term_cache'	=> true,
				'update_meta_cache'	=> true,
				'update_wpjam_cache'=> false
			)));

			global $wpdb;

			$fresh_posts = $wpdb->get_results( sprintf( "SELECT $wpdb->posts.* FROM $wpdb->posts WHERE ID IN (%s)", join( ",", $non_cached_ids ) ) );

			if($fresh_posts){
				update_post_caches( $fresh_posts, $post_type, $update_term_cache, $update_meta_cache );
				if($update_wpjam_cache) wpjam_update_post_caches($fresh_posts, $fresh_posts[0]->post_type);
			}
		}

		return array_values(wp_cache_get_multi($post_ids, 'posts'));
	}

	public static function query($args=array(), $cache_time='3600'){
		if(!isset($args['no_found_rows'])) $args['no_found_rows']	= true;

		$last_changed	= wp_cache_get_last_changed('posts');
		$key			= md5(serialize($args));
		$cache_key		= 'wpjam_query:'.$key.':'.$last_changed;

		$wpjam_query 	= get_transient($cache_key);

		if($wpjam_query === false){
			$wpjam_query = new WP_Query($args);
			set_transient($cache_key, $wpjam_query, $cache_time);
		}

		if($wpjam_query->posts){
			$post_ids = array_column($wpjam_query->posts, 'ID');
			$wpjam_query->posts	= self::get_posts($post_ids);
		} 

		return $wpjam_query;
	}

	/* HTML 片段缓存
	Usage:

	if (!WPJAM_Cache::output('unique-key')) {
		functions_that_do_stuff_live();
		these_should_echo();
		WPJAM_Cache::store(3600);
	}
	*/
	public static function output($key) {
		$output	= get_transient($key);
		if(!empty($output)) {
			echo $output;
			return true;
		} else {
			ob_start();
			return false;
		}
	}

	public static function store($key, $cache_time='600') {
		$output = ob_get_flush();
		set_transient($key, $output, $cache_time);
		echo $output;
	}
}

wp_cache_add_global_groups(['wpjam_list_cache']);
class WPJAM_listCache{
	private $k;

	public function __construct($key){
		$this->key	= $key;
	}

	private function get_items(&$cas_token){
		$items	= wp_cache_get_with_cas($this->key, 'wpjam_list_cache', $cas_token);

		if($items === false){
			$items	= [];
			wp_cache_add($this->key, [], 'wpjam_list_cache', DAY_IN_SECONDS);
			$items	= wp_cache_get_with_cas($this->key, 'wpjam_list_cache', $cas_token);
		}

		return $items;
	}

	private function set_items($cas_token, $items){
		return wp_cache_cas($cas_token, $this->key, $items, 'wpjam_list_cache', DAY_IN_SECONDS);
	}

	public function get_all(){
		return wp_cache_get($this->key, 'wpjam_list_cache');
		return $items?:[];
	}

	public function get($k){
		$items = $this->get_all();
		return $items[$k]??false;  
	}

	public function add($item, $k=null){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items	= $this->get_items($cas_token);

			if($k!==null){
				if(isset($items[$k])){
					return false;
				}

				$items[$k]	= $item;
			}else{
				$items[]	= $item;
			}
			
			$result	= $this->set_items($cas_token, $items);

			$retry	 -= 1;
		}while (!$result && $retry > 0);

		return $result;
	}

	public function increment($k, $offset=1){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items		= $this->get_items($cas_token);
			$items[$k]	= $items[$k]??0; 
			$items[$k]	= $items[$k]+$offset;
			
			$result	= $this->set_items($cas_token, $items);

			$retry	 -= 1;
		}while (!$result && $retry > 0);

		return $result;
	}

	public function decrement($k, $offset=1){
		return $this->increment($k, 0-$offset);
	}

	public function set($item, $k){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items		= $this->get_items($cas_token);
			$items[$k]	= $item;
			$result		= $this->set_items($cas_token, $items);
			$retry 		-= 1;
		}while(!$result && $retry > 0);

		return $result;
	}

	public function remove($k){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items	= $this->get_items($cas_token);
			if(!isset($items[$k])){
				return false;
			}
			unset($items[$k]);
			$result	= $this->set_items($cas_token, $items);
			$retry 	-= 1;
		}while(!$result && $retry > 0);

		return $result;
	}

	public function empty(){
		$cas_token		= '';
		$retry	= 10;

		do{
			$items	= $this->get_items($cas_token);
			if($items == []){
				return [];
			}
			$result	= $this->set_items($cas_token, []);
			$retry 	-= 1;
		}while(!$result && $retry > 0);

		if($result){
			return $items;
		}

		return $result;
	}
}



