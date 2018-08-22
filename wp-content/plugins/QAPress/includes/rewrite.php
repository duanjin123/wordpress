<?php
add_filter( 'rewrite_rules_array','QAPress_rewrite' );
function QAPress_rewrite( $rules ){
    global $qa_slug, $qa_options;
    if(!isset($qa_options)) $qa_options = get_option('qa_options');
    
    $qa_page_id = $qa_options['list_page'];

    if($qa_slug==''){
        $qa_page = get_post($qa_page_id);
        $qa_slug = $qa_page->post_name;
    }

    $newrules = array();
    $newrules[$qa_slug.'/(\d+)\.html$'] = 'index.php?page_id='.$qa_page_id.'&qa_id=$matches[1]';
    $newrules[$qa_slug.'/(\d+)?$'] = 'index.php?page_id='.$qa_page_id.'&qa_page=$matches[1]';
    $newrules[$qa_slug.'/([^/]+)?$'] = 'index.php?page_id='.$qa_page_id.'&qa_cat=$matches[1]';
    $newrules[$qa_slug.'/([^/]+)/(\d+)?$'] = 'index.php?page_id='.$qa_page_id.'&qa_cat=$matches[1]&qa_page=$matches[2]';

    return $newrules + $rules;
}

add_filter('query_vars', 'QAPress_query_vars', 10, 1 );
function QAPress_query_vars($public_query_vars) {
    $public_query_vars[] = 'qa_id';
    $public_query_vars[] = 'qa_page';
    $public_query_vars[] = 'qa_cat';

    return $public_query_vars;
}

add_filter('user_trailingslashit', 'QAPress_untrailingslashit', 10, 2);
function QAPress_untrailingslashit($string, $url){
    if($url == 'page' && preg_match('/\.html\/$/i', $string)){
        return untrailingslashit($string);
    }
    return $string;
}

function QAPress_category_url($cat, $page=1){
    global $permalink_structure, $wp_rewrite, $qa_options;
    if(!isset($qa_options)) $qa_options = get_option('qa_options');
    if(!isset($permalink_structure)) $permalink_structure = get_option('permalink_structure');
    
    $qa_page_id = $qa_options['list_page'];

    $page_url = get_permalink($qa_page_id);

    if($permalink_structure){
        $url = trailingslashit($page_url).$cat;
        if($page>1){
            $url = trailingslashit($url).$page;
        }
    }else{
        $url =  $cat ? add_query_arg('qa_cat', $cat, $page_url) : $page_url;
        if($page>1){
            $url = add_query_arg('qa_page', $page, $url);
        }
    }

    if ( $wp_rewrite->use_trailing_slashes )
        $url = trailingslashit($url);
    else
        $url = untrailingslashit($url);

    return $url;
}

function QAPress_single_url( $id ){
    global $permalink_structure, $qa_options;
    if(!isset($qa_options)) $qa_options = get_option('qa_options');
    if(!isset($permalink_structure)) $permalink_structure = get_option('permalink_structure');
    
    $qa_page_id = $qa_options['list_page'];

    $page_url = get_permalink($qa_page_id);
    
    if($permalink_structure){
        $url = trailingslashit($page_url).$id.'.html';
    }else{
        $url =  add_query_arg('qa_id', $id, $page_url);
    }
    return $url;
}

function QAPress_edit_url( $qid ){
    global $qa_options;
    if(!isset($qa_options)) $qa_options = get_option('qa_options');
    $new_page_id = $qa_options['new_page'];

    $edit_url = get_permalink($new_page_id);

    $edit_url =  add_query_arg('type', 'edit', $edit_url);
    $edit_url =  add_query_arg('id', $qid, $edit_url);

    return $edit_url;
}

add_action( 'template_redirect', 'QAPress_404_page', 1 );
function QAPress_404_page() {
    global $wp_query, $qa_single, $wpcomqadb, $qa_options, $current_cat;
    if(!isset($qa_options)) $qa_options = get_option('qa_options');

    if(isset($wp_query->query['qa_id']) && $wp_query->query['qa_id'] && is_page($qa_options['list_page'])){
        $qa_single = $wpcomqadb->get_question($wp_query->query['qa_id']);

        if( ! ( $qa_single && isset($qa_single->ID) ) ){
            $wp_query->set_404();
            status_header(404);
            get_template_part( 404 );
            exit();
        }
    }else if(isset($wp_query->query['qa_cat']) && $wp_query->query['qa_cat']){
        if(!$current_cat) $current_cat = get_term_by('slug', $wp_query->query['qa_cat'], 'qa_cat');
        if(!$current_cat){
            $wp_query->set_404();
            status_header(404);
            get_template_part( 404 );
            exit();
        }
    }

    if(isset($wp_query->query['qa_page']) && $wp_query->query['qa_page']){
        $total_q = $wpcomqadb->get_questions_total( isset($current_cat) ? $current_cat->term_id : 0 );
        $per_page = isset($qa_options['question_per_page']) && $qa_options['question_per_page'] ? $qa_options['question_per_page'] : 20;

        $numpages = ceil($total_q/$per_page);
        if($wp_query->query['qa_page']>$numpages){
            $wp_query->set_404();
            status_header(404);
            get_template_part( 404 );
            exit();
        }
    }
}