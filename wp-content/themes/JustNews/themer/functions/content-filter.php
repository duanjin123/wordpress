<?php

function filter_content($content=''){
    $sec = array();
    $html = '';
    preg_match_all('/<\w+\s?[^\>]*>={3,}([\s\S]*?)={3,}<\/\w+>/im', $content, $matches);
    $title = $matches[1];
    if(count($title)>0){
        for($i=0; $i<count($matches[0]); $i++){
            $f1 = str_replace('</','<\/',$matches[0][$i]);
            $f2 = isset($matches[0][$i+1]) ? str_replace('</','<\/',$matches[0][$i+1]) : '$';
            preg_match('/('.$f1.')([\s\S]*?)('.$f2.')/i', $content, $matches2);
            $sec[] = $matches2[2];
        }
        $html .='<ul class="entry-tab clearfix">';
        for($x=0; $x<count($title); $x++){
            $html .='<li class="entry-tab-item'.($x==0?' active':'').'">'.$title[$x].'</li>';
        }
        $html .= '</ul>';
        for($y=0; $y<count($sec); $y++){
            $html .='<div class="entry-tab-content'.($y==0?' active':'').'">'.$sec[$y].'</div>';
        }
        $content_array = explode($matches[0][0], $content);

        if( !empty($content_array[0]) ) $html = $content_array[0].$html;
    }else{
        $html = $content;
    }
    return $html;
}

add_filter( 'the_content', 'the_content_filter_images', 100 );
function the_content_filter_images( $content ) {
    if ( is_feed() || is_admin()
        || intval( get_query_var( 'print' ) ) == 1
        || intval( get_query_var( 'printpage' ) ) == 1
        || ( defined('DOING_AJAX') && DOING_AJAX )
        || ! is_singular()
    ) {
        return $content;
    }

    global $options, $post;
    $lazy_img = isset($options['lazyload_img']) && $options['lazyload_img'] ? $options['lazyload_img'] : FRAMEWORK_URI . '/assets/images/lazy.png';

    $respReplace = 'data-srcset=';

    $matches = array();
    $skip_images_regex = '/class=".*j-lazy.*"/';
    $placeholder_image = $lazy_img;

    preg_match_all( '/<img[^>].*?>/i', $content, $matches );

    $search = array();
    $replace = array();
    foreach ( $matches[0] as $imgHTML ) {
        if( in_array($imgHTML, $search) ){ continue; }
        $replaceHTML = $imgHTML;
        if( wpcom::is_spider() ) {
            if(!isset($options['post_img_alt']) || $options['post_img_alt']=='1') {
                $replaceHTML = filter_images_add_alt( $replaceHTML, esc_attr($post->post_title) );
                array_push( $search, $imgHTML );
                array_push( $replace, $replaceHTML );
            }
        } else if ( ! ( preg_match( $skip_images_regex, $imgHTML ) ) ) {
            if(!isset($options['post_img_lazyload']) || $options['post_img_lazyload']=='1'){
                $noscriptHTML = '<noscript>'.$imgHTML.'</noscript>';
                $replaceHTML = preg_replace( '/<img(.*?)src=/i', '<img$1src="' . $placeholder_image . '" data-original=', $imgHTML );
                $replaceHTML = preg_replace( '/srcset=/i', $respReplace, $replaceHTML );
                $replaceHTML = filter_images_add_class( $replaceHTML, 'j-lazy' );

                if(!isset($options['post_img_alt']) || $options['post_img_alt']=='1') {
                    $replaceHTML = filter_images_add_alt( $replaceHTML, esc_attr($post->post_title) );
                    $noscriptHTML = filter_images_add_alt( $noscriptHTML, esc_attr($post->post_title) );
                }
                $replaceHTML = $noscriptHTML.$replaceHTML;
            }else if(!isset($options['post_img_alt']) || $options['post_img_alt']=='1') {
                $replaceHTML = filter_images_add_alt( $replaceHTML, esc_attr($post->post_title) );
            }

            array_push( $search, $imgHTML );
            array_push( $replace, $replaceHTML );
        }
    }
    $content = str_replace( $search, $replace, $content );
    return $content;
}

function filter_images_add_class( $htmlString = '', $newClass ) {
    $pattern = '/class="([^"]*)"/';
    // Class attribute set.
    if ( preg_match( $pattern, $htmlString, $matches ) ) {
        $definedClasses = explode( ' ', $matches[1] );
        if ( ! in_array( $newClass, $definedClasses ) ) {
            $definedClasses[] = $newClass;
            $htmlString = str_replace(
                $matches[0],
                sprintf( 'class="%s"', implode( ' ', $definedClasses ) ),
                $htmlString
            );
        }
        // Class attribute not set.
    } else {
        $htmlString = preg_replace( '/(\<.+\s)/', sprintf( '$1class="%s" ', $newClass ), $htmlString );
    }
    return $htmlString;
}

function filter_images_add_alt( $htmlString = '', $alt='' ){
    $pattern = '/alt="([^"]*)"/';
    // alt attribute set.
    if ( preg_match( $pattern, $htmlString, $matches ) ) {
        if ( ! trim($matches[1]) ) {
            $htmlString = str_replace( $matches[0], sprintf( 'alt="%s"', $alt ), $htmlString );
        }
    } else { // alt attribute not set.
        $htmlString = preg_replace( '/(\<.+\s)/', sprintf( '$1alt="%s" ', $alt ), $htmlString );
    }
    return $htmlString;
}