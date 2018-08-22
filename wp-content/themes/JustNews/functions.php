<?php

define( 'THEME_ID', '5b4220be66895b87' ); // 主题ID，请勿修改！！！
define( 'THEME_VERSION', '2.7.6' ); // 主题版本号，请勿修改！！！

// Themer 框架路径信息常量，请勿修改，框架会用到
define( 'FRAMEWORK_PATH', is_dir($framework_path = get_template_directory() . '/themer') ? $framework_path : get_theme_root() . '/Themer/themer' );
define( 'FRAMEWORK_URI', is_dir($framework_path) ? get_template_directory_uri() . '/themer' : get_theme_root_uri() . '/Themer/themer' );

require FRAMEWORK_PATH .'/load.php';

WPCOM::load(get_template_directory() . '/widgets');



function add_menu(){
    return array(
        'primary'   => '导航菜单',
        'footer'   => '页脚菜单'
    );
}
add_filter('wpcom_menus', 'add_menu');

// sidebar
if ( ! function_exists( 'wpcom_widgets_init' ) ) :
    function wpcom_widgets_init() {
        register_sidebar( array(
            'name'          => '首页边栏',
            'id'            => 'home',
            'description'   => '用户首页显示的边栏',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>'
        ) );
    }
endif;
add_action( 'wpcom_sidebar', 'wpcom_widgets_init' );

add_filter('wpcom_image_sizes', 'justnews_image_sizes');
function justnews_image_sizes($image_sizes){
    $image_sizes['post-thumbnail'] = array(
        'width' => 480,
        'height' => 300
    );
    return $image_sizes;
}

// Excerpt length
if ( ! function_exists( 'wpcom_excerpt_length' ) ) :
    function wpcom_excerpt_length( $length ) {
        return 90;
    }
endif;
add_filter( 'excerpt_length', 'wpcom_excerpt_length', 999 );

function wpcom_add_term_meta( $metas ){
    $metas['category'] = isset($metas['category']) && is_array($metas['category']) ? $metas['category'] : array();
    $metas['special'] = isset($metas['special']) && is_array($metas['special']) ? $metas['special'] : array();
    $metas['product_cat'] = isset($metas['product_cat']) && is_array($metas['product_cat']) ? $metas['product_cat'] : array();

    $metas['category'][] = array(
        'title' => 'Banner图片',
        'type' => 'upload',
        'name' => 'banner',
        'desc' => '可选，设置后标题、描述将显示在banner图片中间，推荐宽度1920px，高度为下面选项设置的高度'
    );
    $metas['category'][] = array(
        'title' => 'Banner高度',
        'type' => 'input',
        'name' => 'banner_height',
        'desc' => 'banner区域高度，单位px，默认300px'
    );
    $metas['category'][] = array(
        'title' => 'Banner文字颜色',
        'type' => 'select',
        'name' => 'text_color',
        'desc' => 'banner图片上的标题、描述文字颜色，此选项需要设置Banner图片才有效',
        'options' => array(
            '0' => '黑色',
            '1' => '白色'
        )
    );

    $metas['special'][] = array(
        'title' => '专题图片',
        'type' => 'upload',
        'name' => 'thumb',
        'desc' => '专题图片，尺寸 400 * 250 px，或等比例图片'
    );
    $metas['special'][] = array(
        'title' => 'Banner图片',
        'type' => 'upload',
        'name' => 'banner',
        'desc' => '可选，设置后标题、描述将显示在banner图片中间，推荐宽度1920px，高度为下面选项设置的高度'
    );
    $metas['special'][] = array(
        'title' => 'Banner高度',
        'type' => 'input',
        'name' => 'banner_height',
        'desc' => 'banner区域高度，单位px，默认300px'
    );
    $metas['special'][] = array(
        'title' => 'Banner文字颜色',
        'type' => 'select',
        'name' => 'text_color',
        'desc' => 'banner图片上的标题、描述文字颜色，此选项需要设置Banner图片才有效',
        'options' => array(
            '0' => '黑色',
            '1' => '白色'
        )
    );

    $metas['product_cat'][] =  array(
        'title' => 'Banner图片',
        'type' => 'upload',
        'name' => 'banner',
        'desc' => '可选，设置后标题、描述将显示在banner图片中间，推荐宽度1920px，高度为下面选项设置的高度'
    );
    $metas['product_cat'][] =  array(
        'title' => 'Banner高度',
        'type' => 'input',
        'name' => 'banner_height',
        'desc' => 'banner区域高度，单位px'
    );
    $metas['product_cat'][] =  array(
        'title' => 'Banner文字颜色',
        'type' => 'select',
        'name' => 'text_color',
        'desc' => 'banner图片上的标题、描述文字颜色，此选项需要设置Banner图片才有效',
        'options' => array(
            '0' => '黑色',
            '1' => '白色'
        )
    );
    return $metas;
}
add_filter('wpcom_tax_metas', 'wpcom_add_term_meta');

// 左右边栏设置
function sidebar_position($echo){
    global $options;
    if(isset($options['sidebar_left']) && $options['sidebar_left']==0){
        $echo .= '<style>.main{float: left;}.sidebar{float:right;}</style>'."\n";
    }
    return $echo;
}
add_filter( 'wpcom_head', 'sidebar_position' );

function format_date($time){
    global $options;
    if(isset($options['time_format']) && $options['time_format']=='0'){
        return date(get_option('date_format').(is_single()?' '.get_option('time_format'):''), $time);
    }
    $t = current_time('timestamp') - $time;
    $f = array(
        '86400'=>'天',
        '3600'=>'小时',
        '60'=>'分钟',
        '1'=>'秒'
    );
    if($t==0){
        return '1秒前';
    }else if( $t >= 604800 || $t < 0){
        return date(get_option('date_format').(is_single()?' '.get_option('time_format'):''), $time);
    }else{
        foreach ($f as $k=>$v)    {
            if (0 !=$c=floor($t/(int)$k)) {
                return $c.$v.'前';
            }
        }
    }
}

add_action('wp_ajax_wpcom_is_login', 'wpcom_is_login');
add_action('wp_ajax_nopriv_wpcom_is_login', 'wpcom_is_login');
// 登录状态
function wpcom_is_login(){
    $data = $_POST;
    $res = array();
    $current_user = wp_get_current_user();
    if($current_user->ID){
        global $options;
        $res['result'] = 0;
        //$res['id'] = $current_user->ID;
        $res['avatar'] = get_avatar( $current_user->ID, 60 );
        $res['url'] = get_author_posts_url( $current_user->ID );
        if(function_exists('um_edit_profile_url')){
            global $ultimatemember;
            $res['display_name'] = um_user('display_name');
            $res['account'] = um_get_core_page('account');
            $res['url'] = um_user_profile_url();
        }else{
            $res['display_name'] = $current_user->display_name?$current_user->display_name:$current_user->user_nicename;
        }
        $res['menus'] = array();

        $res['menus'][0] = array(
            'url' => function_exists('um_edit_profile_url') ? um_user_profile_url() : get_author_posts_url( $current_user->ID ),
            'title' => __('Profile', 'wpcom')
        );
        if(isset($options['profile_menu_url']) && isset($options['profile_menu_title']) && $options['profile_menu_url']){
            $i=1;
            foreach($options['profile_menu_url'] as $menu){
                if($menu && $options['profile_menu_title'][$i-1]) {
                    $res['menus'][$i] = array();
                    $res['menus'][$i]['url'] = esc_url($menu);
                    $res['menus'][$i]['title'] = $options['profile_menu_title'][$i-1];
                }
                $i++;
            }
        }
        if(function_exists('um_edit_profile_url')) {
            $res['menus'][] = array(
                'url' => um_get_core_page('account'),
                'title' => __('Account', 'wpcom')
            );
        }
        $res['menus'][] = array(
            'url' => function_exists('um_edit_profile_url')? um_get_core_page('logout') : wp_logout_url(),
            'title' => __( 'Logout', 'wpcom' )
        );
    }else{
        $res['result'] = -1;
    }

    if ( function_exists('is_woocommerce') ) {
        ob_start();

        woocommerce_mini_cart();

        $mini_cart = ob_get_clean();

        $data = array(
            'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
                    'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
                )
            ),
            'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
        );

        $res['wc'] = $data;
    }
    echo wp_json_encode($res);
    die();
}

add_action('wp_ajax_wpcom_like_it', 'wpcom_like_it');
add_action('wp_ajax_nopriv_wpcom_like_it', 'wpcom_like_it');
function wpcom_like_it(){
    $data = $_POST;
    $res = array();
    if(isset($data['id']) && $data['id'] && $post = get_post($data['id'])){
        $cookie = isset($_COOKIE["wpcom_liked_".$data['id']])?$_COOKIE["wpcom_liked_".$data['id']]:0;
        if(isset($cookie) && $cookie=='1'){
            $res['result'] = -2;
        }else{
            $res['result'] = 0;
            $likes = get_post_meta($data['id'], 'wpcom_likes', true);
            $likes = $likes ? $likes : 0;
            $res['likes'] = $likes + 1;
            // 数据库增加一个喜欢数量
            update_post_meta( $data['id'], 'wpcom_likes', $res['likes'] );
            //cookie标记已经给本文点赞过了
            setcookie('wpcom_liked_'.$data['id'], 1, time()+3600*24*365, '/');
        }
    }else{
        $res['result'] = -1;
    }
    echo wp_json_encode($res);
    die();
}

add_action('wp_ajax_wpcom_heart_it', 'wpcom_heart_it');
add_action('wp_ajax_nopriv_wpcom_heart_it', 'wpcom_heart_it');
function wpcom_heart_it(){
    $data = $_POST;
    $res = array();
    $current_user = wp_get_current_user();
    if($current_user->ID){
        if(isset($data['id']) && $data['id'] && $post = get_post($data['id'])){
            // 用户关注的文章
            $u_favorites = get_user_meta($current_user->ID, 'wpcom_favorites', true);
            $u_favorites = $u_favorites ? $u_favorites : array();
            // 文章关注人数
            $p_favorite = get_post_meta($data['id'], 'wpcom_favorites', true);
            $p_favorite = $p_favorite ? $p_favorite : 0;
            if(in_array($data['id'], $u_favorites)){ // 用户是否关注本文
                $res['result'] = 1;
                $nu_favorites = array();
                foreach($u_favorites as $uf){
                    if($uf != $data['id']){
                        $nu_favorites[] = $uf;
                    }
                }
                $p_favorite -= 1;
            }else{
                $res['result'] = 0;
                $u_favorites[] = $data['id'];
                $nu_favorites = $u_favorites;
                $p_favorite += 1;
            }
            $p_favorite = $p_favorite<0 ? 0 : $p_favorite;
            update_user_meta($current_user->ID, 'wpcom_favorites', $nu_favorites);
            update_post_meta($data['id'], 'wpcom_favorites', $p_favorite);
            $res['favorites'] = $p_favorite;
        }else{
            $res['result'] = -2;
        }
    }else{ // 未登录
        $res['result'] = -1;
    }
    echo wp_json_encode($res);
    die();
}

$roles = get_option('settings_basic');
if(is_array($roles) && isset($roles['slug_like'])){
    $sluglike = $roles['slug_like'];
    $slugadd = $roles['slug_add'];
}else{
    $sluglike = 'favorites';
    $slugadd = 'addpost';
}

add_filter('um_profile_tabs', 'my_like_posts_tab', 1000 );
function my_like_posts_tab( $tabs  ) {
    global $sluglike;
    $tabs[$sluglike] = array(
        'name' => __( 'Favorites', 'wpcom' ),
        'icon' => 'um-faicon-heart',
    );
    return $tabs;
}

function favorites_posts_orderby(){
    global $wpdb;
    $current_user = wp_get_current_user();
    $favorites = get_user_meta($current_user->ID, 'wpcom_favorites', true);
    if($favorites)
        return "FIELD(".$wpdb->posts.".ID, ".implode(',', $favorites).") DESC";
}

add_action('um_profile_content_'.$sluglike.'_default', 'my_like_posts_tab_default');
function my_like_posts_tab_default( $args ) {
    $user_id = um_user('ID');
    $favorites = get_user_meta($user_id, 'wpcom_favorites', true);

    if($favorites) {
        add_filter('posts_orderby', 'favorites_posts_orderby');
        $args = array(
            'post_type' => 'post',
            'post__in' => $favorites,
            'posts_per_page' => 10,
            'ignore_sticky_posts' => 1
        );
        query_posts($args);
        if (have_posts()) :
            echo '<ul class="article-list">';
            while (have_posts()) : the_post();
                get_template_part('templates/list', 'default');
            endwhile;
            echo '</ul>';
        else:
            $emo = um_get_option('profile_empty_text_emo');
            if ( $emo ) {
                $emo = '<i class="um-faicon-frown-o"></i>';
            } else {
                $emo = false;
            }
            echo '<div class="um-profile-note">'.$emo.'<span>'.__('This user has no favorite posts.', 'wpcom') .'</span></div>';
        endif;
        wp_reset_query();
    }else{
        $emo = um_get_option('profile_empty_text_emo');
        if ( $emo ) {
            $emo = '<i class="um-faicon-frown-o"></i>';
        } else {
            $emo = false;
        }
        echo '<div class="um-profile-note">'.$emo.'<span>'.__('This user has no favorite posts.', 'wpcom') .'</span></div>';
    }
}

add_filter('um_profile_tabs', 'add_post_tab', 1000 );
function add_post_tab( $tabs  ) {
    global $slugadd, $options;
    if(isset($options['tougao_on']) && $options['tougao_on']=='1') {
        $tabs[$slugadd] = array(
            'name' => __('Add post', 'wpcom'),
            'icon' => 'um-faicon-pencil-square-o',
        );
    }
    return $tabs;
}

add_filter('um_core_pages', 'add_um_core_pages');
function add_um_core_pages($pages){
    $pages['add-post'] = '投稿页面';
    return $pages;
}

add_filter('um_core_pages_info', 'add_um_core_pages_info');
function add_um_core_pages_info($pages){
    $pages['add-post'] = array('title'=>'投稿页面');
    return $pages;
}

add_filter('um_profile_menu_link_'.$slugadd, 'add_post_tab_link');
function add_post_tab_link(){
    $url = um_get_core_page('add-post');
    $url = $url ? $url : '';
    return $url;
}

if(is_array($roles)){
    add_filter('um_user_profile_tabs', 'disable_user_tab', 1000 );
    function disable_user_tab( $tabs ) {
        global $roles , $sluglike, $slugadd;
        $user_id = um_user('ID');
        $role = get_user_meta( $user_id, 'role', true );
        if(!um_is_myprofile() || !in_array($role, $roles['roles'])){
            unset( $tabs[$sluglike] );
            unset( $tabs[$slugadd] );
        }

        return $tabs;
    }
}

function get_role_name( $user ){
    um_fetch_user( $user );
    global $ultimatemember;
    return $ultimatemember->user->get_role_name(um_user('role'));
}

function post_editor_settings($args = array()){
    $img = current_user_can('upload_files');
    return array(
        'textarea_name' => $args['textarea_name'],
        //'textarea_rows' => $args['textarea_rows'],
        'media_buttons' => false,
        'quicktags' => false,
        'tinymce'       => array(
            'height'        => 350,
            'toolbar1' => 'formatselect,bold,underline,blockquote,forecolor,alignleft,aligncenter,alignright,link,unlink,bullist,numlist,'.($img?'wpcomimg,':'image,').'undo,redo,fullscreen,wp_help',
            'toolbar2' => '',
            'toolbar3' => '',
        )
    );
}

add_filter( 'mce_external_plugins', 'wpcom_mce_plugin');
function wpcom_mce_plugin($plugin_array){
    global $is_submit_page;
    if ( $is_submit_page ) {
        wp_enqueue_media();
        wp_enqueue_script('jquery.taghandler', get_template_directory_uri() . '/js/jquery.taghandler.min.js', array('jquery'), THEME_VERSION, true);
        wp_enqueue_script('edit-post', get_template_directory_uri() . '/js/edit-post.js', array('jquery'), THEME_VERSION, true);

        $plugin_array['wpcomimg'] = admin_url('admin-ajax.php?action=wpcomimg');
    }
    return $plugin_array;
}

add_action('wp_ajax_wpcomimg', 'wpcom_img');
function wpcom_img(){
    header("Content-type: text/javascript");
    echo '(function($) {
            tinymce.create("tinymce.plugins.wpcomimg", {
                init : function(ed, url) {
                    ed.addButton("wpcomimg", {
                        icon: "image",
                        tooltip : "添加图片",
                        onclick: function(){
                            var uploader;
                            if (uploader) {
                                uploader.open();
                            }else{
                                uploader = wp.media.frames.file_frame = wp.media({
                                    title: "选择图片",
                                    button: {
                                        text: "插入图片"
                                    },
                                    library : {
                                        type : "image"
                                    },
                                    multiple: true
                                });
                                uploader.on("select", function() {
                                    var attachments = uploader.state().get("selection").toJSON();
                                    var img = "";
                                    for(var i=0;i<attachments.length;i++){
                                        img += "<img src=\""+attachments[i].url+"\" width=\""+attachments[i].width+"\" height=\""+attachments[i].height+"\" alt=\""+(attachments[i].alt?attachments[i].alt:attachments[i].title)+"\">";
                                    }
                                    tinymce.activeEditor.execCommand("mceInsertContent", false, img)
                                });
                                uploader.open();
                            }
                        }
                    });
                }
        });
        // Register plugin
        tinymce.PluginManager.add("wpcomimg", tinymce.plugins.wpcomimg);
        })(jQuery);';
    exit;
}

add_action('pre_get_posts','wpcom_restrict_media_library');
function wpcom_restrict_media_library( $wp_query_obj ) {
    global $current_user, $pagenow;
    if( ! $current_user instanceof WP_User )
        return;
    if( 'admin-ajax.php' != $pagenow || $_REQUEST['action'] != 'query-attachments' )
        return;
    if( !current_user_can('edit_others_posts') )
        $wp_query_obj->set('author', $current_user->ID );
    return;
}

function wpcom_tougao_tinymce_style($content) {
    if ( ! is_admin() ) {
        global $editor_styles, $stylesheet;
        $editor_styles = (array) $editor_styles;
        $stylesheet    = (array) $stylesheet;
        $stylesheet[] = 'css/editor-style.css';
        $editor_styles = array_merge( $editor_styles, $stylesheet );
    }
    return $content;
}

add_filter('wpcom_update_post','wpcom_update_post');
function wpcom_update_post($res){

    add_filter('the_editor_content', "wpcom_tougao_tinymce_style");

    if(isset($_POST['post-title'])){ // 只处理post请求
        $nonce = $_POST['wpcom_update_post_nonce'];
        if ( wp_verify_nonce( $nonce, 'wpcom_update_post' ) ){
            $post_id = isset($_GET['post_id'])?$_GET['post_id']:'';

            $post_title = $_POST['post-title'];
            $post_excerpt = $_POST['post-excerpt'];
            $post_content = $_POST['post-content'];
            $post_category = isset($_POST['post-category'])?$_POST['post-category']:array();
            $post_tags = $_POST['post-tags'];
            $_thumbnail_id = $_POST['_thumbnail_id'];

            if($post_id){ // 编辑文章
                $post = get_post($post_id);
                if(isset($post->ID)) { // 文章要存在
                    $p = array(
                        'ID' => $post_id,
                        'post_type' => 'post',
                        'post_title' => $post_title,
                        'post_excerpt' => $post_excerpt,
                        'post_content' => $post_content,
                        'post_category' => $post_category,
                        'tags_input' => $post_tags
                    );
                    if($post->post_status=='draft' && trim($post_title)!='' && trim($post_content)!=''){
                        $p['post_status'] = current_user_can( 'publish_posts' ) ? 'publish' : 'pending';
                    }
                    $pid = wp_update_post($p, true);
                    if ( !is_wp_error( $pid ) ) {
                        update_post_meta($pid, '_thumbnail_id', $_thumbnail_id);
                    }
                }
            }else{ // 新建文章
                if(trim($post_title)=='' && trim($post_content)==''){
                    return array();
                }else if(trim($post_title)=='' || trim($post_content)=='' || empty($post_category)){
                    $post_status = 'draft';
                }else{
                    $post_status = current_user_can( 'publish_posts' ) ? 'publish' : 'pending';
                }
                $p = array(
                    'post_type' => 'post',
                    'post_title' => $post_title,
                    'post_excerpt' => $post_excerpt,
                    'post_content' => $post_content,
                    'post_status' => $post_status,
                    'post_category' => $post_category,
                    'tags_input' => $post_tags
                );
                $pid = wp_insert_post($p, true);
                if ( !is_wp_error( $pid ) ) {
                    update_post_meta($pid, '_thumbnail_id', $_thumbnail_id);
                    update_post_meta($pid, 'wpcom_copyright_type', 'copyright_tougao');
                    wp_redirect(get_edit_link($pid).'&submit=true');
                }
            }
        }
    }
    return $res;
}

function get_edit_link($id){
    $url = preg_replace('/(.*)(?|&)post_id=[^&]+?(&)(.*)/i', '$1$2$4', add_post_tab_link().'&');
    $url = substr($url,0,-1);
    if(strpos($url,'?') === false){
        return $url.'?post_id='.$id;
    } else {
        return $url.'&post_id='.$id;
    }
}

function max_page(){
    global $wp_query;
    return $wp_query->max_num_pages;
}

add_action('wp_ajax_wpcom_load_posts', 'wpcom_load_posts');
add_action('wp_ajax_nopriv_wpcom_load_posts', 'wpcom_load_posts');

function wpcom_load_posts(){
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $page = $_POST['page'];
    $page = $page ? $page : 1;
    $per_page = get_option('posts_per_page');
    if($id){
        $posts = new WP_Query(array(
            'posts_per_page' => $per_page,
            'paged' => $page,
            'cat' => $id,
            'post_status' => array( 'publish' ),
            'ignore_sticky_posts' => 0
        ));
    }else{
        global $options;
        $arg = array(
            'posts_per_page' => $per_page,
            'paged' => $page,
            'ignore_sticky_posts' => 0,
            'post_status' => array( 'publish' ),
            'category__not_in' => isset($options['newest_exclude']) ? $options['newest_exclude'] : array()
        );
        $posts = new WP_Query($arg);

    }
    global $post;
    if($posts->have_posts()) {
        while ( $posts->have_posts() ) : $posts->the_post();
            get_template_part('templates/list', 'default-sticky');
        endwhile;
        wp_reset_postdata();
        if($id && $page==1 && get_category($id)->count>$per_page){
            echo '<li class="load-more-wrap"><a class="load-more" data-id="'.$id.'" href="javascript:;">'.__('Load more posts', 'wpcom').'</a></li>';
        }
    }else{
        echo 0;
    }
    exit;
}

add_action( 'init', 'wpcom_create_special' );
function wpcom_create_special(){
    global $options;
    if(isset($options['special_on']) && $options['special_on']) { //是否开启专题功能
        $slug = isset($options['special_slug']) && $options['special_slug'] ? $options['special_slug'] : 'special';
        $labels = array(
            'name' => '专题',
            'singular_name' => '专题',
            'search_items' => '搜索专题',
            'all_items' => '所有专题',
            'parent_item' => '父级专题',
            'parent_item_colon' => '父级专题',
            'edit_item' => '编辑专题',
            'update_item' => '更新专题',
            'add_new_item' => '添加专题',
            'new_item_name' => '新专题名',
            'not_found' => '暂无专题',
            'menu_name' => '专题',
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => $slug),
        );
        register_taxonomy('special', 'post', $args);
    }
}

function get_special_list($num=10, $paged=1){
    $special = get_terms( array(
        'taxonomy' => 'special',
        'orderby' => 'id',
        'order' => 'DESC',
        'number' => $num,
        'hide_empty' => false,
        'offset' => $num*($paged-1)
    ) );
    return $special;
}

// 优化专题排序支持 Simple Custom Post Order 插件
add_filter( 'get_terms_orderby', 'wpcom_get_terms_orderby', 20, 3);
function wpcom_get_terms_orderby($orderby, $args, $tax){
    if(class_exists('SCPO_Engine') && $tax && count($tax)==1 && $tax[0]=='special'){
        $orderby = 't.term_order, t.term_id';
    }
    return $orderby;
}

add_action('wp_ajax_wpcom_load_special', 'wpcom_load_special');
add_action('wp_ajax_nopriv_wpcom_load_special', 'wpcom_load_special');

function wpcom_load_special(){
    global $options, $post;
    $page = $_POST['page'];
    $page = $page ? $page : 1;
    $per_page = isset($options['special_per_page']) && $options['special_per_page'] ? $options['special_per_page'] : 10;

    $special = get_special_list($per_page, $page);
    if($special){
    foreach($special as $sp){
        $special_options = get_option('_special_'.$sp->term_id);
        $thumb = isset($special_options['thumb']) ? $special_options['thumb'] : get_option('special_thumb_'.$sp->term_id);

        $link = get_term_link($sp->term_id);
        ?>
        <div class="col-md-6 col-xs-6 special-item-wrap">
            <div class="special-item">
                <div class="special-item-top">
                    <div class="special-item-thumb">
                        <a href="<?php echo $link;?>" target="_blank"><img src="<?php echo esc_url($thumb);?>" alt="<?php echo esc_attr($sp->name);?>"></a>
                    </div>
                    <div class="special-item-title">
                        <h2><a href="<?php echo $link;?>" target="_blank"><?php echo $sp->name;?></a></h2>
                        <?php echo category_description($sp->term_id);?>
                    </div>
                    <a class="special-item-more" href="<?php echo $link;?>"><?php echo _x('Read More', 'topic', 'wpcom');?></a>
                </div>
                <ul class="special-item-bottom">
                    <?php
                    $args = array(
                        'posts_per_page' => 3,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'special',
                                'field' => 'term_id',
                                'terms' => $sp->term_id
                            )
                        )
                    );
                    $postslist = get_posts( $args );
                    foreach($postslist as $post) {
                        setup_postdata($post);?>
                        <li><a title="<?php echo esc_attr(get_the_title());?>" href="<?php the_permalink();?>" target="_blank"><?php the_title();?></a></li>
                    <?php } wp_reset_postdata(); ?>
                </ul>
            </div>
        </div>
    <?php }
    } else {
        echo 0;
    }
    exit;
}

function wpcom_post_copyright() {
    global $post, $options;

    $copyright = '';

    $copyright_type = get_post_meta($post->ID, 'wpcom_copyright_type', true);
    if(!$copyright_type){
        $copyright = isset($options['copyright_default']) ? $options['copyright_default'] : '';
    }else if($copyright_type=='copyright_tougao'){
        $copyright = isset($options['copyright_tougao']) ? $options['copyright_tougao'] : '';;
    }else if($copyright_type){
        if(isset($options['copyright_id']) && $options['copyright_id']) {
            foreach ($options['copyright_id'] as $i => $id) {
                if($copyright_type = $id && $options['copyright_text'][$i]) {
                    $copyright = $options['copyright_text'][$i];
                }
            }
        }
    }

    if(preg_match('%SITE_NAME%', $copyright)) $copyright = str_replace('%SITE_NAME%', get_bloginfo('name'), $copyright);
    if(preg_match('%SITE_URL%', $copyright)) $copyright = str_replace('%SITE_URL%', get_bloginfo('url'), $copyright);
    if(preg_match('%POST_TITLE%', $copyright)) $copyright = str_replace('%POST_TITLE%', get_the_title(), $copyright);
    if(preg_match('%POST_URL%', $copyright)) $copyright = str_replace('%POST_URL%', get_permalink(), $copyright);
    if(preg_match('%AUTHOR_NAME%', $copyright)) $copyright = str_replace('%AUTHOR_NAME%', get_the_author(), $copyright);
    if(preg_match('%AUTHOR_URL%', $copyright)) $copyright = str_replace('%AUTHOR_URL%', get_author_posts_url(get_the_author_meta( 'ID' )), $copyright);
    if(preg_match('%ORIGINAL_NAME%', $copyright)) $copyright = str_replace('%ORIGINAL_NAME%', get_post_meta($post->ID, 'wpcom_original_name', true), $copyright);
    if(preg_match('%ORIGINAL_URL%', $copyright)) $copyright = str_replace('%ORIGINAL_URL%', get_post_meta($post->ID, 'wpcom_original_url', true), $copyright);

    echo $copyright ? '<div class="entry-copyright">'.$copyright.'</div>' : '';
}

add_filter('comment_reply_link', 'wpcom_comment_reply_link', 10, 1);
function wpcom_comment_reply_link($link){
    if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
        $link = '<a rel="nofollow" class="comment-reply-login" href="javascript:;">回复</a>';
    }
    return $link;
}

add_action('init', 'wpcom_allow_contributor_uploads');
function wpcom_allow_contributor_uploads() {
    $user = wp_get_current_user();
    if( isset($user->roles) && $user->roles && $user->roles[0] == 'contributor' ){
        global $options;
        $allow = isset($options['tougao_upload']) && $options['tougao_upload']=='0' ? 0 : 1;
        $can_upload = isset($user->allcaps['upload_files']) ? $user->allcaps['upload_files'] : 0;

        if ( $allow && !$can_upload ) {
            $contributor = get_role('contributor');
            $contributor->add_cap('upload_files');
        } else if(!$allow && $can_upload){
            $contributor = get_role('contributor');
            $contributor->remove_cap('upload_files');
        }
    }
}

function utf8_excerpt($str, $len){
    $str = strip_tags( str_replace( array( "\n", "\r" ), ' ', $str ) );
    if(function_exists('mb_substr')){
        $excerpt = mb_substr($str, 0, $len, 'utf-8');
    }else{
        preg_match_all("/[x01-x7f]|[xc2-xdf][x80-xbf]|xe0[xa0-xbf][x80-xbf]|[xe1-xef][x80-xbf][x80-xbf]|xf0[x90-xbf][x80-xbf][x80-xbf]|[xf1-xf7][x80-xbf][x80-xbf][x80-xbf]/", $str, $ar);
        $excerpt = join('', array_slice($ar[0], 0, $len));
    }

    if(trim($str)!=trim($excerpt)){
        $excerpt .= '...';
    }
    return $excerpt;
}

add_theme_support( 'wc-product-gallery-lightbox' );

add_action( 'wpcom_echo_ad', 'wpcom_echo_ad', 10, 1);
function wpcom_echo_ad( $id ){
    if(defined('DOING_AJAX') && DOING_AJAX) return false;
    if($id && $id=='ad_flow'){
        global $wp_query;
        if(!isset($wp_query->ad_index)) $wp_query->ad_index = rand(1, $wp_query->post_count-2);
        $current_post = $wp_query->current_post;
        if(isset($wp_query->posts->current_post)) $current_post = $wp_query->posts->current_post;
        if($current_post==$wp_query->ad_index) echo wpcom_ad_html($id);
    }else if($id) {
        echo wpcom_ad_html($id);
    }
}

function wpcom_ad_html($id){
    if($id) {
        global $options;
        $html = '';
        if( wp_is_mobile() && isset($options[$id.'_mobile']) && $options[$id.'_mobile'] ) {
            $html = '<div class="wpcom_ad_wrap">';
            $html .= $options[$id.'_mobile'];
            $html .= '</div>';
        } else if ( isset($options[$id]) && $options[$id] ) {
            $html = '<div class="wpcom_ad_wrap">';
            $html .= $options[$id];
            $html .= '</div>';
        }

        if($html && $id=='ad_flow') $html = '<li class="item item-ad">'.$html.'</li>';
        return $html;
    }
}

add_action( 'wp_head', 'wpcom_style_output', 20 );
if ( ! function_exists( 'wpcom_style_output' ) ) :
    function wpcom_style_output(){
        global $options; ?>
        <style>
            <?php
            $theme_color = WPCOM::color($options['theme_color']?$options['theme_color']:'#3ca5f6');
            $theme_color_hover = WPCOM::color($options['theme_color_hover']?$options['theme_color_hover']:'#4285f4');
            ?>
            a,.sec-panel-head span,.list.tabs .tab.active a,.header .nav>li.active>a,.header .dropdown-menu>.active>a,.entry .entry-info .nickname,.entry-bar-inner .author-title,.entry-action .btn-zan,.entry-action .btn-dashang,.comment-body .nickname,.form-submit-text span,.widget_profile .author-title,.um .um-profile-body .um-item-meta a,.login-modal-body .btn-register{color: <?php echo $theme_color;?>;}
            .header .dropdown-menu>li>a:hover,.header .dropdown-menu>.active>a:hover,.header .dropdown-menu>.active>a:focus,.navbar-action .publish,.pagination .current,.flex-control-paging li a.flex-active,.form-submit .submit,.sidebar .widget_nav_menu ul li.current-menu-item a,.sidebar .widget_nav_menu ul li.current-post-parent a,.search-form input.submit,.action .contact-title,.pf-submit,.tagHandler ul.tagHandlerContainer li.tagItem,.login-modal-body .btn-login,.woocommerce a.button,.woocommerce button.button,.woocommerce input.button,.woocommerce a.button.alt,.woocommerce button.button.alt,.woocommerce input.button.alt,.woocommerce a.button.alt.disabled,.woocommerce a.button.alt.disabled:hover,.woocommerce a.button.alt:disabled,.woocommerce a.button.alt:disabled:hover,.woocommerce a.button.alt:disabled[disabled],.woocommerce a.button.alt:disabled[disabled]:hover,.woocommerce button.button.alt.disabled,.woocommerce button.button.alt.disabled:hover,.woocommerce button.button.alt:disabled,.woocommerce button.button.alt:disabled:hover,.woocommerce button.button.alt:disabled[disabled],.woocommerce button.button.alt:disabled[disabled]:hover,.woocommerce input.button.alt.disabled,.woocommerce input.button.alt.disabled:hover,.woocommerce input.button.alt:disabled,.woocommerce input.button.alt:disabled:hover,.woocommerce input.button.alt:disabled[disabled],.woocommerce input.button.alt:disabled[disabled]:hover,.woocommerce .widget_price_filter .ui-slider .ui-slider-handle,.woocommerce .widget_price_filter .ui-slider .ui-slider-range,.shopping-count,.social-login-form .sl-input-submit,.article-list .item-title a .sticky-post{background-color: <?php echo $theme_color;?>;}
            .entry .entry-content h3,.entry .entry-content .h3,.widget-title{border-left-color: <?php echo $theme_color;?>;}
            .entry-bar-inner .author-title,.entry-action .btn-zan,.entry-action .btn-dashang,.search-form input.keyword:focus,.widget_profile .author-title,.login-modal-body .btn-login,.login-modal-body .btn-register{border-color: <?php echo $theme_color;?>;}
            .entry-bar-inner .author-title:before,.widget_profile .author-title:before{border-right-color: <?php echo $theme_color;?>;}
            .list.tabs .tab.active a{border-bottom-color: <?php echo $theme_color;?>;}

            a:hover,.header .nav>li>a:hover,.navbar-action .login:hover,.navbar-action .login:focus,.navbar-action .profile a:hover,.navbar-search-icon:hover,.navbar-search-icon:focus,.entry .entry-info a:hover,.entry .entry-info a:focus,.entry-bar .info-item a:hover,.p-item-wrap:hover .title a,.special-item-title a:hover,.special-item-bottom a:hover,.widget ul a:hover,.widget ol a:hover,.sec-panel-head .more:hover,.topic-list .topic-wrap:hover span,.tabs .tab a:hover,
            .article-list .item-title a:hover,.article-list .item-meta a:hover,.article-list .item-meta a:focus,.list-links a:hover,.list-links a:focus,.um .um-profile-body .um-item-meta a:hover,.um .um-profile-body .um-item-link a:hover,.load-more:hover,.login-modal-body .btn-register:hover,.list.tabs .tab a:hover{color: <?php echo $theme_color_hover;?>;}
            .navbar-action .publish:hover,.navbar-action .publish:focus,.entry-tag a:hover,.entry-tag a:focus,.entry-action .btn-zan:hover,.entry-action .btn-dashang:hover,.entry-action .btn-zan.liked,.pagination a:hover,.flex-control-paging li a:hover,.form-submit .submit:hover,.widget .tagcloud a:hover,.sidebar .widget_nav_menu ul li.current-menu-item .sub-menu a:hover,.sidebar .widget_nav_menu ul li.current-post-parent .sub-menu a:hover,.sidebar .widget_nav_menu ul li a:hover,.search-form input.submit:hover,.action .a-box:hover,.footer-sns .fa:after,.article-list .item-category:hover,.pf-submit:hover,.tagHandler ul.tagHandlerContainer li.tagItem:hover,.login-modal-body .btn-login:hover,.woocommerce a.button:hover,.woocommerce button.button:hover,.woocommerce input.button:hover,.woocommerce a.button.alt:hover,.woocommerce button.button.alt:hover,.woocommerce input.button.alt:hover,.woocommerce .widget_price_filter .price_slider_wrapper .ui-widget-content,.social-login-form .sl-input-submit:hover{background-color: <?php echo $theme_color_hover;?>;}
            .entry-tag a:hover,.entry-tag a:focus,.entry-action .btn-zan:hover,.entry-action .btn-dashang:hover,.entry-action .btn-zan.liked,.widget .tagcloud a:hover,.load-more:hover,.login-modal-body .btn-login:hover,.login-modal-body .btn-register:hover{border-color: <?php echo $theme_color_hover;?>;}
            .special-item-bottom a:hover:before{border-left-color: <?php echo $theme_color_hover;?>;}
            .list.tabs .tab.active a:hover{border-bottom-color: <?php echo $theme_color_hover;?>;}
            <?php if(isset($options['bg_color']) && ($options['bg_color'] || $options['bg_image'])){ ?>@media (min-width: 992px){
                body{  <?php if($options['bg_color']) {echo 'background-color: '.WPCOM::color($options['bg_color']).';';};?> <?php if($options['bg_image']) {echo 'background-image: url('.$options['bg_image'].');';};?><?php if($options['bg_image_repeat']) {echo 'background-repeat: '.$options['bg_image_repeat'].';';};?><?php if($options['bg_image_position']) {echo 'background-position: '.$options['bg_image_position'].';';};?><?php if($options['bg_image_attachment']=='1') {echo 'background-attachment: fixed;';};?>}
                <?php if($options['special_title_color']){?>.special-head .special-title,.special-head p{color:<?php echo WPCOM::color($options['special_title_color']);?>;}.special-head .page-description:before{background:<?php echo WPCOM::color($options['special_title_color']);?>;}<?php } ?>
                .special-head .page-description:before,.special-head p{opacity: 0.5;}
            }
            <?php } ?>
            @media (max-width: 991px){
                .header .navbar-nav>li.active>a,.header .navbar-nav .dropdown-menu .active a,.header .navbar-nav .dropdown-menu .active .dropdown-menu .active a{color: <?php echo $theme_color;?>;}
                .navbar-collapse{background-color: <?php echo $theme_color;?>;}
                .dropdown-menu>li a{border-color: <?php echo $theme_color_hover;?>;}
                .header .navbar-nav>li.active>a:hover,.header .navbar-nav>li a:hover,.header .navbar-nav .dropdown-menu .active a:hover,.header .navbar-nav .dropdown-menu .active .dropdown-menu a:hover,.header .navbar-nav .dropdown-menu li a:hover{color: <?php echo $theme_color_hover;?>;}
            }
            .j-share{position: fixed!important;top: <?php echo $options['action_top']?$options['action_top']:'50%'?>!important;}
            <?php if(isset($options['logo-height']) && $logo_height = intval($options['logo-height'])){
            $logo_height = $logo_height>50 ? 50 : $logo_height;
            ?>
            .header .logo img{max-height: <?php echo $logo_height;?>px;}
            <?php } if(isset($options['logo-height-mobile']) && $mob_logo_height = intval($options['logo-height-mobile'])){
            $mob_logo_height = $mob_logo_height>40 ? 40 : $mob_logo_height;
            ?>
            @media (max-width: 767px){
                .header .logo img{max-height: <?php echo $mob_logo_height;?>px;}
            }
            <?php } ?>
            <?php if(get_locale()!='zh_CN'){ ?>
            .action .a-box:hover:after{padding: 0;font-family: "FontAwesome";font-size: 20px;line-height: 40px;}
            .action .contact:hover:after{content:'\f0e5';}
            .action .wechat:hover:after{content:'\f029';}
            .action .share:hover:after{content:'\f045';}
            .action .gotop:hover:after{content:'\f106';font-size: 36px;}
            <?php } ?>
            <?php echo $options['custom_css'];?>
        </style>
    <?php }
endif;

function is_multimage( $post_id = '' ){
    global $post, $options;
    if($post_id==''){
        $post_id = $post->ID;
    }
    $multimage = get_post_meta($post_id, 'wpcom_multimage', true);
    $multimage = $multimage=='' ? (isset($options['list_multimage']) ? $options['list_multimage'] : 0) : $multimage;
    return $multimage;
}
