<?php
function wpjam_initial_constants(){
	// 定义CDN和本地域名网址
	define('CDN_HOST',		untrailingslashit(apply_filters('wpjam_cdn_host', get_option('home'))));
	define('LOCAL_HOST',	untrailingslashit(apply_filters('wpjam_local_host', get_option('home'))));

	if(strpos('https://', LOCAL_HOST) !== false){
		define('LOCAL_HOST2',	str_replace('https://', 'http://', LOCAL_HOST));
	}else{
		define('LOCAL_HOST2',	str_replace('http://', 'https://', LOCAL_HOST));
	}

	define('CDN_NAME',	apply_filters('wpjam_cdn_name', ''));	// CDN 名称
}
add_action('wp_loaded', 'wpjam_initial_constants');

// 获取设置
function wpjam_get_setting($option, $setting_name, $configuartor=false){
	if($configuartor){	// 需要验证配置字段
		return WPJAM_SettingsSetting::parse_for_json($option, $setting_name);
	}else{
		if(is_string($option)) $option = wpjam_get_option($option);

		$value	= ($option[$setting_name])??'';

		if($value && is_string($value)){
			return  str_replace("\r\n", "\n", trim($value));
		}else{
			return $value;
		}
	}
}

// 获取选项
function wpjam_get_option($option_name, $configuartor=false){
	if($configuartor){	// 需要验证配置字段
		return WPJAM_SettingsSetting::parse_for_json($option_name);
	}else{
		if(is_multisite()){
			if(is_network_admin()){
				return get_site_option($option_name);
			}else{
				if(wp_installing()){	// 安装的时候没有缓存，会有一大堆 SQL 请求
					static $options;
					$options	= ($options)??[];
					if(isset($options[$option_name])){
						return $options[$option_name];
					}
				}

				$site_option	= get_site_option($option_name)?:[];
				$option			= get_option($option_name)?:[];
				$option			= $option + $site_option;

				// if($option === false){
				// 	$option	= get_site_option($option_name);
				// }

				if(wp_installing()){
					$options[$option_name]	= $option;
				}

				return $option;
			}
		}else{
			return get_option($option_name);
		}
	}
}

function wpjam_validate_post($post_id, $post_type='', $action=''){
	return WPJAM_PostTypeSetting::validate($post_id, $post_type, $action);
}

function wpjam_get_post_views($post_id, $type='views'){
	return WPJAM_PostTypeSetting::get_views($post_id, $type);
}

function wpjam_update_post_views($post_id, $type='views'){
	return WPJAM_PostTypeSetting::update_views($post_id, $type);
}

function wpjam_get_post($post_id, $args=[]){
	return WPJAM_PostTypeSetting::parse_for_json($post_id, $args);
}

function wpjam_get_posts($query_vars, $args=[], &$next_cursor=0){
	if(wpjam_is_assoc_array($query_vars)){
		// $wp_query = new WP_Query($query_vars);

		// if($wp_query->have_posts()){
		// 	$posts			= $wp_query->posts;
		// 	$found_posts	= $wp_query->found_posts;

		// 	$posts			= array_map(function($post) use ($args){ return wpjam_get_post($post->ID, $args); }, $wp_query->posts);
		// }

	}else{
		return WPJAM_Cache::get_posts($query_vars, $args);
	}
}

function wpjam_get_term($term, $taxonomy, $children_terms=[], $max_depth=-1, $depth=0){
	if($max_depth == -1){
		return WPJAM_TaxonomySetting::parse_for_json($term, $taxonomy);
	}else{
		$term	= WPJAM_TaxonomySetting::parse_for_json($term, $taxonomy);
		if(is_wp_error($term)){
			return $term;
		}

		$term['children'] = [];

		if($children_terms){
			if(($max_depth == 0 || $max_depth > $depth+1 ) && isset($children_terms[$term['id']])){
				foreach($children_terms[$term['id']] as $child){
					$term['children'][]	= wpjam_get_term($child, $taxonomy, $children_terms, $max_depth, $depth + 1);
				}
				unset($children_terms[$term['id']]);
			} 
		}

		return $term;
	}
}

/**
 * $max_depth = -1 means flatly display every element.
 * $max_depth = 0 means display all levels.
 * $max_depth > 0 specifies the number of display levels.
 *
 */
function wpjam_get_terms($args, $max_depth=-1){
	if(wpjam_is_assoc_array($args)){
		$taxonomy	= $args['taxonomy'];

		$parent		= 0;
		if(isset($args['parent']) && ($max_depth != -1 && $max_depth != 1)){
			$parent		= $args['parent'];
			unset($args['parent']);
		}

		if($terms = get_terms($args)){
			if($max_depth == -1){
				array_walk($terms, function(&$term) use ($taxonomy){
					$term = wpjam_get_term($term, $taxonomy); 

					if(is_wp_error($term)){
						wpjam_send_json($term);
					}
				});
			}else{
				$top_level_terms	= [];
				$children_terms		= [];

				foreach($terms as $term){
					if(empty($term->parent)){
						if($parent){
							if($term->term_id == $parent){
								$top_level_terms[] = $term;
							}
						}else{
							$top_level_terms[] = $term;
						}
					}else{
						$children_terms[$term->parent][] = $term;
					}
				}

				if($terms = $top_level_terms){
					array_walk($terms, function(&$term) use ($taxonomy, $children_terms, $max_depth){
						$term = wpjam_get_term($term, $taxonomy, $children_terms, $max_depth, 0); 

						if(is_wp_error($term)){
							wpjam_send_json($term);
						}
					});
				}
			}
		}
	}else{
		// 以后再处理
	}

	return $terms;
}

function wpjam_get_taxonomy_setting($taxonomy){
	return WPJAM_TaxonomySetting::get($taxonomy);
}

function wpjam_get_post_type_setting($post_type){
	return WPJAM_PostTypeSetting::get($post_type);
}

function wpjam_get_api_setting($json){
	return  WPJAM_APISetting::get($json);
}

function wpjam_parse_fields_setting($fields, $sub=0){
	return WPJAM_API::parse_fields_setting($fields, $sub);
}

function wpjam_parse_field_value($value, $field){
	return WPJAM_API::parse_field_value($value, $field);
}

function wpjam_get_field_post_ids($value, $field){
	return WPJAM_API::get_field_post_ids($value, $field);
}

function wpjam_parse_shortcode_attr($str,  $tagnames=null){
	return 	WPJAM_API::parse_shortcode_attr($str,  $tagnames);
}

function wpjam_human_time_diff($from, $to=0) {
	return WPJAM_API::human_time_diff($from, $to);
}

function wpjam_get_video_mp4($id_or_url){
	return WPJAM_API::get_video_mp4($id_or_url);
}

function wpjam_get_qqv_mp4($vid){
	return WPJAM_API::get_qqv_mp4($vid);
}

function wpjam_add_direct_api($json, $template){
	WPJAM_API::add_direct($json, $template);
}

function wpjam_get_direct_api($json){
	return WPJAM_API::get_direct($json);
}

// 去掉非 utf8mb4 字符
function wpjam_strip_invalid_text($str){
	return WPJAM_API::strip_invalid_text($str);
}

// 去掉控制字符
function wpjam_strip_control_characters($text){
	return WPJAM_API::strip_control_characters($text);
}

//获取纯文本
function wpjam_get_plain_text($text){
	return WPJAM_API::get_plain_text($text);
}

//获取第一段
function wpjam_get_first_p($text){
	return WPJAM_API::get_first_p($text);
}

//中文截取方式
function wpjam_mb_strimwidth($text, $start=0, $width=40){
	return WPJAM_API::mb_strimwidth($text, $start, $width);
}

// 检查非法字符
function wpjam_blacklist_check($str){
	return  WPJAM_API::blacklist_check($str);
}

// 获取当前页面 url
function wpjam_get_current_page_url(){
	return WPJAM_API::get_current_page_url();
}

function wpjam_get_parameter($parameter, $args=[]){
	return WPJAM_API::get_parameter($parameter, $args);
}

function wpjam_send_json($response, $options = JSON_UNESCAPED_UNICODE, $depth = 512){
	WPJAM_API::send_json($response, $options, $depth);
}

function wpjam_json_encode( $data, $options = JSON_UNESCAPED_UNICODE, $depth = 512){
	return WPJAM_API::json_encode($data, $options, $depth);
}

function wpjam_json_decode($json){
	return WPJAM_API::json_decode($json);
}

function wpjam_remote_request($url, $args=[], $err_args=[]){
	return WPJAM_API::http_request($url, $args, $err_args);
}

function wpjam_get_ua(){
	return WPJAM_API::get_user_agent();
}

function wpjam_get_user_agent(){
	return WPJAM_API::get_user_agent();
}

function wpjam_get_ua_data($ua=''){
	return WPJAM_API::parse_user_agent($ua);
}

function wpjam_parse_user_agent($ua=''){
	return WPJAM_API::parse_user_agent($ua);
}

function wpjam_get_ipdata($ip=''){
	return WPJAM_API::parse_ip($ip);
}

function wpjam_parse_ip($ip=''){
	return WPJAM_API::parse_ip($ip);
}

function wpjam_get_ip(){
	return WPJAM_API::get_ip();
}	

function is_wpjam_json($json=''){
	global $wpjam_json;

	$wpjam_json	= ($wpjam_json)??'';

	if($wpjam_json){
		if($json){
			return ($wpjam_json == $json);
		}else{
			return $wpjam_json;
		}
	}else{
		return false;
	}
}

function wpjam_get_json(){
	global $wpjam_json;

	return $wpjam_json;
}

function is_ipad(){
	return WPJAM_API::is_ipad();
}

function is_iphone(){
	return WPJAM_API::is_iphone();
}

function is_ios(){
	return WPJAM_API::is_ios();
}

function is_mac(){
	return is_macintosh();
}

function is_macintosh(){
	return WPJAM_API::is_macintosh();
}

function is_android(){
	return WPJAM_API::is_android();
}

// 判断当前用户操作是否在微信内置浏览器中
function is_weixin(){ 
	return WPJAM_API::is_weixin();
}

// 判断当前用户操作是否在微信小程序中
function is_weapp(){ 
	return WPJAM_API::is_weapp();
}

// WP_Query 缓存
function wpjam_query($args=[], $cache_time='600'){
	return WPJAM_Cache::query($args, $cache_time);
}

function wpjam_image_hwstring($size, $retina=false){
	$size	= wpjam_parse_size($size);
	$width	= ($retina)?intval($size['width']/2):$size['width'];
	$height	= ($retina)?intval($size['height']/2):$size['height'];
	return image_hwstring($width, $height);
}

function wpjam_parse_size($size){
	global $content_width, $_wp_additional_image_sizes;	

	if(is_array($size)){
		if(wpjam_is_assoc_array($size)){
			return $size;
		}else{
			$width	= intval($size[0]??0);
			$height	= intval($size[1]??0);
		}
	}else{
		if(strpos($size, 'x')){
			$size	= explode('x', $size);
			$width	= intval($size[0]);
			$height	= intval($size[1]);
		}elseif(is_numeric($size)){
			$width	= $size;
			$height	= 0;
		}elseif($size == 'thumb' || $size == 'thumbnail' || $size == 'post-thumbnail'){
			$width	= 
			$height = 150;
		}elseif($size == 'medium'){
			$width	= 
			$height = 300;
		}elseif($size == 'medium_large'){
			$width	= 768;
			$height	= 0;
		}elseif($size == 'large'){
			$width	= 1024;
			$height	= 0;
		}elseif(isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$size])){
			$width	= intval($_wp_additional_image_sizes[$size]['width']);
			$height	= intval($_wp_additional_image_sizes[$size]['height']);
		}else{
			$width	= 0;
			$height	= 0;
		}
	}

	return compact('width','height');
}


// 1. $img_url 简单替换一下 CDN 域名
// 2. $img_url, array('width'=>100, 'height'=>100)	// 这个为最标准版本
// 3. $img_url, 100x100
// 4. $img_url, 100
// 5. $img_url, array(100,100)
// 6. $img_url, array(100,100), $crop=1, $retina=1
// 7. $img_url, 100, 100, $crop=1, $retina=1
function wpjam_get_thumbnail(){
	$args_num	= func_num_args();
	$args		= func_get_args();

	$img_url	= $args[0];

	if(empty($img_url))	return '';

	if($args_num == 1){
		$thumb_args = [];
	}elseif($args_num == 2){
		$thumb_args = wpjam_parse_size($args[1]);
	}else{
		if(is_numeric($args[1])){
			$width	= $args[1]??0;
			$height	= $args[2]??0;
			$crop	= $args[3]??1;
			$retina	= $args[4]??1;
		}else{
			$size	= wpjam_parse_size($args[1]);
			$width	= $size['width'];
			$height	= $size['height'];
			$crop	= $args[2]??1;
			$retina	= $args[3]??1;
		}

		$thumb_args = compact('width','height','crop','retina');
	}

	return apply_filters('wpjam_thumbnail', $img_url, $thumb_args);
}

function wpjam_get_attachment_image_src($attachment_id, $size='full'){
	$img_url 	= wp_get_attachment_url($attachment_id);

	if(empty($img_url)){
		return ['', 0, 0, false];
	}

	$image_meta		= wp_get_attachment_metadata($attachment_id);
	$meta_width		= $image_meta['width']??0;
	$meta_height	= $image_meta['height']??0;

	$size		= wpjam_parse_size($size);

	if($size['width'] || $size['height']){
		$img_url	= wpjam_get_thumbnail($img_url, $size);

		$width		= min($size['width'], $meta_width);
		$height		= min($size['height'], $meta_height);

		$height		= $height?:intval($meta_height/$meta_width)*$width;

		return [$img_url, $width, $height, false];
	}else{
		$img_url	= wpjam_get_thumbnail($img_url);

		return [$img_url, $meta_width, $meta_height, false];
	}
}

function wpjam_content_images($content, $width=750, $strip_size=true){
	return preg_replace_callback('|<img.*?src=[\'"](.*?)[\'"].*?>|i',function($matches) use ($width, $strip_size){
		$img_url	= wpjam_get_thumbnail(trim($matches[1]), array('width'=>$width, 'content'=>true));

		$result		= str_replace($matches[1], $img_url, $matches[0]);

		if($strip_size){
			$result	= preg_replace('|width=[\'"](.*?)[\'"]|i', '', $result);
			$result	= preg_replace('|height=[\'"](.*?)[\'"]|i', '', $result);
		}
			
		return $result;
	}, $content);
}

// 打印
function wpjam_print_r($value){
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability)){
		echo '<pre>';
		print_r($value);
		echo '</pre>';
	}
}

function wpjam_var_dump($value){
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability)){
		echo '<pre>';
		var_dump($value);
		echo '</pre>';
	}
}


//WP Pagenavi
function wpjam_pagenavi($total=0){
	if(!$total){
		global $wp_query;
		$total = $wp_query->max_num_pages;
	}

	$big = 999999999; // need an unlikely integer
	
	$pagination = array(
		'base'		=> str_replace( $big, '%#%', get_pagenum_link( $big ) ),
		'format'	=> '',
		'total'		=> $total,
		'current'	=> max( 1, get_query_var('paged') ),
		'prev_text'	=> __('&laquo;'),
		'next_text'	=> __('&raquo;'),
		'end_size'	=> 2,
		'mid_size'	=> 2
	);

	echo '<div class="pagenavi">'.paginate_links($pagination).'</div>'; 
}


if(!function_exists('get_post_excerpt')){   
	//获取日志摘要
	function get_post_excerpt($post=null, $excerpt_length=240){
		$post = get_post($post);
		if(empty($post)) return '';

		$post_excerpt = $post->post_excerpt;
		if($post_excerpt == ''){
			$post_content   = strip_shortcodes($post->post_content);
			//$post_content = apply_filters('the_content',$post_content);
			$post_content   = wp_strip_all_tags( $post_content );
			$excerpt_length = apply_filters('excerpt_length', $excerpt_length);	 
			$excerpt_more   = apply_filters('excerpt_more', ' ' . '...');
			$post_excerpt   = wpjam_get_first_p($post_content); // 获取第一段
			if(mb_strwidth($post_excerpt) < $excerpt_length*1/3 || mb_strwidth($post_excerpt) > $excerpt_length){ // 如果第一段太短或者太长，就获取内容的前 $excerpt_length 字
				$post_excerpt = mb_strimwidth($post_content,0,$excerpt_length,$excerpt_more,'utf-8');
			}
		}else{
			$post_excerpt = wp_strip_all_tags( $post_excerpt );	
		}
		
		$post_excerpt = trim( preg_replace( "/[\n\r\t ]+/", ' ', $post_excerpt ), ' ' );

		return $post_excerpt;
	}
}

// 判断一个数组是关联数组，还是顺序数组
function wpjam_is_assoc_array(array $arr){
	if ([] === $arr) return false;
	return array_keys($arr) !== range(0, count($arr) - 1);
}

// 向关联数组指定的 Key 之前插入数据
function wpjam_array_push(&$array, $data=null, $key=false){
	$data	= (array)$data;

	$offset	= ($key===false)?false:array_search($key, array_keys($array));
	$offset	= ($offset)?$offset:false;

	if($offset){
		$array = array_merge(
			array_slice($array, 0, $offset), 
			$data, 
			array_slice($array, $offset)
		);
	}else{	// 没指定 $key 或者找不到，就直接加到末尾
		$array = array_merge($array, $data);
	}
}

function wpjam_localize_script($handle, $object_name, $l10n ){
	wp_localize_script( $handle, $object_name, array('l10n_print_after' => $object_name.' = ' . wpjam_json_encode( $l10n )) );
}


function wpjam_is_mobile_number($number){
	return preg_match('/^0{0,1}(13[0-9]|15[0-3]|15[5-9]|147|170|17[6-8]|18[0-9])[0-9]{8}$/', $number);
}

// function wpjam_is_400_number($number){
// 	return preg_match('/^400(\d{7})$/', $number);
// }

// function wpjam_is_800_number($number){
// 	return preg_match('/^800(\d{7})$/', $number);
// }

function wpjam_is_scheduled_event( $hook ) {	// 不用判断参数
	$crons = _get_cron_array();
	if (empty($crons)) return false;
	
	foreach ($crons as $timestamp => $cron) {
		if (isset($cron[$hook])) return true;
	}

	return false;
}

function wpjam_is_holiday($date=''){
	$date	= ($date)?$date:date('Y-m-d', current_time('timestamp'));
	$w		= date('w', strtotime($date));

	$is_holiday = ($w == 0 || $w == 6)?1:0;

	return apply_filters('wpjam_holiday', $is_holiday, $date);
}

function wpjam_set_cookie($key, $value, $expire){
	$expire	= ($expire < time())?$expire+time():$expire;

	$secure = ('https' === parse_url(get_option('home'), PHP_URL_SCHEME));

	setcookie($key, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure);

    if ( COOKIEPATH != SITECOOKIEPATH ){
        setcookie($key, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure);
    }
    $_COOKIE[$key] = $value;
}

function wpjam_attachment_url_to_postid($url){
	$post_id = wp_cache_get($url, 'attachment_url_to_postid');

	if($post_id === false){
		global $wpdb;

		$upload_dir	= wp_get_upload_dir();
		$path		= str_replace(parse_url($upload_dir['baseurl'], PHP_URL_PATH).'/', '', parse_url($url, PHP_URL_PATH));

		$post_id	= $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s", $path));

		wp_cache_set($url, $post_id, 'attachment_url_to_postid', DAY_IN_SECONDS);
	}

	return (int) apply_filters( 'attachment_url_to_postid', $post_id, $url );
}

function wpjam_urlencode_img_cn_name($img_url){
	return str_replace(['%3A','%2F'], [':','/'], urlencode($img_url));
}


function wpjam_is_mobile() {
	return wp_is_mobile();
}

function wpjam_basic_get_default_settings(){
	return [
		'diable_revision'				=> 1,
		'disable_autoembed'				=> 1,
		'disable_trackbacks'			=> 1,
		'disable_xml_rpc'				=> 1,
		'disable_emoji'					=> 1,
		'disable_post_embed'			=> 1,
		'disable_privacy'				=> 1,
		'disable_auto_update'			=> 1,
		'disable_widgets'				=> 1,
		
		'remove_head_links'				=> 1,
		'remove_admin_bar'				=> 1,
		'remove_dashboard_widgets'		=> 1,
		'remove_capital_P_dangit'		=> 1,
		'no_admin'						=> 0,
		'locale'						=> 1,

		'order_by_registered'			=> 1,
		'excerpt_optimization'			=> 1,
		'search_optimization'			=> 1,
		'404_optimization'				=> 1,
		'strict_user'					=> 1,
		'show_all_setting'				=> 1,

		'admin_footer_text'				=> '<span id="footer-thankyou">感谢使用<a href="https://cn.wordpress.org/" target="_blank">WordPress</a>进行创作。</span> | <a href="http://wpjam.com/" title="WordPress JAM" target="_blank">WordPress JAM</a>'
	];
}


function wpjam_api_validate_quota($json, $max_times=1000){
	if(WPJAM_API::is_over_quota($json, $max_times)){
		wpjam_send_json([
			'errcode'	=> 'exceed_quota',
			'errmsg'	=> 'API 调用次数超限'
		]);
	}	
}

function wpjam_api_validate_access_token(){
	if(!isset($_GET['access_token']) && is_super_admin()){
		return true;
	}

	$token	= wpjam_get_parameter('access_token', ['method' => 'GET', 'required'=>true]);

	if(!WPJAM_API::validate_access_token($token)){
		wpjam_send_json([
			'errcode'	=> 'invalid_access_token',
			'errmsg'	=> '非法 Access Token'
		]);
	}
}


//copy from image_constrain_size_for_editor
// function wpjam_get_dimensions($size){
// 	global $content_width, $_wp_additional_image_sizes;

// 	$width	= 0;
// 	$height	= 0;

// 	if (is_array($size)){
// 		$width	= isset($size[0])?$size[0]:0;
// 		$height	= isset($size[1])?$size[1]:0;
// 	}elseif($size == 'thumb' || $size == 'thumbnail' || $size == 'post-thumbnail'){
// 		$width	= intval(get_option('thumbnail_size_w'));
// 		$height	= intval(get_option('thumbnail_size_h'));

// 		if ( !$width && !$height ) {
// 			$width	= 128;
// 			$height	= 96;
// 		}
// 	}elseif($size == 'medium'){
// 		$width	= intval(get_option('medium_size_w'));
// 		$height	= intval(get_option('medium_size_h'));
// 	}elseif($size == 'large'){
// 		$width	= intval(get_option('large_size_w'));
// 		$height	= intval(get_option('large_size_h'));
// 		if ( intval($content_width) > 0 )
// 			$width = min(intval($content_width), $width);
// 	}elseif(isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes) && in_array($size, array_keys($_wp_additional_image_sizes))){
// 		$width	= intval( $_wp_additional_image_sizes[$size]['width'] );
// 		$height	= intval( $_wp_additional_image_sizes[$size]['height'] );
// 		if(intval($content_width) > 0) // Only in admin. Assume that theme authors know what they're doing.
// 			$width	= min(intval($content_width), $width);
// 	}else {	// $size == 'full' has no constraint
// 		//没了
// 	}

// 	return compact('width','height');
// }

