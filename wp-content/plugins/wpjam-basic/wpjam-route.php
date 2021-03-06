<?php
add_filter('query_vars', function ($query_vars) {
	$query_vars[]	= 'module';
	$query_vars[]	= 'action';

	// 如果 $custom_taxonomy_key.'_id' 不用在 rewrite ，下面代码无效
	// if($custom_taxonomies = get_taxonomies(array('public' => true, '_builtin' => false))){
	// 	foreach ($custom_taxonomies as $custom_taxonomy_key => $custom_taxonomy) {
	// 		$query_vars[]	= $custom_taxonomy_key.'_id';
	// 	}
	// }

	return $query_vars;
});

add_action('generate_rewrite_rules', function ($wp_rewrite){
	$wpjam_rewrite_rules = [];
	
	//重新加回全站的 feed permalink
	$wpjam_rewrite_rules['feed/(feed|rdf|rss|rss2|atom)/?$']	= 'index.php?&feed=$matches[1]';
	$wpjam_rewrite_rules['(feed|rdf|rss|rss2|atom)/?$']			= 'index.php?&feed=$matches[1]';

	$wpjam_rewrite_rules['api/([^/]+).json?$']					= 'index.php?module=json&action=$matches[1]';

	$wp_rewrite->rules		= array_merge(apply_filters('wpjam_rewrite_rules', $wpjam_rewrite_rules), $wp_rewrite->rules);

	// wpjam_print_r($wp_rewrite->rules);

},11);

add_filter('request', function ($query_vars){
	$module = ($query_vars['module'])??'';
	$action = ($query_vars['action'])??'';

	if($module == 'json' && strpos($action, 'mag.') === 0){
		return $query_vars;
	}

	if(!empty($_REQUEST['tag_id'])){
		$query_vars['tag_id'] = $_REQUEST['tag_id'];
	}

	if($custom_taxonomies = get_taxonomies(array('public' => true, '_builtin' => false))){
		$tax_query = array();

		foreach ($custom_taxonomies as $custom_taxonomy) {

			$current_term_id = ($query_vars[$custom_taxonomy.'_id'])??(($_REQUEST[$custom_taxonomy.'_id'])??'');

			if($current_term_id){
				if($term = get_term($current_term_id, $custom_taxonomy)){	// wp 本身的 cache 有问题， WP_Term::get_instance
					$tax_query[$custom_taxonomy]	= array(
						'taxonomy'	=> $custom_taxonomy,
						'terms'		=> array( $current_term_id ),
						'field'		=> 'id',
					);
				}else{
					wp_die('非法'.$custom_taxonomy.'_id');
				}
			}
		}

		if($tax_query){
			$query_vars['tax_query']				= array_values($tax_query);
			$query_vars['tax_query']['relation']	= 'AND';
		}
	}
	
	return $query_vars;
});

//设置 headers
add_action('send_headers', function ($wp){
	$module = ($wp->query_vars['module'])??'';
	$action = ($wp->query_vars['action'])??'';

	if($module == 'json'){
		if(!isset($_GET['debug'])){ 
			$content_type = isset($_GET['callback'])?'application/javascript':'application/json';

			header('Content-Type: ' .  $content_type.'; charset=' . get_option('blog_charset'));
			header('X-Content-Type-Options: nosniff');

			$origin = get_http_origin();

			if ( $origin ) {
				// Requests from file:// and data: URLs send "Origin: null"
				if ( 'null' !== $origin ) {
					$origin = esc_url_raw( $origin );
				}
				header( 'Access-Control-Allow-Origin: ' . $origin );
				header( 'Access-Control-Allow-Methods: GET, POST' );
				header( 'Access-Control-Allow-Credentials: true' );
				header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
				header( 'Vary: Origin' );
			}
			
			if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ){
				exit;
			}
		}

		if(strpos($action, 'mag.') === 0){
			global $wpjam_json;
			include(WPJAM_BASIC_PLUGIN_DIR.'template/api/route.php');
		}
	}

	do_action('wpjam_module', $module, $action);

	if($module){
		remove_action('template_redirect', 'redirect_canonical');
	}

	// 缓存使得这个 filter 无效
	// add_filter('posts_where', function ($where, $wp_query){
	// 	if($wp_query->is_main_query()){

	// 		global $wpdb;
	// 		$first	= intval(($_GET['first'])??(($_GET['first_time'])??''));
	// 		$cursor	= intval(($_GET['cursor'])??(($_GET['last_time'])??''));

	// 		if(!$first && !$cursor){    //不指定first和last，默认返回最新的数据，就是客户端第一次加载 
	// 		    //do nothing
	// 		}elseif($first){          //指定first，获取大于first的最新数据，就是客户端下拉刷新
	// 		    $where .= " AND ({$wpdb->posts}.post_date > '".get_date_from_gmt(date('Y-m-d H:i:s',$first))."')";
	// 		}elseif($cursor){           //指定cursor，获取小于cursor的更多数据，就是加载更多
	// 		    $where .= " AND ({$wpdb->posts}.post_date < '".get_date_from_gmt(date('Y-m-d H:i:s',$cursor))."')";
	// 		}
	// 	}

	// 	return $where;
	// }, 10, 2);

	add_filter('template_include', function ($template) use ($module, $action){
		if($module){
			$action = ($action == 'new' || $action == 'add')?'edit':$action;

			if($action){
				$wpjam_template = STYLESHEETPATH.'/template/'.$module.'/'.$action.'.php';
			}else{
				$wpjam_template = STYLESHEETPATH.'/template/'.$module.'/index.php';
			}

			$wpjam_template		= apply_filters( 'wpjam_template', $wpjam_template, $module, $action );

			if(is_file($wpjam_template)){
				return $wpjam_template;
			}else{
				wp_die('路由错误！');
			}
		}

		return $template;
	});
});

function wpjam_api_set_response(&$response){
	global $wp_query;

	if($wp_query->have_posts()){

		if(isset($_GET['s'])){
			$response['total_pages']	= (int)$wp_query->max_num_pages;
			$response['current_page']	= (int)(isset($_GET['paged'])?$_GET['paged']:1);
		}else{
			$response['has_more']	= ($wp_query->max_num_pages>1)?1:0;

			$first_post_time = (int)strtotime(get_gmt_from_date($wp_query->posts[0]->post_date)); 
			$post = end($wp_query->posts);
			$last_post_time = (int)strtotime(get_gmt_from_date($post->post_date));

			$first_time	= isset($_GET['first_time'])?(int)$_GET['first_time']:'';
			$last_time	= isset($_GET['last_time'])?(int)$_GET['last_time']:'';

			if(!$first_time && !$last_time){								//第一次加载，需要返回first_time和最后last_time
				$response['first_time']	= $first_post_time;
				$response['last_time'] 	= $last_post_time;
			}elseif($first_time && $wp_query->max_num_pages > 1){			//下拉刷新，数据超过一页：需要返回fist_time和last_time，客户端需要把所有数据清理
				$response['first_time']	= $first_post_time;
				$response['last_time'] 	= $last_post_time;
			}elseif($first_time && $wp_query->max_num_pages < 2){			//下拉刷新，数据不超过一页：需要返回first_time，不需要last_time
				$response['first_time']	= $first_post_time;
			}elseif($last_time){											//加载更多：不需要first_time，需要返回last_time
				$response['last_time']	= $last_post_time;
			}

			$response['total_pages']	= (int)$wp_query->max_num_pages;
			$response['current_page']	= (int)(isset($_GET['paged'])?$_GET['paged']:1);
		}
	}
}

function wpjam_api_signon(){
	$user = ($_SERVER['PHP_AUTH_USER'])??'';
	$pass = ($_SERVER['PHP_AUTH_PW'])??'';

	if(empty($user) || empty($pass))	return false;

	$wp_user = wp_signon(array(
		'user_login'	=> $user,
		'user_password'	=> $pass,
	));

	if(is_wp_error($wp_user))	return false;

	if(current_user_can('mamage_options'))	return true;
	
	return false;
}

function is_module($module='', $action=''){
	$current_module	= get_query_var('module');
	$current_action	= get_query_var('action');

	// 没设置 module
	if(!$current_module)	return false;
	
	// 不用确定当前是什么 module
	if(!$module)	return true;
	
	if($module != $current_module)	return false;

	if(!$action)	return true;

	if($action != $current_action)	return false;
	
	return true;
}
