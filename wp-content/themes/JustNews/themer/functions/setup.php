<?php

// wpcom setup
add_action( 'after_setup_theme', 'wpcom_setup' );
if ( ! function_exists( 'wpcom_setup' ) ) :
    function wpcom_setup() {
        global $options;
        /**
         * Add text domain
         */
        load_theme_textdomain('wpcom', get_template_directory() . '/lang');
        add_theme_support( 'woocommerce' );

        // 缩略图设置
        add_theme_support( 'post-thumbnails' );
        set_post_thumbnail_size( intval(isset($options['thumb_width']) && $options['thumb_width'] ? $options['thumb_width'] : 480), intval(isset($options['thumb_height']) && $options['thumb_height'] ? $options['thumb_height'] : 320), true );

        // 允许添加友情链接
        add_filter( 'pre_option_link_manager_enabled', '__return_true' );

        // This theme uses wp_nav_menu() in two locations.
        register_nav_menus( apply_filters( 'wpcom_menus', array() ));

        if(isset($options['wx_appid']) && $options['wx_appid'] && $options['wx_appsecret']) new WX_share();

        remove_action( 'wp_head', 'rel_canonical' );
        remove_action('wp_head', 'wp_generator');
        add_filter('revslider_meta_generator', '__return_false');

        if( !isset($options['disable_rest']) || (isset($options['disable_rest']) && $options['disable_rest']=='1')) {
            add_filter('rest_enabled', '__return_false');
            add_filter('rest_jsonp_enabled', '__return_false');
            remove_action('wp_head', 'rest_output_link_wp_head', 10);
            remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        }

        if( !isset($options['disable_emoji']) || (isset($options['disable_emoji']) && $options['disable_emoji']=='1')) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_plugins', 'wpcom_disable_emojis_tinymce');
        }
    }
endif;

add_action( 'admin_init', 'wpcom_admin_setup' );
function wpcom_admin_setup() {
    new WPCOM_Shortcodes();
    new WPCOM_Meta();
    if(stristr( $_SERVER['REQUEST_URI'], 'post.php' ) !== false && file_exists( get_template_directory() . '/css/editor-style.css' )) add_editor_style( 'css/editor-style.css' );
}

add_filter('wpcom_image_sizes', 'wpcom_image_sizes');
function wpcom_image_sizes($image_sizes){
    global $options, $_wp_additional_image_sizes;
    if(!empty($_wp_additional_image_sizes)) {
        foreach ($_wp_additional_image_sizes as $sk => $size) {
            if (isset($size['crop']) && $size['crop'] == 1) {
                $image_sizes[$sk] = $size;
            }
        }
    }
    $image_sizes['post-thumbnail'] = array(
        'width' => intval(isset($options['thumb_width']) && $options['thumb_width'] ? $options['thumb_width'] : 480),
        'height' => intval(isset($options['thumb_height']) && $options['thumb_height'] ? $options['thumb_height'] : 320)
    );
    $image_sizes['default'] = array(
        'width' => intval(isset($options['thumb_default_width']) && $options['thumb_default_width'] ? $options['thumb_default_width'] : 480),
        'height' => intval(isset($options['thumb_default_height']) && $options['thumb_default_height'] ? $options['thumb_default_height'] : 320)
    );
    return $image_sizes;
}

// wp title
add_filter( 'wp_title_parts', 'wpcom_title_parts' );
if ( ! function_exists( 'wpcom_title' ) ) :
    function wpcom_title_parts( $parts ){
        global $wp_title_parts;
        $wp_title_parts = $parts;
        return $parts;
    }
endif;

add_filter( 'wp_title', 'wpcom_title', 10, 3 );
if ( ! function_exists( 'wpcom_title' ) ) :
    function wpcom_title( $title, $sep, $seplocation) {
        global $post, $paged, $page, $options, $wp_title_parts;

        if(!isset($options['seo']) || $options['seo']=='1') {

            if ((is_home() || is_front_page()) && isset($options['home-title']) && $options['home-title']) {
                return $options['home-title'];
            }

            $title_array = $wp_title_parts;
            $prefix = '';

            if (!empty($title)) {
                $prefix = "$sep";
            }

            if ('right' == $seplocation) {
                $first_index = count($title_array) - 1;
            } else {
                $first_index = 0;
            }

            if (is_singular()) {
                $seo_title = trim(strip_tags(get_post_meta($post->ID, 'wpcom_seo_title', true)));
                if ($seo_title != '') $title_array[$first_index] = $seo_title;
            } else if (is_category() || is_tag() || is_tax()) {
                global $tax_options;
                if (!$tax_options) {
                    $term = get_queried_object();
                    $tax_options = get_option('_' . $term->taxonomy . '_' . $term->term_id);
                }
                $seo_title = isset($tax_options['seo_title']) && $tax_options['seo_title'] != '' ? $tax_options['seo_title'] : '';
                if ($seo_title != '') $title_array[$first_index] = $seo_title;
            }

            if ('right' == $seplocation) {
                $title_array = array_reverse($title_array);
                $title = implode("$sep", $title_array) . $prefix;
            } else {
                $title = $prefix . implode("$sep", $title_array);
            }
        }

        // 首页标题
        if (empty($title) && (is_home() || is_front_page())) {
            $desc = get_bloginfo('description');
            if ($desc) {
                $title = get_option('blogname') . (isset($options['title_sep_home']) && $options['title_sep_home'] ? $options['title_sep_home'] : $sep) . $desc;
            } else {
                $title = get_option('blogname');
            }
        } else {
            if ($paged >= 2 || $page >= 2) // 增加页数
                $title = $title . sprintf(__('Page %s', 'wpcom'), max($paged, $page)) . $sep;
            if ('right' == $seplocation) {
                $title = $title . get_option('blogname');
            } else {
                $title = get_option('blogname') . $title;
            }
        }
        return $title;
    }
endif;

// 加载静态资源
if ( ! function_exists( 'wpcom_scripts' ) ) :
    function wpcom_scripts() {
        global $options;
        // 载入主样式
        wp_enqueue_style( 'stylesheet', get_template_directory_uri() . '/css/style.css', array(), THEME_VERSION );

        // 替换自带的jQuery
        wp_deregister_script( 'jquery' );
        wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery.min.js', false, '1.12.4');

        // 载入js文件
        wp_enqueue_script( 'main', get_template_directory_uri() . '/js/main.js', array( 'jquery' ), THEME_VERSION, true );

        if ( is_singular() && isset($options['comments_open']) && $options['comments_open']=='1' && comments_open() && get_option( 'thread_comments' )) {
            wp_enqueue_script( 'comment-reply' );
        }
    }
endif;
add_action( 'wp_enqueue_scripts', 'wpcom_scripts' );
/* 静态资源结束 */

add_action( 'wp_enqueue_scripts', 'wpcom_localize_script' );
function wpcom_localize_script() {
    global $options;
    wp_localize_script( 'main', '_wpcom_js', array(
        'ajaxurl' => admin_url( 'admin-ajax.php'),
        'slide_speed' => isset($options['slide_speed'])?$options['slide_speed']: ''
    ) );
}

// Excerpt more
add_filter('excerpt_more', 'wpcom_excerpt_more');
if ( ! function_exists( 'wpcom_excerpt_more' ) ) :
    function wpcom_excerpt_more( $more ) {
        return '...';
    }
endif;

if ( ! function_exists( 'wpcom_disable_emojis_tinymce' ) ) :
    function wpcom_disable_emojis_tinymce( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        } else {
            return array();
        }
    }
endif;

// 百度熊掌号JSON_LD数据
add_action( 'wp_enqueue_scripts', 'wpcom_xzh_scripts' );
function wpcom_xzh_scripts() {
    global $options;
    if ( !isset($options['xzh-appid']) || empty($options['xzh-appid']) ||  ! is_singular() || is_attachment() ) {
        return;
    }
    wp_enqueue_script( 'xzh', '//msite.baidu.com/sdk/c.js?appid='.$options['xzh-appid'], array( 'jquery' ) );
}

add_action( 'wp_footer', 'wpcom_baidu_xzh');
function wpcom_baidu_xzh(){
    global $options;

    if ( !isset($options['xzh-appid']) || empty($options['xzh-appid']) ||  ! is_singular() || is_attachment() ) {
        return;
    }
    ?>

    <script type="application/ld+json">
        {
            "@context": "https://ziyuan.baidu.com/contexts/cambrian.jsonld",
            "@id": "<?php the_permalink();?>",
            "appid": "<?php echo $options['xzh-appid'];?>",
            "title": "<?php the_title();?>",
            "images": <?php echo wpcom_bdxzh_imgs();?>,
            "description": "<?php echo utf8Substr(strip_tags(get_the_excerpt()), 120);?>",
            "pubDate": "<?php the_time('Y-m-d\TH:i:s');?>",
            "upDate": "<?php the_modified_time('Y-m-d\TH:i:s');?>"
        }
    </script>
<?php }

function wpcom_bdxzh_imgs(){
    global $post;
    $imgs = '[]';

    preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', $post->post_content, $matches, PREG_PATTERN_ORDER);

    if(isset($matches[1]) && isset($matches[1][2])){ // 有3张图片
        $imgs = '["'.$matches[1][0].'","'.$matches[1][1].'","'.$matches[1][2].'"]';
    }else if($img_url = (isset($GLOBALS['post-thumb']) ? $GLOBALS['post-thumb'] : wpcom::thumbnail_url($post->ID)) ){
        $imgs = '["'.$img_url.'"]';
    }
    return $imgs;
}

add_action( 'transition_post_status', 'wpcom_xzh_submit', 10, 3 );
function wpcom_xzh_submit( $new_status, $old_status, $post ){
    if( $new_status!='publish' || $new_status==$old_status || $post->post_type!='post' ) return false;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return false;
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return false;

    global $options;
    if(isset($options['xzh-submit']) && $options['xzh-submit']) {
        wp_remote_post(trim($options['xzh-submit']), array(
                'method' => 'POST',
                'timeout' => 30,
                'headers' => array('Content-Type: text/plain'),
                'body' => get_permalink($post->ID)
            )
        );
    }
}

// 文章缩进
add_filter( 'wpcom_head', 'show_indent' );
function show_indent($echo){
    if(is_single()||is_page()){
        global $post, $options;
        $em = isset($options['show_indent'])?$options['show_indent']:get_post_meta($post->ID, 'wpcom_show_indent', true);
        if($em=='1'){
            $echo .= '<style>.entry-content p{text-indent: 2em;}</style>'."\n";
        }
    }
    return $echo;
}

// wpml 多语言插件添加菜单选项
add_filter('wp_nav_menu_items', 'wpml_nav_menu_items', 10, 2);
function wpml_nav_menu_items($items, $args) {
    // get languages
    $languages = apply_filters( 'wpml_active_languages', NULL, 'skip_missing=0' );

    // add $args->theme_location == 'primary-menu' in the conditional if we want to specify the menu location.
    if ( $languages && $args->theme_location == 'primary') {
        if(!empty($languages) && count($languages)>1){
            foreach($languages as $l){
                if($l['active']){
                    $items .= '<li class="menu-item dropdown"><a href="javascript:;"><img src="' . $l['country_flag_url'] . '" height="12" alt="' . $l['language_code'] . '" width="18"> ' . $l['native_name'] . '</a><ul class="dropdown-menu">';
                }
            }
            foreach($languages as $l){
                if(!$l['active']){
                    // flag with native name
                    $items .= '<li class="menu-item"><a href="' . $l['url'] . '"><img src="' . $l['country_flag_url'] . '" height="12" alt="' . $l['language_code'] . '" width="18"> ' . $l['native_name'] . '</a></li>';
                }
            }
            $items .= '</ul></li>';
        }
    }

    return $items;
}

add_filter( 'mce_buttons_2', 'wpcom_mce_wp_page' );
function wpcom_mce_wp_page( $buttons ) {
    $buttons[] = 'wp_page';
    return $buttons;
}

add_filter( 'mce_buttons', 'wpcom_mce_buttons' );
function wpcom_mce_buttons( $buttons ) {
    $buttons[] = 'fontsizeselect';
    return $buttons;
}

add_filter( 'tiny_mce_before_init', 'wpcom_mce_text_sizes' );
function wpcom_mce_text_sizes( $initArray ){
    $initArray['fontsize_formats'] = "10px 12px 14px 16px 18px 20px 24px 28px 32px 36px";
    return $initArray;
}

// 控制边栏标签云
add_filter('widget_tag_cloud_args', 'wpcom_tag_cloud_filter', 10);
function wpcom_tag_cloud_filter($args = array()) {
    global $options;
    $args['number'] = isset($options['tag_cloud_num']) && $options['tag_cloud_num'] ? $options['tag_cloud_num'] : 30;
    // $args['orderby'] = 'count';
    // $args['order'] = 'RAND';
    return $args;
}

add_filter( 'pre_update_option_sticky_posts', 'wpcom_fix_sticky_posts' );
if ( ! function_exists( 'wpcom_fix_sticky_posts' ) ) :
    function wpcom_fix_sticky_posts( $stickies ) {
        $old_stickies = array_diff( get_option( 'sticky_posts' ), $stickies );
        foreach( $stickies as $sticky )
            wp_update_post( array( 'ID' => $sticky, 'menu_order' => 1 ) );
        foreach( $old_stickies as $sticky )
            wp_update_post( array( 'ID' => $sticky, 'menu_order' => 0 ) );
        return $stickies;
    }
endif;

add_action( 'pre_get_posts', 'wpcom_sticky_posts_query', 10 );
if ( ! function_exists( 'wpcom_sticky_posts_query' ) ) :
    function wpcom_sticky_posts_query( $q ) {
        if( !isset( $q->query_vars[ 'ignore_sticky_posts' ] ) ){
            $q->query_vars[ 'ignore_sticky_posts' ] = 1;
        }
        if ( ( isset( $q->query_vars[ 'ignore_sticky_posts' ] ) && !$q->query_vars[ 'ignore_sticky_posts' ] ) ){
            $q->query_vars[ 'ignore_sticky_posts' ] = 1;
            if(isset($q->query_vars[ 'orderby' ]) && $q->query_vars[ 'orderby' ]) {
                $q->query_vars[ 'orderby' ] .= ' menu_order';
            }else{
                $q->query_vars[ 'orderby' ] = 'menu_order date';
            }
        }
        return $q;
    }
endif;

add_filter('wp_handle_upload_prefilter','wpcom_file_upload_rename', 10);
if ( ! function_exists( 'wpcom_file_upload_rename' ) ) :
function wpcom_file_upload_rename( $file ) {
    global $options;
    if(isset($options['file_upload_rename']) && $options['file_upload_rename']=='1') {
        $file['name'] = preg_replace('/\s/', '-', $file['name']);
        if (!preg_match('/^[0-9_a-zA-Z!@()+-.]+$/u', $file['name'])) {
            $ext = substr(strrchr($file['name'], '.'), 1);
            $file['name'] = date('YmdHis') . rand(10, 99) . '.' . $ext;
        }
    }
    return $file;
}
endif;

// 安装依赖插件
function wpcom_register_required_plugins() {
    $config = array(
        'id'           => 'wpcom',
        'default_path' => '',
        'menu'         => 'wpcom-install-plugins',
        'parent_slug'  => 'wpcom-panel',
        'capability'   => 'edit_theme_options',
        'has_notices'  => true,
        'dismissable'  => true,
        'dismiss_msg'  => '',
        'is_automatic' => false
    );

    tgmpa( $config );
}

add_action( 'tgmpa_register', 'wpcom_register_required_plugins' );

function wpcom_tgm_show_admin_notice_capability() {
    return 'edit_theme_options';
}
add_filter( 'tgmpa_show_admin_notice_capability', 'wpcom_tgm_show_admin_notice_capability' );