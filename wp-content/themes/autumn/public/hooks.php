<?php

if( wpjam_get_setting('wpjam_theme', 'foot_link') ) {
	add_filter('pre_option_link_manager_enabled', '__return_true');	/*激活友情链接后台*/
}

//禁止代码标点转换
remove_filter('the_content', 'wptexturize');
//载入JS\CSS
add_action('wp_enqueue_scripts', function () {

	if (!is_admin()) {
		
		wp_enqueue_style('style', get_stylesheet_directory_uri().'/static/css/style.css');
		wp_enqueue_style('fonts', get_stylesheet_directory_uri().'/static/fonts/iconfont.css');

		//wp_enqueue_script('jquery',			get_stylesheet_directory_uri() . '/static/js/jquery.js');
		wp_enqueue_script('autumn',	get_stylesheet_directory_uri() . '/static/js/autumn.min.js', ['jquery'], '', true);
		wp_localize_script( 'autumn', 'site_url', array("home_url"=>get_option('home'),"admin_url"=>admin_url("admin-ajax.php") ) );
		
        wp_deregister_script('jquery');
        wp_register_script('jquery', "https://cdn.bootcss.com/jquery/3.1.1/jquery.min.js", false, null);
        wp_enqueue_script('jquery');
        wp_deregister_script('jquery-migrate');
        wp_register_script('jquery-migrate', "https://cdn.bootcss.com/jquery-migrate/3.0.0/jquery-migrate.min.js", false, null);
        wp_enqueue_script('jquery-migrate');

	}
	if (is_single()) {
		wp_enqueue_style('fancybox', get_stylesheet_directory_uri().'/static/fancybox/jquery.fancybox.min.css');
		wp_enqueue_script('fancybox3', get_stylesheet_directory_uri() . '/static/fancybox/jquery.fancybox.min.js', ['jquery'], '', true);
	}
});
//fancybox3图片添加 data-fancybox
add_filter('the_content', 'fancybox');
function fancybox ($content){
    global $post;
    $pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>(.*?)<\/a>/i";
    $replacement = '<a$1href=$2$3.$4$5 data-fancybox="images" $6>$7</a>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
//文章列表 AJAX加载
function xintheme_load_more_scripts() {
	global $wp_query; 

	$paging = wpjam_get_setting('wpjam_theme', 'paging_xintheme');
	if( $paging == '3' ){
		wp_register_script( 'xintheme_loadmore', get_stylesheet_directory_uri() . '/static/js/myloadmore.js', array('jquery'), '', true );
	}
	if( $paging == '4' ){
		wp_register_script( 'xintheme_loadmore', get_stylesheet_directory_uri() . '/static/js/myloadmore-2.js', array('jquery'), '', true );
	}
	wp_localize_script( 'xintheme_loadmore', 'misha_loadmore_params', array(
		'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
		'posts' => json_encode( $wp_query->query_vars ), // everything about your loop is here
		'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
		'max_page' => $wp_query->max_num_pages
	) );
 
 	wp_enqueue_script( 'xintheme_loadmore' );
}
 
add_action( 'wp_enqueue_scripts', 'xintheme_load_more_scripts' );
//文章加载 主循环
function misha_loadmore_ajax_handler(){
 
	$args = json_decode( stripslashes( $_POST['query'] ), true );
	$args['paged'] = $_POST['page'] + 1; // we need next page to be loaded
	$args['post_status'] = 'publish';
	$args['ignore_sticky_posts'] = 1;
 
	query_posts( $args );
 
	if( have_posts() ) :
 
		while( have_posts() ): the_post();
 
			get_template_part( 'template-parts/content-list' );
 
		endwhile;
 
	endif;
	die; 
}
 
add_action('wp_ajax_loadmore', 'misha_loadmore_ajax_handler'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_loadmore', 'misha_loadmore_ajax_handler'); // wp_ajax_nopriv_{action}

//删除菜单多余css class
function wpjam_css_attributes_filter($classes) {
	return is_array($classes) ? array_intersect($classes, array('current-menu-item','current-post-ancestor','current-menu-ancestor','current-menu-parent','menu-item-has-children','menu-item')) : '';
}
add_filter('nav_menu_css_class',	'wpjam_css_attributes_filter', 100, 1);
add_filter('nav_menu_item_id',		'wpjam_css_attributes_filter', 100, 1);
add_filter('page_css_class', 		'wpjam_css_attributes_filter', 100, 1);

if( wpjam_get_setting('wpjam_theme', 'xintheme_article') ) {
	add_filter('login_redirect', function ($redirect_to, $request){
		if( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ){
			return home_url("/wp-admin/edit.php");
		}else{
			return $redirect_to;
		}

	}, 10, 3);
}

//删除wordpress默认相册样式
add_filter('gallery_style', 
	create_function('$css', 'return preg_replace("#<style type=\'text/css\'>(.*?)</style>#s", "", $css);')
);

add_filter('wpjam_post_thumbnail_uri', function($post_thumbnail_uri, $post){
	if(get_post_meta($post->ID, 'header_img', true)){
		return get_post_meta($post->ID, 'header_img', true);
	}elseif($post_thumbnail_uri){
		return $post_thumbnail_uri;
	}else{
		return wpjam_get_post_first_image($post->post_content);
	}
},10,2);

//去掉分类描述P标签
add_filter('category_description', function ($description) {
	return wp_strip_all_tags($description, true);
});

/* 评论作者链接新窗口打开 */

add_filter('get_comment_author_link', function () {
	$url	= get_comment_author_url();
	$author = get_comment_author();
	if ( empty( $url ) || 'http://' == $url ){
		return $author;
	}else{
		return "<a target='_blank' href='$url' rel='external nofollow' class='url'>$author</a>";
	}
});

//搜索结果排除所有页面
add_filter('pre_get_posts', function ($query) {
	if ($query->is_search) {
		$query->set('post_type', 'post');
	}
	return $query;
});

/* 搜索关键词为空 */
add_filter( 'request', function ( $query_variables ) {
	if (isset($_GET['s']) && !is_admin()) {
		if (empty($_GET['s']) || ctype_space($_GET['s'])) {
			wp_redirect( home_url() );
			exit;
		}
	}
	return $query_variables;
} );

//禁止头部加载s.w.org
add_filter( 'wp_resource_hints', function ( $hints, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		return array_diff( wp_dependencies_unique_hosts(), $hints );
	}
	return $hints;
}, 10, 2 );


//给文章图片自动添加alt和title信息
add_filter('the_content', function ($content) {
	global $post;
	$pattern		= "/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
	$replacement	= '<a$1href=$2$3.$4$5 alt="'.$post->post_title.'" title="'.$post->post_title.'"$6>';
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
});

//文章自动nofollow
add_filter( 'the_content', function ( $content ) {
	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>";
	if(preg_match_all("/$regexp/siU", $content, $matches, PREG_SET_ORDER)) {
		if( !empty($matches) ) {
   
			$srcUrl = get_option('siteurl');
			for ($i=0; $i < count($matches); $i++)
			{
				$tag = $matches[$i][0];
				$tag2 = $matches[$i][0];
				$url = $matches[$i][0];
   
				$noFollow = '';
				$pattern = '/target\s*=\s*"\s*_blank\s*"/';
				preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
				if( count($match) < 1 )
					$noFollow .= ' target="_blank" ';
   
				$pattern = '/rel\s*=\s*"\s*[n|d]ofollow\s*"/';
				preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
				if( count($match) < 1 )
					$noFollow .= ' rel="nofollow" ';
   
				$pos = strpos($url,$srcUrl);
				if ($pos === false) {
					$tag = rtrim ($tag,'>');
					$tag .= $noFollow.'>';
					$content = str_replace($tag2,$tag,$content);
				}
			}
		}
	}
	$content = str_replace(']]>', ']]>', $content);
	return $content;
});


//修复 WordPress 找回密码提示“抱歉，该key似乎无效”
add_filter('retrieve_password_message', function ( $message, $key ) {
	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by('email', trim($_POST['user_login']));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_user_by('login', $login);
	}
	
	$user_login = $user_data->user_login;
	$msg	= __('有人要求重设如下帐号的密码：'). "\r\n\r\n";
	$msg	.= network_site_url() . "\r\n\r\n";
	$msg	.= sprintf(__('用户名：%s'), $user_login) . "\r\n\r\n";
	$msg	.= __('若这不是您本人要求的，请忽略本邮件，一切如常。') . "\r\n\r\n";
	$msg	.= __('要重置您的密码，请打开下面的链接：'). "\r\n\r\n";
	$msg	.= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') ;

	return $msg;
}, null, 2);


//禁止FEED
if( wpjam_get_setting('wpjam_theme', 'xintheme_feed') ) {
	function wpjam_disable_feed() {
		wp_die(__('<h1>Feed已经关闭, 请访问网站<a href="'.get_bloginfo('url').'">首页</a>!</h1>'));
	}

	add_action('do_feed',		'wpjam_disable_feed', 1);
	add_action('do_feed_rdf',	'wpjam_disable_feed', 1);
	add_action('do_feed_rss',	'wpjam_disable_feed', 1);
	add_action('do_feed_rss2',	'wpjam_disable_feed', 1);
	add_action('do_feed_atom',	'wpjam_disable_feed', 1);
}

//使用v2ex镜像avatar头像
if( wpjam_get_setting('wpjam_theme', 'xintheme_v2ex') ) {
	add_filter( 'get_avatar', function ($avatar) {
		return str_replace(['cn.gravatar.com/avatar', 'secure.gravatar.com/avatar', '0.gravatar.com/avatar', '1.gravatar.com/avatar', '2.gravatar.com/avatar'], 'cdn.v2ex.com/gravatar', $avatar);
	}, 10, 3 );
}

if(!function_exists('the_views')){
	add_action('wp_head', function (){
		if (is_singular()) {global $post;
			if($post_ID = $post->ID){
				$post_views = (int)get_post_meta($post_ID, 'views', true);
				if(!update_post_meta($post_ID, 'views', ($post_views+1))){
					add_post_meta($post_ID, 'views', 1, true);
				}
			}
		}
	});  
}
//点赞
function dotGood(){  
    global $wpdb, $post;  
    $id = $_POST["um_id"];  
    if ($_POST["um_action"] == 'topTop') {  
        $specs_raters = get_post_meta($id, 'dotGood', true);  
        $expire = time() + 99999999;  
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost  
        setcookie('dotGood_' . $id, $id, $expire, '/', $domain, false);  
        if (!$specs_raters || !is_numeric($specs_raters)) update_post_meta($id, 'dotGood', 1);  
        else update_post_meta($id, 'dotGood', ($specs_raters + 1));  
        echo get_post_meta($id, 'dotGood', true);  
    }  
    die;  
}  
add_action('wp_ajax_nopriv_dotGood', 'dotGood');  
add_action('wp_ajax_dotGood', 'dotGood');
//七牛缩略图
function xintheme_wpjam_get_post_thumbnail($post=null, $size='thumbnail', $crop=1, $class="wp-post-image"){
	if($post_thumbnail_src = wpjam_get_post_thumbnail_src($post, $size, $crop)){
		$post_thumbnail_src_2x = wpjam_get_post_thumbnail_src($post, $size, $crop, 2);
		return ''.$post_thumbnail_src.'';
	}
	
	return '';
}
