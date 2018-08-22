<?php
class WPCOM {
    private static $_render;
    private static $_preview;
    public static function get_post($id, $type='post'){
        if(is_numeric($id)){
            return get_post($id);
        }else{
            $args = array(
                'name'        => $id,
                'post_status' => 'any',
                'post_type' => $type,
                'posts_per_page' => 1
            );
            $my_posts = get_posts($args);
            if($my_posts) return $my_posts[0];
        }
    }

    public static function category( $tax = 'category' ){
        //$categories = get_categories(array('hide_empty' => 0));
        $categories = get_terms( array(
            'taxonomy' => $tax,
            'hide_empty' => false,
        ) );

        $cats = array();

        foreach ($categories as $cat){
            $cats[$cat->term_id] = $cat->name;
        }

        return $cats;
    }

    public static function register ( $wp_customize ) {
        global $wpdb;
        $values = $wpdb->get_results("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_page_modules'");
        $echo = new stdClass();
        foreach($values as $value){
            if($value){
                $echo->{$value->post_id} = maybe_unserialize($value->meta_value);
            }
        }
        $wp_customize->add_setting('_page_modules',
            array(
                'default' => $echo,
                'type' => 'page_modules',
                'capability' => 'edit_theme_options',
                'transport' => 'postMessage'
            )
        );

        add_action('admin_print_footer_scripts', array( 'WPCOM' , 'modules_options' ) );
    }

    public static function modules_options(){
        $cats = array();
        foreach(self::category() as $k=>$v){
            $cats[] = array(
                'id' => $k,
                'name' => $v
            );
        }

        $product_cat_json = '';

        if ( function_exists('is_woocommerce') ) {
            $product_cats = get_terms('product_cat', array('hide_empty' => 0));
            $pcats = array();
            foreach ($product_cats as $pcat) {
                $pcats[] = array(
                    'id' => $pcat->term_id,
                    'name' => $pcat->name
                );
            }
            $product_cat_json = function_exists('is_woocommerce') ? 'var _product_cat = '.wp_json_encode($pcats).';' : '';
        }

        echo '<script>var _category = '.wp_json_encode($cats).';var _modules = '.wp_json_encode(self::modules()).';'.$product_cat_json.'</script>';
    }

    public static function get_all_sliders(){
        $sliders = array();
        if(shortcode_exists("rev_slider")){
            $slider = new RevSlider();
            $revolution_sliders = $slider->getArrSliders();
            foreach ( $revolution_sliders as $revolution_slider ) {
                $alias = $revolution_slider->getAlias();
                $title = $revolution_slider->getTitle();
                $sliders[$alias] = $title.' ('.$alias.')';
            }
        }
        return $sliders;
    }

    public static function live_preview() {
        self::$_preview = 1;
        wp_enqueue_style("customizer", FRAMEWORK_URI."/assets/css/customizer.css", false, FRAMEWORK_VERSION, "all");
        wp_enqueue_script('wpcom-customizer', FRAMEWORK_URI . '/assets/js/customizer.js', array('jquery', 'customize-preview'), FRAMEWORK_VERSION, true );
    }

    public static function modules_preview($options){
        self::$_render = $options->post_value();
        if(self::$_render){
            return add_filter('get_post_metadata', array( 'WPCOM', 'mod_preview_filter' ), 10, 3);
        }
    }

    public static function mod_preview_filter($modules, $object_id, $meta_key){
        if($meta_key == '_page_modules'){
            $render = self::$_render;
            if(!$modules){
                $modules = array();
            }
            if(isset($render[$object_id])){
                $modules[] = $render[$object_id];
                return $modules;
            }
        }
    }

    public static function modules_update($options){
        foreach($options as $k=>$o){
            update_post_meta($k, '_page_modules', $o);
        }
    }

    public static function modules(){
        return apply_filters( 'wpcom_modules', new stdClass() );
    }

    public static function modules_setting(){
        include FRAMEWORK_PATH . '/html/modules.php';
        exit;
    }

    public static function modules_setting_head(){
        wp_enqueue_style("panel", FRAMEWORK_URI."/assets/css/modules.css", false, FRAMEWORK_VERSION, "all");
        wp_enqueue_style( 'wp-color-picker' );
        wp_styles()->do_items();
    }

    public static function modules_setting_foot(){
        wp_enqueue_script("angular", FRAMEWORK_URI."/assets/js/angular.min.js", array(), FRAMEWORK_VERSION, true);
        wp_enqueue_script("shortcodes", FRAMEWORK_URI."/assets/js/modules.js", array('wp-color-picker'), FRAMEWORK_VERSION, true);

        do_action( 'admin_footer' );
        do_action('admin_print_footer_scripts');
    }

    public static function wpdocs_dequeue_script(&$scripts) {
        $scripts->remove('jquery');
        $scripts->add('jquery', false, array('jquery-core'), FRAMEWORK_VERSION);
    }

    public static function editor_settings($args = array()){
        return array(
            'textarea_name' => $args['textarea_name'],
            'textarea_rows' => $args['textarea_rows'],
            'tinymce'       => array(
                'height'        => 150,
                'toolbar1' => 'formatselect,fontsizeselect,bold,blockquote,forecolor,alignleft,aligncenter,alignright,link,unlink,bullist,numlist,fullscreen,wp_help',
                'toolbar2' => '',
                'toolbar3' => '',
            )
        );
    }

    public static function render_page( $modules = null ){
        global $post;
        $render = $modules ? $modules : get_post_meta($post->ID, '_page_modules', true);
        if(!$render){
            $render = array();
        }
        if(self::$_preview==1) echo '<div class="wpcom-container">';
        if(is_array($render) && count($render)>0) {
            foreach ($render as $v) {
                $v['settings']['modules-id'] = $v['id'];
                do_action('wpcom_modules_' . $v['type'], $v['settings'], 0);
            }
        }else{
            echo '<div class="wpcom-inner"></div>';
        }
        if(self::$_preview==1) echo '</div>';
    }

    public static function load( $folder ){
        if($globs = glob( "{$folder}/*.php" ))
            foreach( $globs as $filename ) require_once $filename;
    }

    public static function thumbnail( $url, $width = null, $height = null, $crop = false, $img_id = 0, $size = '', $single = false, $upscale = true ) {
        /* WPML Fix */
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ){
            global $sitepress;
            $url = $sitepress->convert_url( $url, $sitepress->get_default_language() );
        }
        /* WPML Fix */

        $aq_resize = Aq_Resize::getInstance();
        return $aq_resize->process( $url, $width, $height, $crop, $img_id, $size, $single, $upscale );
    }

    public static function thumbnail_url($post_id='', $size='full'){
        global $post;
        if(!$post_id) $post_id = isset($post->ID) && $post->ID ? $post->ID : '';
        $img = get_the_post_thumbnail_url($post_id, $size);
        if( !$img ){
            if( !$post || $post->ID!=$post_id){
                $post = get_post($post_id);
            }
            preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', $post->post_content, $matches);
            if(isset($matches[1]) && isset($matches[1][0])) { // 文章有图片
                $img = $matches[1][0];
            }
        }
        return $img;
    }

    public static function thumbnail_html($html, $post_id, $post_thumbnail_id, $size){
        global $options;
        $image_sizes = apply_filters('wpcom_image_sizes', array());
        if(isset($image_sizes[$size])){
            $width = isset($image_sizes[$size]['width']) && $image_sizes[$size]['width'] ? $image_sizes[$size]['width'] : 480;
            $height = isset($image_sizes[$size]['height']) && $image_sizes[$size]['height'] ? $image_sizes[$size]['height'] : 320;
            $img_url = '';
            if(!$post_thumbnail_id){
                global $post;
                preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', $post->post_content, $matches);
                if(isset($matches[1]) && isset($matches[1][0])){ // 文章有图片
                    $img_url = $matches[1][0];
                    if( current_user_can( 'manage_options' ) ) {
                        $img_url = self::save_remote_img($img_url, $post);
                        if(is_array($img_url) && isset($img_url['id'])){
                            $post_thumbnail_id = $img_url['id'];
                            $img_url = $img_url['url'];
                        }
                    }

                    if(current_user_can( 'manage_options' ) && isset($options['auto_featured_image']) && $options['auto_featured_image']=='1'){
                        if(!$post_thumbnail_id) $post_thumbnail_id = self::get_attachment_id($img_url);
                        if($post_thumbnail_id) set_post_thumbnail($post->ID, $post_thumbnail_id);
                    }
                }
            }

            if($img_url) {
                $image = self::thumbnail($img_url, $width, $height, true, $post_thumbnail_id?$post_thumbnail_id:0, $size);
                if($image) {
                    if( !self::is_spider() && (!isset($options['thumb_img_lazyload']) || $options['thumb_img_lazyload']=='1') ) { // 非蜘蛛，并且开启了延迟加载
                        $lazy_img = isset($options['lazyload_img']) && $options['lazyload_img'] ? $options['lazyload_img'] : FRAMEWORK_URI.'/assets/images/lazy.png';
                        $lazy = self::thumbnail($lazy_img, $image_sizes[$size]['width'], $image_sizes[$size]['height'], true, 0, $size);
                        if($lazy && isset($lazy[0])) $lazy_img = $lazy[0];
                        $html = '<img class="j-lazy" src="'.$lazy_img.'" data-original="' . $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '" alt="' . esc_attr(get_the_title($post_id)) . '">';
                    } else {
                        $html = '<img src="' . $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '" alt="' . esc_attr(get_the_title($post_id)) . '">';
                    }
                }
            }
        }
        return $html;
    }

    public static function thumbnail_src($image, $attachment_id, $size, $icon){
        $image_sizes = apply_filters('wpcom_image_sizes', array());
        $res_image = '';
        if(!is_array($size) && isset($image_sizes[$size]) && (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX))){
            // 排除后台的ajax请求
            if( defined('DOING_AJAX') && DOING_AJAX && isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], '/wp-admin/')){
                return $image;
            }
            $res_image = self::thumbnail($image[0], $image_sizes[$size]['width'], $image_sizes[$size]['height'], true, $attachment_id, $size);
        }
        return $res_image ? $res_image : $image;
    }

    public static function thumbnail_attr($attr, $attachment, $size){
        global $options, $post;

        if( self::is_spider() || (isset($options['thumb_img_lazyload']) && $options['thumb_img_lazyload']=='0') ) {
            $attr['alt'] = $post->post_title ? $post->post_title : $attachment->post_title;
            return $attr;
        }

        $image_sizes = apply_filters('wpcom_image_sizes', array());
        if( (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) && !is_embed() ) {
            // 排除后台的ajax请求
            if( defined('DOING_AJAX') && DOING_AJAX && isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], '/wp-admin/')){
                return $attr;
            }

            $lazy_img = isset($options['lazyload_img']) && $options['lazyload_img'] ? $options['lazyload_img'] : FRAMEWORK_URI . '/assets/images/lazy.png';
            if( !is_array($size) && isset($image_sizes[$size]) ) {
                $lazy = self::thumbnail($lazy_img, $image_sizes[$size]['width'], $image_sizes[$size]['height'], true, 0, $size);
                if ($lazy && isset($lazy[0])) $lazy_img = $lazy[0];
            }
            $attr['data-original'] = $attr['src'];
            $attr['src'] = $lazy_img;
            $attr['class'] .= ' j-lazy';
            $attr['alt'] = $post->post_title ? $post->post_title : $attachment->post_title;
        }
        return $attr;
    }

    public static function check_post_images( $new_status, $old_status, $post ){
        global $options, $wpdb;
        if( $new_status!='publish' ) return false;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return false;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return false;

        if( (!isset($options['save_remote_img']) || !$options['save_remote_img']) && $post->post_type=='post' ) { // post 文章类型检查缩略图
            $post_thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
            if (!$post_thumbnail_id) {
                preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', $post->post_content, $matches);
                if (isset($matches[1]) && isset($matches[1][0])) {
                    $img_url = $matches[1][0];
                    self::save_remote_img($img_url, $post);
                }
            }
        }else if( isset($options['save_remote_img']) && isset($options['save_remote_img'])=='1' ) {
            set_time_limit(0);
            preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', $post->post_content, $matches);

            $search = array();
            $replace = array();
            if (isset($matches[1]) && isset($matches[1][0])) {
                $feature = 0;
                $post_thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);

                // 文章无特色图片，并开启了自动特色图片
                if($post->post_type=='post' && !$post_thumbnail_id && isset($options['auto_featured_image']) && $options['auto_featured_image']=='1') $feature = 1;

                // 去重
                $image_list = array();
                foreach ($matches[1] as $item) {
                    if( !in_array($item, $image_list) ) array_push($image_list, $item);
                }

                $i=0;
                foreach ($image_list as $img) {
                    $img_url = self::save_remote_img($img, $post, $i==0&&$feature);
                    if(is_array($img_url) && isset($img_url['id'])){
                        array_push($search, $img);
                        array_push($replace, $img_url['url']);
                    }
                    $i++;
                }

                if($search) {
                    $post->post_content = str_replace($search, $replace, $post->post_content);
                    // wp_update_post(array('ID' => $post->ID, 'post_content' => $post->post_content));
                    // wp_update_post会重复触发 transition_post_status hook
                    $data = array('post_content' => $post->post_content);
                    $data = wp_unslash( $data );
                    $wpdb->update( $wpdb->posts, $data, array( 'ID' => $post->ID ) );
                }
            }
        }
    }

    public static function save_remote_img($img_url, $post=null, $feature = 1){
        global $options;
        if(isset($options['remote_img_thumb']) && $options['remote_img_thumb']==0)
            return $img_url;

        $upload_info = wp_upload_dir();
        $upload_url = $upload_info['baseurl'];

        $http_prefix = "http://";
        $https_prefix = "https://";
        $relative_prefix = "//"; // The protocol-relative URL

        /* if the $url scheme differs from $upload_url scheme, make them match
           if the schemes differe, images don't show up. */
        if(!strncmp($img_url, $https_prefix,strlen($https_prefix))){ //if url begins with https:// make $upload_url begin with https:// as well
            $upload_url = str_replace($http_prefix, $https_prefix, $upload_url);
        }elseif(!strncmp($img_url, $http_prefix, strlen($http_prefix))){ //if url begins with http:// make $upload_url begin with http:// as well
            $upload_url = str_replace($https_prefix, $http_prefix, $upload_url);
        }elseif(!strncmp($img_url, $relative_prefix, strlen($relative_prefix))){ //if url begins with // make $upload_url begin with // as well
            $upload_url = str_replace(array( 0 => "$http_prefix", 1 => "$https_prefix"), $relative_prefix, $upload_url);
        }

        // Check if $img_url is local.
        if ( false === strpos( $img_url, $upload_url ) ){ // 外链图片
            //Fetch and Store the Image
            $http_options = array(
                'httpversion' => '1.1',
                'timeout' => 30,
                'redirection' => 20,
                'sslverify' => FALSE,
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
            );
            $img_url =  wp_specialchars_decode($img_url);
            $get = wp_remote_head( $img_url, $http_options );
            $response_code = wp_remote_retrieve_response_code ( $get );

            if (200 == $response_code) { // 图片状态需为 200
                $type = $get ['headers'] ['content-type'];

                $mime_to_ext = array (
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/bmp' => 'bmp',
                    'image/tiff' => 'tif'
                );

                $file_ext = isset($mime_to_ext[$type]) ? $mime_to_ext[$type] : '';

                $allowed_filetype = array('jpg','gif','png', 'bmp');

                if (in_array ( $file_ext, $allowed_filetype )) { // 仅保存图片格式 'jpg','gif','png', 'bmp'
                    $http = wp_remote_get ( $img_url, $http_options );
                    if (!is_wp_error ( $http ) && 200 === $http ['response'] ['code']) { // 请求成功
                        if( (preg_match('/\/\/mmbiz\.qlogo\.cn/i', $img_url) || preg_match('/\/\/mmbiz\.qpic\.cn/i', $img_url) ) && function_exists('file_get_contents') ){ // 微信公众号图片，使用file_get_contents获取
                            $img_url = str_replace('tp=webp', 'tp='.$file_ext, $img_url);
                            $http ['body'] = file_get_contents($img_url);
                        }
                        $filename = rawurldecode(wp_basename($img_url));
                        $ext = substr(strrchr($filename, '.'), 1);
                        $filename = wp_basename($filename, "." . $ext) . '.' . $file_ext;

                        $time = $post ? date('Y/m', strtotime($post->post_date)) : date('Y/m');
                        $mirror = wp_upload_bits($filename, '', $http ['body'], $time);

                        // 保存到媒体库
                        $attachment = array(
                            'post_title' => preg_replace( '/\.[^.]+$/', '', wp_basename($img_url) ),
                            'post_mime_type' => $type,
                            'guid' => $mirror['url']
                        );

                        $attach_id = wp_insert_attachment($attachment, $mirror['file'], $post?$post->ID:0);

                        if($attach_id) {
                            $attach_data = self::generate_attachment_metadata($attach_id, $mirror['file']);
                            wp_update_attachment_metadata($attach_id, $attach_data);

                            if ($post && $feature) {
                                // 设置文章特色图片
                                set_post_thumbnail($post->ID, $attach_id);
                            }

                            $img_url = array(
                                'id' => $attach_id,
                                'url' => $mirror['url']
                            );
                        }else{ // 保存到数据库失败，则删除图片
                            @unlink($mirror['file']);
                        }
                    }
                }
            }
        }

        return $img_url;
    }

    public function get_attachment_id( $url ) {
        $attachment_id = 0;
        $dir = wp_upload_dir();
        if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?
            $file = basename( $url );
            $query_args = array(
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'fields'      => 'ids',
                'meta_query'  => array(
                    array(
                        'value'   => $file,
                        'compare' => 'LIKE',
                        'key'     => '_wp_attachment_metadata',
                    ),
                )
            );
            $query = new WP_Query( $query_args );
            if ( $query->have_posts() ) {
                foreach ( $query->posts as $post_id ) {
                    $meta = wp_get_attachment_metadata( $post_id );
                    $original_file       = basename( $meta['file'] );
                    $cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
                    if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
                        $attachment_id = $post_id;
                        break;
                    }
                }
            }
        }
        return $attachment_id;
    }

    public static function generate_attachment_metadata($attachment_id, $file) {
        $attachment = get_post ( $attachment_id );
        $metadata = array ();
        if (!function_exists('file_is_displayable_image')) include( ABSPATH . 'wp-admin/includes/image.php' );
        if (preg_match ( '!^image/!', get_post_mime_type ( $attachment ) ) && file_is_displayable_image ( $file )) {
            $imagesize = getimagesize ( $file );
            $metadata ['width'] = $imagesize [0];
            $metadata ['height'] = $imagesize [1];

            // Make the file path relative to the upload dir
            $metadata ['file'] = _wp_relative_upload_path ( $file );

            // Fetch additional metadata from EXIF/IPTC.
            $image_meta = wp_read_image_metadata( $file );
            if ( $image_meta )
                $metadata['image_meta'] = $image_meta;

            // work with some watermark plugin
            $metadata = apply_filters ( 'wp_generate_attachment_metadata', $metadata, $attachment_id );
        }
        return $metadata;
    }

    public static function reg_module( $module ){
        add_action('wpcom_modules_'.$module, 'wpcom_modules_'.$module, 10, 2);
        add_filter('wpcom_modules', 'wpcom_'.$module);
    }

    public static function modules_style(){
        if( is_singular() && is_page_template('page-home.php') ) {
            global $post;
            $modules = get_post_meta($post->ID, '_page_modules', true);
            if( !$modules ){
                $modules = array();
            }
        }else if( is_home() && function_exists('get_default_mods') ){
            $modules = get_default_mods();
        }

        if( isset($modules) && is_array($modules) && $modules ) {
            global $wpcom_modules;
            ob_start();
            if ( count($modules) > 0 ) {
                foreach ($modules as $v) {
                    if (isset($wpcom_modules[$v['type']])) {
                        $v['settings']['modules-id'] = $v['id'];
                        $wpcom_modules[$v['type']]->style($v['settings']);
                    }
                    // 例如全宽模块下会有子模块
                    if ($v['settings'] && isset($v['settings']['modules']) && $v['settings']['modules']) {
                        foreach ($v['settings']['modules'] as $m) {
                            if (isset($wpcom_modules[$m['type']])) {
                                $m['settings']['modules-id'] = $m['id'];
                                $wpcom_modules[$m['type']]->style($m['settings']);
                            }
                            // 例如全宽模块下可添加栅格模块，栅格模块下面还可以放子模块，目前最多就3层
                            if ($m['settings'] && isset($m['settings']['modules']) && $m['settings']['modules']) {
                                foreach ($m['settings']['modules'] as $s) {
                                    if (isset($wpcom_modules[$s['type']])) {
                                        $s['settings']['modules-id'] = $s['id'];
                                        $wpcom_modules[$s['type']]->style($s['settings']);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $styles = ob_get_contents();
            ob_end_clean();

            if ( $styles != '' ) echo '<style>' . $styles . '</style>';
        }
    }

    public static function color( $color, $rgb = false ){
        if($rgb){
            $color = str_replace('#', '', $color);
            if (strlen($color) > 3) {
                $rgb = array(
                    'r' => hexdec(substr($color, 0, 2)),
                    'g' => hexdec(substr($color, 2, 2)),
                    'b' => hexdec(substr($color, 4, 2))
                );
            } else {
                $r = substr($color, 0, 1) . substr($color, 0, 1);
                $g = substr($color, 1, 1) . substr($color, 1, 1);
                $b = substr($color, 2, 1) . substr($color, 2, 1);
                $rgb = array(
                    'r' => hexdec($r),
                    'g' => hexdec($g),
                    'b' => hexdec($b)
                );
            }
            return $rgb;
        }else{
            if(strlen($color) && substr($color, 0, 1)!='#'){
                $color = '#'.$color;
            }
            return $color;
        }
    }

    public static function shortcode_render(){
        $shortcodes = array('btn', 'gird', 'icon', 'alert', 'panel', 'tabs', 'accordion', 'map');
        foreach($shortcodes as $sc){
            add_shortcode($sc, 'wpcom_sc_'.$sc);
        }
    }

    public static function is_spider() {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $spiders = array(
            'Googlebot', // Google
            'Baiduspider', // 百度
            '360Spider', // 360
            'bingbot', // Bing
            'Sogou web spider' // 搜狗
        );

        foreach ($spiders as $spider) {
            $spider = strtolower($spider);
            //查找有没有出现过
            if (strpos($userAgent, $spider) !== false) {
                return $spider;
            }
        }
    }
}

// Setup the Theme Customizer settings and controls...
add_action( 'customize_register' , array( 'WPCOM' , 'register' ) );

// Enqueue live preview javascript in Theme Customizer admin screen
add_action( 'customize_preview_init' , array( 'WPCOM' , 'live_preview' ) );

add_action( 'wpcom_render_page', array( 'WPCOM' , 'render_page' ) );

add_action( 'wp_ajax_wpcom_modules', array('WPCOM', 'modules_setting') );

add_action( 'customize_preview_page_modules', array('WPCOM', 'modules_preview') );
add_action( 'customize_update_page_modules', array('WPCOM', 'modules_update') );

add_filter( 'post_thumbnail_html', array('WPCOM', 'thumbnail_html'), 10, 4 );
add_filter( 'wp_get_attachment_image_src', array('WPCOM', 'thumbnail_src'), 10, 4 );
add_filter( 'wp_get_attachment_image_attributes', array('WPCOM', 'thumbnail_attr'), 10, 3 );

add_action( 'init', array('WPCOM', 'shortcode_render') );
add_action( 'transition_post_status', array('WPCOM', 'check_post_images'), 10, 3 );
add_action( 'wp_head', array( 'WPCOM', 'modules_style' ), 30 );


if(isset($_SERVER["REQUEST_URI"]) && preg_match("/action=wpcom_modules/i", $_SERVER["REQUEST_URI"])){
    add_filter( 'wp_default_scripts', array('WPCOM', 'wpdocs_dequeue_script') );
}

$tpl_dir = get_template_directory();
$sty_dir = get_stylesheet_directory();

require FRAMEWORK_PATH . '/core/panel.php';
require FRAMEWORK_PATH . '/core/module.php';

WPCOM::load(FRAMEWORK_PATH . '/functions');
WPCOM::load(FRAMEWORK_PATH . '/widgets');
WPCOM::load(FRAMEWORK_PATH . '/modules');
WPCOM::load($tpl_dir . '/modules');
if($tpl_dir !== $sty_dir) {
    WPCOM::load($sty_dir . '/modules');
}