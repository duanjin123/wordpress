<?php
function wpjam_has_post_thumbnail(){
	return wpjam_get_post_thumbnail_uri()?true:false;
}

function wpjam_post_thumbnail($size='thumbnail', $crop=1, $class="wp-post-image"){
	if($post_thumbnail = wpjam_get_post_thumbnail(null, $size, $crop, $class)){
		echo $post_thumbnail;
	}
}

function wpjam_get_post_thumbnail($post=null, $size='thumbnail', $crop=1, $class="wp-post-image"){
	if($post_thumbnail_src = wpjam_get_post_thumbnail_src($post, $size, $crop)){
		$post_thumbnail_src_2x = wpjam_get_post_thumbnail_src($post, $size, $crop, 2);
		return '<img src="'.$post_thumbnail_src.'" srcset="'.$post_thumbnail_src_2x.' 2x" alt="'.the_title_attribute(array('echo'=>false)).'" class="'.$class.'"'.wpjam_image_hwstring($size).' />';
	}
	
	return '';
}

function wpjam_get_post_thumbnail_src($post=null, $size='thumbnail', $crop=1, $retina=1){
	if($post_thumbnail_uri = wpjam_get_post_thumbnail_uri($post, $size)){
		return wpjam_get_thumbnail($post_thumbnail_uri, $size, $crop, $retina);
	}

	return false;
}

function wpjam_get_post_thumbnail_uri($post=null, $size='full'){
	$post = get_post($post);
	if(!$post)	return false;
	
	$post_id	= $post->ID;
	$size		= CDN_NAME?'full':$size;	// 有第三方CDN的话，就获取原图
	
	$post_thumbnail_uri	= has_post_thumbnail($post_id)?wpjam_get_post_image_url(get_post_thumbnail_id($post_id), $size):'';
	$post_thumbnail_uri	= apply_filters('wpjam_post_thumbnail_uri',$post_thumbnail_uri, $post);
	$post_thumbnail_uri	= ($post_thumbnail_uri)?:wpjam_get_default_thumbnail_uri();
	
	return $post_thumbnail_uri;
}

function wpjam_get_default_thumbnail_src($size){
	return wpjam_get_thumbnail(wpjam_get_default_thumbnail_uri(), $size);
}

function wpjam_get_default_thumbnail_uri(){
	return apply_filters('wpjam_default_thumbnail_uri','');
}

function get_post_first_image($post_content=''){
	return wpjam_get_post_first_image($post_content);
}

function wpjam_get_post_first_image($post_content='', $size='full'){
	if(!$post_content){
		$the_post		= get_post();
		$post_content	= $the_post->post_content;
	}

	preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $post_content, $matches );
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
		$image_id = $matches[1][0];
		if($image_url = wpjam_get_post_image_url($image_id, $size)){
			return $image_url;
		}
	}

	preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	   
		return $matches[1][0];
	}
		
	return false;
}

function wpjam_get_post_image_url($image_id, $size='full'){
	if($thumb = wp_get_attachment_image_src($image_id, $size)){
		return $thumb[0];
	}
	return false;	
}


function wpjam_has_term_thumbnail(){
	return (wpjam_get_term_thumbnail_uri())?true:false;
}

function wpjam_term_thumbnail($size='thumbnail', $crop=1, $class="wp-term-image"){
	if($term_thumbnail =  wpjam_get_term_thumbnail(null, $size, $crop, $class)){
		echo $term_thumbnail;
	}
}

function wpjam_get_term_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-term-image"){
	if($term_thumbnail_src = wpjam_get_term_thumbnail_src($term, $size, $crop)){
		$term_thumbnail_src_2x = wpjam_get_term_thumbnail_src($term, $size, $crop, 2);

		return  '<img src="'.$term_thumbnail_src.'" srcset="'.$term_thumbnail_src_2x.' 2x" class="'.$class.'"'.wpjam_image_hwstring($size).' />';
	}
	return false;
}

function wpjam_get_term_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	if($term_thumbnail_uri = wpjam_get_term_thumbnail_uri($term)){
		return wpjam_get_thumbnail($term_thumbnail_uri, $size, $crop, $retina);
	}
	return false;	
}

function wpjam_get_term_thumbnail_uri($term=null){
	$term	= ($term)?:get_queried_object();
	$term	= get_term($term);

	if (!$term) return false;

	return apply_filters('wpjam_term_thumbnail_uri', false, $term);
}

function wpjam_has_tag_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_tag_thumbnail_uri($term=null){
	return  wpjam_get_term_thumbnail_uri($term);
}

function wpjam_get_tag_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	return  wpjam_get_term_thumbnail_src($term, $size, $crop, $retina);
}

function wpjam_get_tag_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-tag-image"){
	return  wpjam_get_term_thumbnail($term, $size, $crop, $class);
}

function wpjam_tag_thumbnail($size='thumbnail', $crop=1, $class="wp-tag-image"){
	wpjam_term_thumbnail($size,$crop,$class);
}

function wpjam_has_category_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_category_thumbnail_uri($term=null){
	return  wpjam_get_term_thumbnail_uri($term);
}

function wpjam_get_category_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	return  wpjam_get_term_thumbnail_src($term, $size, $crop, $retina);
}

function wpjam_get_category_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-category-image"){
	return  wpjam_get_term_thumbnail($term, $size, $crop, $class);
}

function wpjam_category_thumbnail($size='thumbnail', $crop=1, $class="wp-category-image"){
	wpjam_term_thumbnail($size,$crop,$class);
}
