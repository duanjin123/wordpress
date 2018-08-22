<?php

add_action( 'wp_enqueue_scripts', 'QAPress_scripts', 20 );
function QAPress_scripts() {
    global $qa_options;
    if(!isset($qa_options)) $qa_options = get_option('qa_options');
    wp_enqueue_style( 'QAPress', QAPress_URI . 'css/style.css', array(), QAPress_VERSION );

    $color = isset($qa_options['color']) && $qa_options['color'] ? $qa_options['color'] : '#1471CA';
    $hover = isset($qa_options['color_hover']) && $qa_options['color_hover'] ? $qa_options['color_hover'] : '#0D62B3';
    $custom_css = "
        .q-content .topic-tab,.q-content .q-answer .as-user,.q-content .q-answer .as-comment-name,.profile-QAPress-tab .QAPress-tab-item{color: {$color};}
        .q-content .q-topic-wrap a:hover,.q-content .q-answer .as-action a:hover,.q-content .topic-tab:hover,.q-content .topic-title:hover{color:{$hover};}
        .q-content .put-top,.q-content .topic-tab.current-tab,.q-content .q-answer .as-submit .btn-submit,.q-content .q-answer .as-comments-submit,.q-content .q-add-header .btn-post,.q-content .q-pagination .current,.q-btn-new,.profile-QAPress-tab .QAPress-tab-item.active{background-color:{$color};}
        .q-content .q-answer .as-submit .btn-submit:hover,.q-content .q-answer .as-comments-submit:hover,.q-content .q-add-header .btn-post:hover,.q-content .topic-tab.current-tab:hover,.q-content .q-pagination a:hover,.q-btn-new:hover,.profile-QAPress-tab .QAPress-tab-item:hover{background-color:{$hover};}
        .q-content .q-answer .as-comments-input:focus,.profile-QAPress-tab .QAPress-tab-item{border-color: {$color};}
        .profile-QAPress-tab .QAPress-tab-item:hover{border-color: {$hover};}
        ";
    wp_add_inline_style( 'QAPress', $custom_css );
    // 载入js文件
    wp_enqueue_script( 'QAPress-js', QAPress_URI . 'js/scripts.min.js', array( 'jquery' ), QAPress_VERSION, true );

    wp_localize_script( 'QAPress-js', 'QAPress_js', array(
        'ajaxurl' => admin_url( 'admin-ajax.php'),
        'ajaxloading' => QAPress_URI . 'images/loading.gif'
    ) );
}

add_action( 'init', 'QAPress_register_category' );
function QAPress_register_category() {
    register_taxonomy( 'qa_cat', null,
        array(
            'labels' => array(
                'add_new_item' => '添加分类',
                'edit_item' => '编辑分类',
                'update_item' => '更新分类'
            ),
            'public' => false,
            'show_ui' => true,
            'label' => '问答分类',
            'hierarchical' => false,
        )
    );
}

add_action( 'admin_menu', 'QAPress_cat_menu');
function QAPress_cat_menu(){
    global $QAPress;
    if($QAPress->is_active())
        add_submenu_page('QAPress', '问答分类', '问答分类', 'edit_theme_options', 'edit-tags.php?taxonomy=qa_cat', null);
}

add_filter('manage_edit-qa_cat_columns', 'QAPress_remove_column' );
function QAPress_remove_column( $columns ){
    unset($columns['posts']);
    return $columns;
}

add_action('admin_head', 'QAPress_remove_cat_fileds');
function QAPress_remove_cat_fileds(){
    remove_all_actions( 'qa_cat_add_form_fields' );
    remove_all_actions( 'qa_cat_edit_form_fields' );
    remove_all_actions( 'created_qa_cat' );
    remove_all_actions( 'edited_qa_cat' );
}

add_filter( 'parent_file', 'QAPress_parent_file' );
function QAPress_parent_file( $parent_file='' ){
    global $pagenow;
    if ( !empty($_GET['taxonomy']) && ($_GET['taxonomy'] == 'qa_cat') && $pagenow == 'edit-tags.php' ) {
        $parent_file = 'QAPress';
    }
    return $parent_file;
}


function QAPress_format_date($time){
    $t = time() - $time;
    $f=array(
        '31536000'=>'年',
        '2592000'=>'个月',
        '604800'=>'星期',
        '86400'=>'天',
        '3600'=>'小时',
        '60'=>'分钟',
        '1'=>'秒'
    );
    if($t==0){
        return '1秒前';
    }
    foreach ($f as $k=>$v){
        if (0 !=$c=floor($t/(int)$k)) {
            return $c.$v.'前';
        }
    }
}

function QAPress_category( $cat, $cats=null ){
    if($cats){
        foreach ($cats as $c) {
            if($c->term_id==$cat) return $c->name;
        }
    }else{
        $c = get_term($cat, 'qa_cat');
        return $c->name;
    }
}

function QAPress_categorys(){
    // WP 4.5+
    $terms = get_terms( array(
            'taxonomy' => 'qa_cat',
            'hide_empty' => false
        )
    );

    return $terms;
}


add_filter( 'wp_title_parts', 'QAPress_title_parts', 5 );
function QAPress_title_parts( $parts ){
    global $qa_options, $current_cat;
    if(!isset($qa_options)) $qa_options = get_option('qa_options');
    if(is_page($qa_options['list_page'])){
        global $wp_query, $qa_single, $wpcomqadb;
        if(isset($wp_query->query['qa_id']) && $wp_query->query['qa_id']){
            $qa_single = $wpcomqadb->get_question($wp_query->query['qa_id']);
            $parts[] = $qa_single->title;
        }else if(isset($wp_query->query['qa_cat']) && $wp_query->query['qa_cat']){
            if(!$current_cat) $current_cat = get_term_by('slug', $wp_query->query['qa_cat'], 'qa_cat');
            $parts[] = $current_cat ? $current_cat->name : '';
        }

        if(isset($wp_query->query['qa_page']) && $wp_query->query['qa_page']){
            array_unshift($parts, '第'.$wp_query->query['qa_page'].'页');
        }
    }
    return $parts;
}

function QAPress_editor_settings($args = array()){
    $allow_img = isset($args['allow_img']) && $args['allow_img'] ? 1 : 0;
    return array(
        'textarea_name' => $args['textarea_name'],
        'media_buttons' => false,
        'quicktags' => false,
        'tinymce' => array(
            'statusbar' => false,
            'height'        => isset($args['height']) ? $args['height'] : 120,
            'toolbar1' => 'bold,italic,underline,blockquote,bullist,numlist'.($allow_img?',QAImg':''),
            'toolbar2' => '',
            'toolbar3' => ''
        )
    );
}

add_filter( 'mce_external_plugins', 'QAPress_mce_plugin');
function QAPress_mce_plugin($plugin_array){
    $plugin_array['QAImg'] = QAPress_URI . 'js/QAImg.min.js';
    return $plugin_array;
}

function QAPress_mail( $to, $subject, $content ){
    $html = '<p>亲爱的用户，您好！</p>';
    $html .= $content;
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($to, $subject, $html, $headers);
}


add_filter( 'wpcom_profile_tabs', 'QAPress_add_profile_tabs' );
function QAPress_add_profile_tabs( $tabs ){
    $tabs += array(
        25 => array(
            'slug' => 'questions',
            'title' => '问答'
        )
    );
    return $tabs;
}

add_action('wpcom_profile_tabs_questions', 'QAPress_questions');
function QAPress_questions() {
    global $profile, $wpcomqadb, $current_user;
    $all_cats = QAPress_categorys();
    $questions = $wpcomqadb->get_questions_by_user($profile->ID, 20, 1);
    $q_total = $wpcomqadb->get_questions_total_by_user($profile->ID);
    $q_numpages = ceil($q_total/20);

    $answers = $wpcomqadb->get_answers_by_user($profile->ID, 10, 1);
    $a_total = $wpcomqadb->get_answers_total_by_user($profile->ID);
    $a_numpages = ceil($a_total/10);

    $is_user = isset($current_user) && isset($current_user->ID) && $current_user->ID == $profile->ID;

    if($questions){
        $users_id = array();
        foreach($questions as $p){
            if(!in_array($p->user, $users_id)) $users_id[] = $p->user;
            if(!in_array($p->last_answer, $users_id)) $users_id[] = $p->last_answer;
        }

        $user_array = get_users(array('include'=>$users_id));
        $users = array();
        foreach($user_array as $u){
            $users[$u->ID] = $u;
        }
    }
    ?>
    <div class="profile-QAPress-tab" data-user="<?php echo $profile->ID;?>">
        <div class="QAPress-tab-item active">问题</div>
        <div class="QAPress-tab-item">回答</div>
    </div>
    <div class="profile-QAPress-content q-content active">
        <?php
        if($questions){
            foreach ($questions as $item) { ?>
                <div class="q-topic-item">
                    <div class="reply-count pull-left">
                        <span class="count-of-replies" title="回复数"><?php echo $item->answers;?></span>
                        <span class="count-seperator">/</span>
                        <span class="count-of-visits" title="点击数"><?php echo $item->views;?></span>
                    </div>
                    <div class="last-time pull-right">
                        <?php if($item->last_answer){ ?><a class="last-time-user" href="<?php echo QAPress_single_url($item->ID);?>#answer" target="_blank"><?php echo get_avatar( $item->last_answer, '60' );?></a> <?php } ?>
                        <span class="last-active-time"><?php echo QAPress_format_date(strtotime($item->modified));?></span>
                    </div>
                    <div class="topic-title-wrapper"><span class="topiclist-tab"><?php echo QAPress_category($item->category, $all_cats);?></span><a class="topic-title" href="<?php echo QAPress_single_url($item->ID);?>" title="<?php echo esc_attr($item->title);?>" target="_blank"><?php echo $item->title;?></a>
                    </div>
                </div>
            <?php } if($q_numpages>1) { ?>
                <div class="load-more-wrap"><a href="javascript:;" class="load-more j-user-questions">点击查看更多</a></div>
            <?php }
        }else{ ?>
            <div class="profile-no-content"><?php echo ($is_user?'你':'该用户');?>还没有发布过问题。</div>
        <?php } ?>
    </div>
    <?php if($answers){ ?><div class="profile-QAPress-content profile-comments-list">
        <?php foreach($answers as $answer){ $question = $wpcomqadb->get_question($answer->question);?>
            <div class="comment-item">
                <div class="comment-item-link">
                    <a target="_blank" href="<?php echo esc_url(QAPress_single_url($answer->question));?>#answer">
                        <i class="fa fa-comments"></i> <?php $excerpt = wp_trim_words( $answer->content, 100, '...' ); echo $excerpt ? $excerpt : '（过滤内容）' ?>
                    </a>
                </div>
                <div class="comment-item-meta">
                    <span><?php echo QAPress_format_date(strtotime($answer->date));?> 回答 <a target="_blank" href="<?php echo QAPress_single_url($answer->question);?>"><?php echo $question->title;?></a></span>
                </div>
            </div>
        <?php } if($a_numpages>1) { ?>
            <div class="load-more-wrap"><a href="javascript:;" class="load-more j-user-answers">点击查看更多</a></div>
        <?php } }else{ ?>
            <div class="profile-QAPress-content"><div class="profile-no-content"><?php echo ($is_user?'你':'该用户');?>还没有回答过问题。</div></div>
        <?php } ?>
    </div>
<?php }

add_action( 'wp_ajax_QAPress_user_questions', 'QAPress_user_questions' );
add_action( 'wp_ajax_nopriv_QAPress_user_questions', 'QAPress_user_questions' );
function QAPress_user_questions(){
    if( isset($_POST['user']) && is_numeric($_POST['user']) && $user = get_user_by('ID', $_POST['user'] ) ){
        global $wpcomqadb;
        $page = $_POST['page'];
        $page = $page ? $page : 1;
        $all_cats = QAPress_categorys();
        $questions = $wpcomqadb->get_questions_by_user($user->ID, 20, $page);
        if($questions){
            foreach($questions as $item){ ?>
                <div class="q-topic-item">
                    <div class="reply-count pull-left">
                        <span class="count-of-replies" title="回复数"><?php echo $item->answers;?></span>
                        <span class="count-seperator">/</span>
                        <span class="count-of-visits" title="点击数"><?php echo $item->views;?></span>
                    </div>
                    <div class="last-time pull-right">
                        <?php if($item->last_answer){ ?><a class="last-time-user" href="<?php echo QAPress_single_url($item->ID);?>#answer" target="_blank"><?php echo get_avatar( $item->last_answer, '60' );?></a> <?php } ?>
                        <span class="last-active-time"><?php echo QAPress_format_date(strtotime($item->modified));?></span>
                    </div>
                    <div class="topic-title-wrapper"><span class="topiclist-tab"><?php echo QAPress_category($item->category, $all_cats);?></span><a class="topic-title" href="<?php echo QAPress_single_url($item->ID);?>" title="<?php echo esc_attr($item->title);?>" target="_blank"><?php echo $item->title;?></a>
                    </div>
                </div>
            <?php }
        }else{ echo 0; }
    }
    exit;
}

add_action( 'wp_ajax_QAPress_user_answers', 'QAPress_user_answers' );
add_action( 'wp_ajax_nopriv_QAPress_user_answers', 'QAPress_user_answers' );
function QAPress_user_answers(){
    if( isset($_POST['user']) && is_numeric($_POST['user']) && $user = get_user_by('ID', $_POST['user'] ) ){
        global $wpcomqadb;
        $page = $_POST['page'];
        $page = $page ? $page : 1;
        $answers = $wpcomqadb->get_answers_by_user($user->ID, 10, $page);

        if($answers){
            foreach($answers as $answer){ $question = $wpcomqadb->get_question($answer->question);?>
                <div class="comment-item">
                    <div class="comment-item-link">
                        <a target="_blank" href="<?php echo esc_url(QAPress_single_url($answer->question));?>#answer">
                            <i class="fa fa-comments"></i> <?php $excerpt = wp_trim_words( $answer->content, 100, '...' ); echo $excerpt ? $excerpt : '（过滤内容）' ?>
                        </a>
                    </div>
                    <div class="comment-item-meta">
                        <span><?php echo QAPress_format_date(strtotime($answer->date));?> 回答 <a target="_blank" href="<?php echo QAPress_single_url($answer->question);?>"><?php echo $question->title;?></a></span>
                    </div>
                </div>
            <?php }
        }else{ echo 0; }
    }
    exit;
}
