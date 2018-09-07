<?php
/*
Plugin Name: 七牛镜像云存储
Description: 使用七牛云存储实现 WordPress 博客静态文件 CDN 加速！
Plugin URI: http://blog.wpjam.com/project/wpjam-qiniu/
Author URI: http://blog.wpjam.com/
Version: 1.3.3
*/
// add_action('wpjam_basic_set_default_settings', function(){
// 	if(!get_option('wpjam-qiniutek')){
// 		update_blog_option($blog_id, 'wpjam-qiniutek', array());
// 	}
// });

add_filter('option_wpjam-qiniutek', function($value){
	if(is_multisite()){
		return wp_parse_args($value, get_site_option('wpjam-qiniutek')?:[]);
	}else{
		return $value;
	}
});


function wpjam_qiniu_get_setting($setting_name){
	return wpjam_get_setting('wpjam-qiniutek', $setting_name);
}

//定义在七牛绑定的域名。
add_filter('wpjam_cdn_host', function ($cdn_host){
	return wpjam_qiniu_get_setting('host')?:$cdn_host;
});


add_filter('wpjam_local_host', function ($local_host){
	return wpjam_qiniu_get_setting('local')?:$local_host;
});


add_filter('wpjam_cdn_name', function ($tag){
	return 'qiniu';
});


add_filter('wpjam_html_replace', function ($html){
	if(is_admin())	return $html;

	$cdn_exts	= wpjam_qiniu_get_setting('exts');

	if(empty($cdn_exts)) return $html;

	$cdn_dirs	= str_replace('-','\-',wpjam_qiniu_get_setting('dirs'));

	if($cdn_dirs){
		$regex	=  '/'.str_replace('/','\/',LOCAL_HOST).'\/(('.$cdn_dirs.')\/[^\s\?\\\'\"\;\>\<]{1,}.('.$cdn_exts.'))([\"\\\'\s\]\?]{1})/';
		$html =  preg_replace($regex, CDN_HOST.'/$1$4', $html);

		$regex	=  '/'.str_replace('/','\/',LOCAL_HOST2).'\/(('.$cdn_dirs.')\/[^\s\?\\\'\"\;\>\<]{1,}.('.$cdn_exts.'))([\"\\\'\s\]\?]{1})/';
		$html =  preg_replace($regex, CDN_HOST.'/$1$4', $html);
	}else{
		$regex	= '/'.str_replace('/','\/',LOCAL_HOST).'\/([^\s\?\\\'\"\;\>\<]{1,}.('.$cdn_exts.'))([\"\\\'\s\]\?]{1})/';
		$html =  preg_replace($regex, CDN_HOST.'/$1$3', $html);

		$regex	= '/'.str_replace('/','\/',LOCAL_HOST2).'\/([^\s\?\\\'\"\;\>\<]{1,}.('.$cdn_exts.'))([\"\\\'\s\]\?]{1})/';
		$html =  preg_replace($regex, CDN_HOST.'/$1$3', $html);
	}
	return $html;
});


add_filter('wpjam_remote_image', function ($status, $img_url=''){
	return wpjam_qiniu_can_remote_image($img_url);
}, 10, 2);

function wpjam_qiniu_can_remote_image($img_url){
	if(get_option('permalink_structure') == false)	return false;	//	没开启固定链接
	if(wpjam_qiniu_get_setting('remote') == false)	return false;	//	没开启选线

	if($img_url){
		$exceptions	= explode("\n", wpjam_qiniu_get_setting('exceptions'));	// 后台设置不加载的远程图片

		if($exceptions){
			foreach ($exceptions as $exception) {
				if(trim($exception) && strpos($img_url, trim($exception)) !== false ){
					return false;
				}
			}
		}
	}

	return true;
}

add_filter('wpjam_default_thumbnail_uri', function ($default_thumbnail_uri){
	return wpjam_qiniu_get_setting('default');
});


add_filter('wpjam_thumbnail','wpjam_qiniu_thumbnail',10,2);
function wpjam_qiniu_thumbnail($img_url, $args){
	if(empty($img_url)) return $img_url;

	$local_hosts	= wpjam_qiniu_get_setting('locals')?:array();
	$local_hosts	= array_merge($local_hosts, [LOCAL_HOST, LOCAL_HOST2]);

	$img_url		= str_replace($local_hosts, CDN_HOST, $img_url);
	
	if(strpos($img_url, CDN_HOST) === false){
		if(isset($args['content']) && wpjam_qiniu_can_remote_image($img_url)){
			$img_url	= wpjam_get_content_remote_img_url($img_url);
		}else{
			return $img_url;
		}
	}

	if(isset($args['content'])){
		$args['width']	= $args['width']?:($_GET['width']??wpjam_qiniu_get_setting('width'));

		$img_url = wpjam_get_qiniu_watermark($img_url);
	}

	return wpjam_get_qiniu_thumbnail($img_url, $args);
}

//使用七牛缩图 API 进行裁图
function wpjam_get_qiniu_thumbnail($img_url, $args=array()){
	extract(wp_parse_args($args, array(
		'crop'		=> 1,
		'width'		=> 0,
		'height'	=> 0,
		'retina'	=> 1,
		'mode'		=> null,
		'format'	=> '',
		'interlace'	=> 0,
		'quality'	=> 0,
	)));

	if($mode === null){
		$crop	= $crop && ($width && $height);	// 只有都设置了宽度和高度才裁剪
		$mode	= $mode?:($crop?1:2);
	}
	
	$width		= intval($width)*$retina;
	$height		= intval($height)*$retina;

	$format		= $format?:(wpjam_qiniu_get_setting('webp')?'webp':'');
	$interlace	= $interlace?:(wpjam_qiniu_get_setting('interlace')?1:0);
	$quality	= $quality?:(wpjam_qiniu_get_setting('quality'));

	// if($width || $height || $format || $interlace || $quality){
	if($width || $height){
		$arg	= 'imageView2/'.$mode;

		if($width)		$arg .= '/w/'.$width;
		if($height) 	$arg .= '/h/'.$height;
		if($format)		$arg .= '/format/'.$format;
		if($interlace)	$arg .= '/interlace/'.$interlace;
		if($quality)	$arg .= '/q/'.$quality;

		if(strpos($img_url, 'imageView2')){
			$img_url	= preg_replace('/imageView2\/(.*?)#/', '', $img_url);
		}

		if(strpos($img_url, 'watermark')){
			$img_url	= $img_url.'|'.$arg;
		}else{
			$img_url	= add_query_arg( array($arg => ''), $img_url );
		}

		$img_url	= $img_url.'#';
	}

	return $img_url;
}

// 获取七牛水印
function wpjam_get_qiniu_watermark($img_url, $args=array()){
	extract(wp_parse_args($args, array(
		'watermark'	=> '',
		'dissolve'	=> '',
		'gravity'	=> '',
		'dx'		=> 0,
		'dy'		=> 0,
	)));

	$watermark	= $watermark?:wpjam_qiniu_get_setting('watermark');
	if($watermark){
		$watermark	= str_replace(array('+','/'),array('-','_'),base64_encode($watermark));
		$dissolve	= $dissolve?:(wpjam_qiniu_get_setting('dissolve')?:100);
		$gravity	= $gravity?:(wpjam_qiniu_get_setting('gravity')?:'SouthEast');
		$dx			= $dx?:(wpjam_qiniu_get_setting('dx')?:10);
		$dy			= $dy?:(wpjam_qiniu_get_setting('dy')?:10);

		$watermark	= 'watermark/1/image/'.$watermark.'/dissolve/'.$dissolve.'/gravity/'.$gravity.'/dx/'.$dx.'/dy/'.$dy;

		if(strpos($img_url, 'imageView')){
			$img_url = $img_url.'|'.$watermark;
		}else{
			$img_url = add_query_arg(array($watermark=>''), $img_url);
		}
	}

	return $img_url;
}

function wpjam_get_qiuniu_timestamp($img_url){
	$t		= dechex(time()+HOUR_IN_SECONDS*6);	
	$key	= '';
	$path	= parse_url($img_url, PHP_URL_PATH);
	$sign	= strtolower(md5($key.$path.$t));

	return add_query_arg(array('sign' => $sign, 't'=>$t), $img_url);
}

function wpjam_get_qiniu_image_info($img_url){
	$img_url 	= add_query_arg(array('imageInfo'=>''),$img_url);
	
	$response	= wp_remote_get($img_url);
	if(is_wp_error($response)){
		return $response;
	}

	$response	= json_decode($response['body'], true);

	if(isset($response['error'])){
		return new WP_Error('error', $response['error']);
	}

	return $response;
}


add_filter('wp_resource_hints', function ($urls, $relation_type){
	if($relation_type == 'dns-prefetch' && defined('CDN_HOST')){
		$urls[]	= CDN_HOST;
	}

	return $urls;
}, 10, 2);
