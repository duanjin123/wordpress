<?php

function wpcom_related_post( $showposts = 10, $title = '相关文章', $img = false, $class = 'p-item-wrap', $item_class = 'col-xs-6 col-md-4 col-sm-6' ){
    global $post, $options;


    $args = array(
        'post__not_in' => array($post->ID),
        'showposts' => $showposts,
        'ignore_sticky_posts' => 1,
        'orderby' => 'rand'
    );

    if(isset($options['related_by']) && $options['related_by']=='1'){
        $tag_list = array();
        $tags = get_the_tags($post->ID);
        if($tags) {
            foreach ($tags as $tag) {
                $tid = $tag->term_id;
                if (!in_array($tid, $tag_list)) {
                    $tag_list[] = $tid;
                }
            }
        }
        $args['tag__in'] = $tag_list;
    }else{
        $cat_list = array();
        $categories = get_the_category($post->ID);
        if($categories) {
            foreach ($categories as $category) {
                $cid = $category->term_id;
                if (!in_array($cid, $cat_list)) {
                    $cat_list[] = $cid;
                }
            }
        }
        $args['category'] = join(',', $cat_list);
    }

    if($img){
        $args['meta_query'] = array(array('key' => '_thumbnail_id'));
    }

    $posts = get_posts($args);
    $output = '';
    if( $posts ) {
        $output .= '<h3 class="entry-related-title">'.$title.'</h3>';
        if($img)
            $output .=  '<ul class="entry-related-img clearfix p-list">';
        else
            $output .=  '<ul class="entry-related clearfix">';
        foreach ( $posts as $post ) { setup_postdata($post);
            if ($img) {
                $output .= '<li class="'.$item_class.' p-item"><div class="' . $class . '"><a class="thumb" href="' . get_permalink() . '">' . get_the_post_thumbnail() . '</a><h2 class="title"><a href="' . get_permalink() . '" title="' . esc_attr(get_the_title()) . '">' . get_the_title() . '</a></h2></div></li>';
            } else {
                $output .= '<li><a href="' . get_the_permalink() . '" title="' . esc_attr(get_the_title()) . '">' . get_the_title() . '</a></li>';
            }
        }
        $output .= '</ul>';
    }
    wp_reset_postdata();
    echo $output;
}