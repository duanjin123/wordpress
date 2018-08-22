<?php
if(is_home()){
    $sidebar = 'home';
}else if(is_page()){
    global $post;
    $sidebar = get_post_meta($post->ID, 'wpcom_sidebar', true);
}else if(is_category()){
    global $cat_options;
    $sidebar = isset($cat_options) && isset($cat_options['sidebar']) ? $cat_options['sidebar'] : get_option('cat_sidebar_'.$cat);
}else if(is_singular('post')){
    $category = get_the_category();
    $cat = $category[0]->cat_ID;
    $cat_options = get_option('_category_'.$cat);
    $sidebar = isset($cat_options['sidebar']) ? $cat_options['sidebar'] : get_option('cat_sidebar_'.$cat);
}else if(function_exists('is_woocommerce') && (is_post_type_archive( 'product' ) || is_woocommerce()) ){
    $sidebar = get_post_meta(wc_get_page_id( 'shop' ), 'wpcom_sidebar', true);
}else if(is_tag() || is_tax()){
    global $tax_options;
    if(!$tax_options) {
        $term = get_queried_object();
        $tax_options = get_option('_' . $term->taxonomy . '_' . $term->term_id);
    }
    $sidebar = isset($tax_options['sidebar']) ? $tax_options['sidebar'] : '';
}
$sidebar = isset($sidebar) && $sidebar ? $sidebar : 'primary';
dynamic_sidebar($sidebar);