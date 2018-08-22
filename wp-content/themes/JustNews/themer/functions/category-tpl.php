<?php
/**
 * 分类模板信息设置
 * 在分类添加和编辑页面新增模板选择选项
 */

function wpcom_category_tpl_options( $metas ){
    global $wpcom_panel;

    if($cat_tpl = (array)$wpcom_panel->get_category_tpl()) {
        $cat_tpl = array(''=>'默认模板') + $cat_tpl + apply_filters( 'wpcom_cat_tpl', array() );
        $metas['category'] = isset($metas['category']) && is_array($metas['category']) ? $metas['category'] : array();

        $metas['category'][] = array(
            'title' => '分类模板',
            'type' => 'select',
            'options' => $cat_tpl,
            'name' => 'tpl'
        );
    }
    return $metas;
}

// 分类列表选项
function add_cat_tpl_column( $columns ){
    $columns['tpl'] = '分类模板';
    return $columns;
}

// 分类列表选项 模板
function add_cat_tpl_column_content( $content, $column_name, $term_id ){

    if( $column_name !== 'tpl' ){
        return $content;
    }

    global $wpcom_panel, $cat_tpl;
    if(!$cat_tpl){
        $cat_tpl = $wpcom_panel->get_category_tpl();
    }

    $term_id = absint( $term_id );
    $cat_options = get_option('_category_'.$term_id);
    $val = isset($cat_options['tpl']) ? $cat_options['tpl'] : get_option('cat_tpl_'.$term_id);

    $content .= $val && isset($cat_tpl->{$val}) ? $cat_tpl->{$val} : '默认模板';

    return $content;
}

// 分类列表选项 排序
function add_cat_tpl_column_sortable( $sortable ){
    $sortable[ 'tpl' ] = 'tpl';
    return $sortable;
}

function category_posts_per_page( $query ) {
    if( $query->is_main_query() && is_category() && ! is_admin() ) {
        global $options;
        $cat_obj = $query->get_queried_object();
        $thisCat = $cat_obj->term_id;
        $cat_options = get_option('_category_'.$thisCat);
        $tpl = isset($cat_options['tpl']) ? $cat_options['tpl'] : get_option('cat_tpl_'.$thisCat);
        if($tpl && isset($options[$tpl.'_shows']) && $options[$tpl.'_shows']){
            $post_shows = $options[$tpl.'_shows'];
            $query->set( 'posts_per_page', $post_shows );
        }
    }
}

add_action('pre_get_posts', 'category_posts_per_page' );
add_filter('wpcom_tax_metas', 'wpcom_category_tpl_options');
add_filter('manage_edit-category_columns', 'add_cat_tpl_column' );
add_filter('manage_category_custom_column', 'add_cat_tpl_column_content', 10, 3 );
add_filter('manage_edit-category_sortable_columns', 'add_cat_tpl_column_sortable' );