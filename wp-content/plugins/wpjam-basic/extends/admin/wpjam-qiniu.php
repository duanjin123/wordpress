<?php
add_filter('wpjam_pages', 'wpjam_qiniutek_admin_pages');
add_filter('wpjam_network_pages', 'wpjam_qiniutek_admin_pages');
function wpjam_qiniutek_admin_pages($wpjam_pages){
	$qiniu_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNS4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IuWbvuWxgl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjE1My44OHB4IiBoZWlnaHQ9IjEwMy4zcHgiIHZpZXdCb3g9IjAgMCAxNTMuODggMTAzLjMiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDE1My44OCAxMDMuMyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMTUzLjY4NCwwLjc5MWMtMC4yNjYtMC40OTctMC44My0wLjk2My0xLjg1LTAuNzI4Yy0xLjk4NCwwLjQ2NS0yNS4xMzksMzkuMTY5LTc0Ljg4NywzOC4wOThoLTAuMDE2DQoJYy05LjQ1MiwwLjIwMy0xOC42MjgtMS4wMy0yNi4xODgtMy4xNTZsLTQuMzI3LTEzLjk4YzAsMC0wLjgwMS0zLjQzOC00LjExNS01LjE5NmMtMi4yOTMtMS4yMDctMy42MzEtMC44MjEtMy45MTEtMC40ODQNCgljLTAuMjUyLDAuMzE2LTAuMjA0LDAuNjUzLTAuMjA0LDAuNjUzbDIuMDUzLDE1LjI2NkMxNC44OTEsMjAuNCwzLjQ3NCwwLjQwMywyLjA0MiwwLjA2M2MtMS4wMTUtMC4yMzUtMS41NzgsMC4yMy0xLjg0NSwwLjcyOA0KCWMtMC40MjcsMC44LTAuMDIxLDEuOTI5LTAuMDIxLDEuOTI5YzcuMTUyLDIxLjMxMSwyMi41ODcsMzguMTI2LDQyLjU2Nyw0Ny4wOWw1LjUwOSwzNi45MzENCgljMC4zNzQsMTAuNTMzLDcuNDE2LDE2LjU1OSwxNi41NjksMTYuNTU5aDI3LjM5YzkuMTUzLDAsMTYuMDA4LTYuNTg4LDE2LjU3NS0xNi41NTlsNS4wMTktMzAuNTM3YzAsMCwwLjA4NS0wLjQyNi0wLjE2Ni0wLjYwNA0KCWMtMC4zMTItMC4xOTEtMi42OTgtMC4yNjQtNy41MjgsMy4zMTRjLTQuODMsMy41ODItNi40NjMsOC43NTYtNi40NjMsOC43NTZzLTUuMjE5LDEyLjY5OS02LjU5MSwxOC4xMjUNCgljLTEuNDQ0LDUuNzEzLTcuODUsNS4yMDMtNy44NSw1LjIwM3MtOS43ODksMC0xNC42ODEsMGMtNC44OTUsMC01LjM5Ni00LjM5NS01LjM5Ni00LjM5NWwtOS4xNi0zMi4yMTUNCgljNi42NzEsMS42ODYsMTMuNjg0LDIuNTY4LDIwLjk2MiwyLjU0M2gwLjAxNmMzNS45NzUsMC4xNDEsNjUuODk3LTIxLjg4Myw3Ni43NTYtNTQuMjEyQzE1My43MDMsMi43MTksMTU0LjExLDEuNTksMTUzLjY4NCwwLjc5MXoiDQoJLz4NCjwvc3ZnPg0K';

	$subs	= [];
	$subs['wpjam-qiniutek']	= [
		'menu_title'	=> '设置',
		'function'		=>'option',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-qiniutek.php',
	];

	if( wpjam_qiniu_get_setting('bucket') && wpjam_qiniu_get_setting('access') && wpjam_qiniu_get_setting('secret') ){
		$subs['wpjam-qiniutek-update']	= [
			'menu_title'	=> '文件更新',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-qiniutek-update.php',
		];

		$subs['wpjam-qiniutek-robots']	= [
			'menu_title'	=> 'Robots.txt',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-qiniutek-robots.php',
		
		];
	}
	
	$wpjam_pages['wpjam-qiniutek']	= [
		'menu_title'	=> '七牛云存储',		
		'icon'			=> $qiniu_icon,
		'function'		=> 'option',
		'subs'			=> $subs,
		'position'		=> '110.3'
	];

	return $wpjam_pages;
}

function wpjam_qiniutek_delete_file($file){
	global $qiniutek_client;

	if(!$qiniutek_client){
		$qiniutek_client = wpjam_get_qiniutek_client();
	}

	$wpjam_qiniutek = get_option( 'wpjam-qiniutek' );
	$qiniutek_bucket = $wpjam_qiniutek['bucket'];

	$file_array = parse_url($file);
	$key = str_replace($file_array['scheme'].'://'.$file_array['host'].'/', '', $file);
	$err = Qiniu_RS_Delete($qiniutek_client, $qiniutek_bucket, $key);

	if($err !== null){
		return new WP_Error($err->Code, $err->Err);
	}else{
		return true;
	}
}

function wpjam_qiniutek_put_file($key, $file){
	global $qiniutek_client;

	if(!$qiniutek_client){
		$qiniutek_client = wpjam_get_qiniutek_client();
	}

	$wpjam_qiniutek = get_option( 'wpjam-qiniutek' );
	$qiniutek_bucket = $wpjam_qiniutek['bucket'];

	$putPolicy = new Qiniu_RS_PutPolicy($qiniutek_bucket);
	$upToken = $putPolicy->Token(null);

	if(!function_exists('Qiniu_Put')){
		require_once(WPJAM_BASIC_PLUGIN_DIR."/extends/qiniu/sdk/io.php");
	}

	list($ret, $err) = Qiniu_PutFile($upToken, $key, $file);
	
	if($err !== null){
		return new WP_Error($err->Code, $err->Err);
	}else{
		return true;
	}
}

function wpjam_qiniutek_put($key, $str){
	global $qiniutek_client;

	if(!$qiniutek_client){
		$qiniutek_client = wpjam_get_qiniutek_client();
	}

	$wpjam_qiniutek = get_option( 'wpjam-qiniutek' );
	$qiniutek_bucket = $wpjam_qiniutek['bucket'];

	$putPolicy = new Qiniu_RS_PutPolicy($qiniutek_bucket);
	$upToken = $putPolicy->Token(null);

	if(!function_exists('Qiniu_Put')){
		require_once(WPJAM_BASIC_PLUGIN_DIR."/extends/qiniu/sdk/io.php");
	}

	list($ret, $err) = Qiniu_Put($upToken, $key, $str, null);

	if($err !== null){
		return new WP_Error($err->Code, $err->Err);
	}else{
		return true;
	}
}

function wpjam_get_qiniutek_client(){

	$wpjam_qiniutek = get_option( 'wpjam-qiniutek' );
	if(!class_exists('Qiniu_MacHttpClient')){
		require_once(WPJAM_BASIC_PLUGIN_DIR."/extends/qiniu/sdk/rs.php");
	}	
	Qiniu_SetKeys($wpjam_qiniutek['access'], $wpjam_qiniutek['secret']);
	$qiniutek_client = new Qiniu_MacHttpClient(null);

	return $qiniutek_client;
}

add_action('wp_loaded', function (){	
	if(CDN_NAME == '')
		return;

	add_filter('pre_option_thumbnail_size_w',	'__return_zero');
	add_filter('pre_option_thumbnail_size_h',	'__return_zero');
	add_filter('pre_option_medium_size_w',		'__return_zero');
	add_filter('pre_option_medium_size_h',		'__return_zero');
	add_filter('pre_option_large_size_w',		'__return_zero');
	add_filter('pre_option_large_size_h',		'__return_zero');

	add_filter('intermediate_image_sizes_advanced', function($sizes){
		if(isset($sizes['full'])){
			return ['full'=>$sizes['full']];
		}else{
			return [];
		}
	});

	add_filter('image_size_names_choose', function($sizes){
		if(isset($sizes['full'])){
			return ['full'=>$sizes['full']];
		}else{
			return [];
		}
	});

	add_filter('upload_dir', function($uploads){
		$uploads['url']		= wpjam_get_thumbnail($uploads['url']);
		$uploads['baseurl']	= wpjam_get_thumbnail($uploads['baseurl']);

		return $uploads;
	});

	add_filter('wp_calculate_image_srcset_meta', '__return_empty_array');

	// add_filter('image_downsize', '__return_true');
	add_filter('wp_get_attachment_image_src', function($image, $attachment_id, $size, $icon){
		return  wpjam_get_attachment_image_src($attachment_id, $size);
	}, 10 ,4);

	add_filter('wp_prepare_attachment_for_js', function($response, $attachment, $meta){
		if(isset($response['sizes'])){
			$orientation	= $response['sizes']['full']['orientation'];

			foreach (array('thumbnail', 'medium', 'medium_large', 'large') as $s) {
				$image_src = wpjam_get_attachment_image_src($attachment->ID, $s);

				$response['sizes'][$s]	= array(
					'url'			=> $image_src[0],
					'width'			=> $image_src[1],
					'height'		=> $image_src[2],
					'orientation'	=> $orientation
				);
			}
		}

		return $response;
	}, 10, 3);
});