<?php

add_action('wp_head', 'wpcom_seo');

if (!function_exists('utf8Substr')) {
    function utf8Substr($str, $len){
        if(function_exists('mb_substr')){
            return mb_substr($str, 0, $len, 'utf-8');
        }else{
            preg_match_all("/[x01-x7f]|[xc2-xdf][x80-xbf]|xe0[xa0-xbf][x80-xbf]|[xe1-xef][x80-xbf][x80-xbf]|xf0[x90-xbf][x80-xbf][x80-xbf]|[xf1-xf7][x80-xbf][x80-xbf][x80-xbf]/", $str, $ar);
            return join('', array_slice($ar[0], 0, $len));
        }
    }
}

function wpcom_seo(){
    global $options, $post;
    $keywords = '';
    $description = '';
    if(!isset($options['seo']) || $options['seo']=='1') {
        if(!isset($options['seo'])){
            $options['keywords'] = '';
            $options['description'] = '';
            $options['fav'] = '';
        }
        if (is_home() || is_front_page()) {
            $keywords = str_replace('，', ',', esc_attr(trim(strip_tags($options['keywords']))));
            $description = esc_attr(trim(strip_tags($options['description'])));
        } else if (is_singular()) {
            $keywords = str_replace('，', ',', esc_attr(trim(strip_tags(get_post_meta( $post->ID, 'wpcom_seo_keywords', true)))));
            if($keywords=='' && is_singular('post')){
                $post_tags = get_the_tags();
                if ($post_tags) {
                    foreach ($post_tags as $tag) {
                        $keywords = $keywords . $tag->name . ",";
                    }
                }
                $keywords = rtrim($keywords, ',');
            } else if($keywords=='' && is_singular('page')) {
                $keywords = $post->post_title;
            }
            $description = esc_attr(trim(strip_tags(get_post_meta( $post->ID, 'wpcom_seo_description', true))));
            if($description=='') {
                if ($post->post_excerpt) {
                    $description = esc_attr($post->post_excerpt);
                } else {
                    $content = preg_replace("/\[(\/?map.*?)\]/si", "", $post->post_content);
                    $content = do_shortcode($content);
                    $content = preg_replace("/[\s\r\n]*/", "", trim(strip_tags($content)));

                    $description = utf8Substr($content, 200);
                }
            }
        } else if (is_category() || is_tag() || is_tax() ) {
            global $tax_options;
            if(!$tax_options) {
                $term = get_queried_object();
                $tax_options = get_option('_' . $term->taxonomy . '_' . $term->term_id);
            }

            $keywords = isset($tax_options['seo_keywords']) && $tax_options['seo_keywords']!='' ? $tax_options['seo_keywords'] : single_cat_title('', false);
            $keywords = str_replace('，', ',', esc_attr(trim(strip_tags($keywords))));

            $description = isset($tax_options['seo_description']) && $tax_options['seo_description']!='' ? $tax_options['seo_description'] : term_description();
            $description = esc_attr(trim(strip_tags($description)));
        }
    }
    echo '<!--  WPCOM主题相关信息开始 -->'."\n";
    $seo = '<meta name="applicable-device" content="pc,mobile" />'."\n";
    $seo .= '<meta http-equiv="Cache-Control" content="no-transform" />'."\n";
    if($fav=$options['fav']){ $seo .= '<link rel="shortcut icon" href="'.$fav.'" />'."\n"; }
    if ($keywords) $seo .= '<meta name="keywords" content="' . esc_attr($keywords) . '" />' . "\n";
    if ($description) $seo .= '<meta name="description" content="' . esc_attr(trim(strip_tags($description))) . '" />' . "\n";
    if(is_singular() && !is_front_page()){
        global $paged;
        if(!$paged){$paged = 1;}
        $url = get_pagenum_link($paged);

        $img_url = wpcom::thumbnail_url($post->ID, 'full');
        $GLOBALS['post-thumb'] = $img_url;
        if(!$img_url){
            preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', $post->post_content, $matches);
            if(isset($matches[1]) && isset($matches[1][0])){
                $img_url = $matches[1][0];
            }
        }

        $image = $img_url ? $img_url : (isset($options['wx_thumb']) ? $options['wx_thumb'] : '');

        $type = 'article';
        if(is_singular('page')){
            $type = 'webpage';
        }else if(is_singular('product')){
            $type = 'product';
        }
        $seo .= '<meta property="og:type" content="'.$type.'" />' . "\n";
        $seo .= '<meta property="og:url" content="'.$url.'" />' . "\n";
        $seo .= '<meta property="og:site_name" content="'.esc_attr(get_bloginfo( "name" )).'" />' . "\n";
        $seo .= '<meta property="og:title" content="'.esc_attr($post->post_title).'" />' . "\n";
        if($image) $seo .= '<meta property="og:image" content="'.esc_url($image).'" />' . "\n";
        if ($description) $seo .= '<meta property="og:description" content="'.esc_attr(trim(strip_tags($description))).'" />' . "\n";
    }else if (is_home() || is_front_page()) {
        global $page;
        if(!$page){$page = 1;}
        $url = get_pagenum_link($page);

        $image = isset($options['wx_thumb']) ? $options['wx_thumb'] : '';
        $title = isset($options['home-title']) ? $options['home-title'] : '';;

        if($title=='') {
            $desc = get_bloginfo('description');
            if ($desc) {
                $title = get_option('blogname') . (isset($options['title_sep_home']) && $options['title_sep_home'] ? $options['title_sep_home'] : ' - ') . $desc;
            } else {
                $title = get_option('blogname');
            }
        }


        $seo .= '<meta property="og:type" content="webpage" />' . "\n";
        $seo .= '<meta property="og:url" content="'.$url.'" />' . "\n";
        $seo .= '<meta property="og:site_name" content="'.esc_attr(get_bloginfo( "name" )).'" />' . "\n";
        $seo .= '<meta property="og:title" content="'.esc_attr($title).'" />' . "\n";
        if($image) $seo .= '<meta property="og:image" content="'.esc_url($image).'" />' . "\n";
        if ($description) $seo .= '<meta property="og:description" content="'.esc_attr(trim(strip_tags($description))).'" />' . "\n";
    } else if (is_category() || is_tag() || is_tax() ) {
        global $paged;
        if(!$paged){$paged = 1;}
        $url = get_pagenum_link($paged);
        $image = isset($options['wx_thumb']) ? $options['wx_thumb'] : '';

        $seo .= '<meta property="og:type" content="webpage" />' . "\n";
        $seo .= '<meta property="og:url" content="'.$url.'" />' . "\n";
        $seo .= '<meta property="og:site_name" content="'.esc_attr(get_bloginfo( "name" )).'" />' . "\n";
        $seo .= '<meta property="og:title" content="'.esc_attr(single_cat_title('', false)).'" />' . "\n";
        if($image) $seo .= '<meta property="og:image" content="'.esc_url($image).'" />' . "\n";
        if ($description) $seo .= '<meta property="og:description" content="'.esc_attr(trim(strip_tags($description))).'" />' . "\n";
    }

    if( ( (isset($options['xzh-appid']) && $options['xzh-appid']) || (isset($options['canonical']) && $options['canonical']=='1') ) && is_singular() ){
        $id = get_queried_object_id();
        if ( 0 !== $id && $url = wp_get_canonical_url( $id )) {
            $seo .= '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
        }
    }

    echo apply_filters('wpcom_head', $seo);
    echo '<!--  WPCOM主题相关信息结束 -->'."\n";
}

// add seo options
add_filter( 'wpcom_metas', 'wpcom_post_seo_options', 15 );
function wpcom_post_seo_options( $opts ){
    global $wp_post_types, $options;
    if(!isset($options['seo']) || $options['seo']=='1'){
        $exclude_types = array( 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'um_form', 'um_role', 'um_directory', 'feature', 'client' );
        foreach( $wp_post_types as $type => $ops ){
            if( ! in_array( $type , $exclude_types ) ){

                if(!isset($opts[$type])) $opts[$type] = array();
                if(isset($opts[$type]['option'])){
                    $opts[$type] = array($opts[$type]);
                }

                $opts[$type][] =  array(
                    "title" => "SEO设置",
                    "context" => "advanced",
                    "option" => array(
                        array(
                            'name' => 'seo_title',
                            'title' => '自定义标题',
                            'desc' => '如果不想用默认的文章标题，可自定义title内容',
                            'type' => 'text'
                        ),
                        array(
                            'name' => 'seo_keywords',
                            'title' => '关键词',
                            'desc' => '多个关键词用英文逗号隔开，优先显示此处设置的关键词，未设置则使用标签',
                            'type' => 'text'
                        ),
                        array(
                            'name' => 'seo_description',
                            'title' => '描述',
                            'desc' => '优先显示此处设置的描述，未设置则使用文章摘要，无摘要则截取首段内容',
                            'type' => 'textarea'
                        )
                    )
                );
            }
        }
    }
    return $opts;
}

add_filter('wpcom_tax_metas', 'wpcom_tax_seo_options');
function wpcom_tax_seo_options( $metas ){
    global $options;
    if(!isset($options['seo']) || $options['seo']=='1') {
        $exclude_taxonomies = array('nav_menu', 'link_category', 'post_format');
        $taxonomies = get_taxonomies();
        foreach ($taxonomies as $key => $taxonomy) {
            if (!in_array($key, $exclude_taxonomies)) {
                $metas[$key] = isset($metas[$key]) && is_array($metas[$key]) ? $metas[$key] : array();
                $metas[$key][] = array(
                    'title' => '自定义标题',
                    'type' => 'input',
                    'name' => 'seo_title',
                    'desc' => '如果不想用默认的标题，可自定义title内容'
                );
                $metas[$key][] = array(
                    'title' => 'SEO关键词',
                    'type' => 'input',
                    'name' => 'seo_keywords',
                    'desc' => '多个关键词用英文逗号隔开，未设置则使用类目名称'
                );
                $metas[$key][] = array(
                    'title' => 'SEO描述',
                    'type' => 'textarea',
                    'name' => 'seo_description',
                    'desc' => '优先显示此处设置的描述，未设置则使用上面的图像描述内容'
                );
            }
        }
    }
    return $metas;
}