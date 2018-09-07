<?php
function wpjam_get_related_posts_query($post_id=null, $number=5){
	return WPJAM_PostTypeSetting::related_query($post_id, $number);
}

function wpjam_related_posts($number=5, $args){
	$post_id	= get_the_ID();

	$related_query	= WPJAM_PostTypeSetting::related_query($post_id, $number);

	// wpjam_print_r($related_query);
	// $related_query->posts	= array_filter($related_query->posts, function($related_post) use ($post_id){ return $related_post->ID != $post_id; });
	echo wpjam_get_post_list($related_query,$args);
}


function wpjam_get_new_posts($number=5, $args = array()){
	return wpjam_get_post_list(wpjam_query(array(
		'post_type'		=>($args['post_type'])??'post', 
		'posts_per_page'=>$number, 
		'orderby'		=>$args['orderby']??'date', 
	)), $args);
}

function wpjam_new_posts($number=5, $args= array()){
	if($output = wpjam_get_new_posts($number, $args)){
		echo $output;
	}
}

function wpjam_get_top_viewd_posts($number=5, $args = array()){
	$date_query	= array();

	if(isset($args['days'])){
		$date_query	= array(array(
			'column'	=> $args['column']??'post_date_gmt',
			'after'		=> $args['days'].' days ago',
		));
	}

	return wpjam_get_post_list(wpjam_query(array(
		'post_type'		=>$args['post_type']??array('post'), 
		'posts_per_page'=>$number, 
		'orderby'		=>'meta_value_num', 
		'meta_key'		=>'views', 
		'date_query'	=>$date_query 
	)), $args);
}

function wpjam_top_viewd_posts($number=5, $args= array()){
	if($output = wpjam_get_top_viewd_posts($number, $args)){
		echo $output;
	}
}

function wpjam_get_post_list($wpjam_query, $args){
	extract(wp_parse_args($args, array(
		'class'			=> '', 
		'thumb'			=> true,	
		'excerpt'		=> false, 
		'size'			=> 'thumbnail', 
		'crop'			=> true, 
		'thumb_class'	=> 'wp-post-image',
	)));

	if($thumb)			$class		= $class.' has-thumb';
	if($class)			$class		= ' class="'.$class.'"';
	if(is_singular())	$post_id	= get_the_ID();

	$output = '';
	$i = 0;

	if($wpjam_query->have_posts()){
		while($wpjam_query->have_posts()){
			$wpjam_query->the_post();

			$li = '';

			if($thumb){ 
				$li .=	wpjam_get_post_thumbnail(null, $size, $crop, $thumb_class)."\n";		
				$li .=	'<h4>'.get_the_title().'</h4>';
			}else{
				$li .= get_the_title();
			}

			if($excerpt){
				$li .= '<p>'.get_the_excerpt().'</p>';
			}

			if(!is_singular() || (is_singular() && $post_id != get_the_ID())) {
				$li =	'<a href="'.get_permalink().'" title="'.the_title_attribute(array('echo'=>false)).'">'.$li.'</a>';
			}
			$output .=	'<li>'.$li.'</li>'."\n";
		}

		$output = '<ul'.$class.'>'."\n".$output.'</ul>'."\n";

	}else{
		$output = false;
	}

	wp_reset_postdata();
	return $output;	
}
