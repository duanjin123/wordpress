<?php
/**
 * 加载静态资源
 */
if ( ! function_exists( 'wpcom_scripts' ) ) :
function wpcom_scripts() {
    global $options;
    // 载入主样式
    wp_enqueue_style( 'stylesheet', get_stylesheet_directory_uri() . '/style.css', array(), THEME_VERSION );

    // 替换自带的jQuery
    wp_deregister_script( 'jquery' );
    wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery.min.js', false, '1.11.3');

    // 载入js文件
    wp_enqueue_script( 'main', get_template_directory_uri() . '/js/main.js', array( 'jquery' ), THEME_VERSION, true );
    
    if ( is_singular() && isset($options['comments_open']) && $options['comments_open']=='1' && comments_open() && get_option( 'thread_comments' )) {
        wp_enqueue_script( 'comment-reply' );
    }
}
endif;
add_action( 'wp_enqueue_scripts', 'wpcom_scripts' );
/* 静态资源结束 */

load_theme_textdomain('wpcom', get_stylesheet_directory() . '/lang');