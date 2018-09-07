<?php
add_action('wp_loaded', 'wpjam_cdn_ob_cache');
function wpjam_cdn_ob_cache(){			// HTML 替换，镜像 CDN 主函数
	if(CDN_NAME){	// 如果有第三方 CDN
	
		if( apply_filters('wpjam_remote_image', false, '') ){

			// 远程图片的 Rewrite 规则，第三方插件需要 flush rewrite
			add_filter('wpjam_rewrite_rules', function ($rewrite_rules){
				$rewrite_rules[CDN_NAME.'/([^/]+)/image/([^/]+)\.([^/]+)?$']	= 'index.php?p=$matches[1]&'.CDN_NAME.'_image=$matches[2]&'.CDN_NAME.'_image_type=$matches[3]';	
				return $rewrite_rules;
			});
			

			// 远程图片的 Query Var
			add_filter('query_vars', function ($query_vars) {
				$query_vars[] = CDN_NAME.'_image';
				$query_vars[] = CDN_NAME.'_image_type';
				return $query_vars;
			});

			// 远程图片加载模板
			add_action('template_redirect',		function (){
				$remote_image 		= get_query_var(CDN_NAME.'_image');
				$remote_image_type 	= get_query_var(CDN_NAME.'_image_type');

				if($remote_image && $remote_image_type){
					include(WPJAM_BASIC_PLUGIN_DIR.'template/image.php');
					exit;
				}
			}, 5);
		}

		add_filter('the_content', 'wpjam_cdn_content',1);
	}

	global $mq_blog_id;
	if(empty($mq_blog_id)){
		ob_start('wpjam_cdn_html_replace');
	}
}

function wpjam_cdn_html_replace($html){
	return apply_filters('wpjam_html_replace',$html);
}


function wpjam_cdn_content($content){
	if(wpjam_get_json()){
		return $content;
	}
	return preg_replace_callback('|<img.*?(src=[\'"](.*?)[\'"]).*?>|i', function($matches){
		$img_url 	= trim($matches[2]);

		if(empty($img_url)) return;

		if(strpos($matches[0], 'srcset=')){
			return $matches[0];
		}

		$width = $height = 0;

		if(preg_match('|<img.*?width=[\'"](.*?)[\'"].*?>|i', $matches[0], $width_matches)){
			$width = $width_matches[1];
		}

		if(preg_match('|<img.*?height=[\'"](.*?)[\'"].*?>|i', $matches[0], $height_matches)){
			$height = $height_matches[1];
		}

		$img_url_2x	= wpjam_get_thumbnail($img_url, array(
			'width'		=> $width,
			'height'	=> $height,
			'retina'	=> 2,
			'content'	=> true,
		));

		$img_url	= wpjam_get_thumbnail($img_url, array(
			'width'		=> $width,
			'height'	=> $height,
			'retina'	=> 1,
			'content'	=> true,
		));

		return str_replace($matches[1], 'src="'.$img_url.'" srcset="'.$img_url.' 1x, '.$img_url_2x.' 2x"', $matches[0]);

	}, $content);
}

// 获取远程图片
function wpjam_get_content_remote_img_url($img_url){
	$img_type = strtolower(pathinfo($img_url, PATHINFO_EXTENSION));
	if($img_type != 'gif'){
		$img_type	= ($img_type == 'png')?'png':'jpg';
		$img_url	= CDN_HOST.'/'.CDN_NAME.'/'.get_the_ID().'/image/'.md5($img_url).'.'.$img_type;
	}
	
	return $img_url;
}

// 通过 query string 强制刷新 CSS 和 JS
add_filter('script_loader_src',		'wpjam_cdn_loader_src',10,2);
add_filter('style_loader_src',		'wpjam_cdn_loader_src',10,2);
function wpjam_cdn_loader_src($src, $handle){
	if(get_option('timestamp')){
		$src = remove_query_arg(array('ver'), $src);
		$src = add_query_arg('ver',get_option('timestamp'),$src);
	}
	return $src;		
}