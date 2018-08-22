<?php

class QAPress_AJAX {
    function __construct(){
        if (defined('DOING_AJAX') && DOING_AJAX && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){ //只接受ajax请求
            global $wpcomqadb;
            $this->qadb = $wpcomqadb;
            add_action('wp_ajax_QAPress_views', array($this, 'add_views'));
            add_action('wp_ajax_nopriv_QAPress_views', array($this, 'add_views'));

            add_action('wp_ajax_QAPress_answers_pagination', array($this, 'answers_pagination'));
            add_action('wp_ajax_nopriv_QAPress_answers_pagination', array($this, 'answers_pagination'));

            add_action('wp_ajax_QAPress_comments', array($this, 'get_comments'));
            add_action('wp_ajax_nopriv_QAPress_comments', array($this, 'get_comments'));

            add_action('wp_ajax_QAPress_add_comment', array($this, 'add_comment'));
            add_action('wp_ajax_nopriv_QAPress_add_comment', array($this, 'add_comment'));

            add_action('wp_ajax_QAPress_add_answer', array($this, 'add_answer'));
            add_action('wp_ajax_nopriv_QAPress_add_answer', array($this, 'add_answer'));

            add_action('wp_ajax_QAPress_add_question', array($this, 'add_question'));
            // add_action('wp_ajax_nopriv_QAPress_add_question', array($this, 'add_question'));

            add_action('wp_ajax_QAPress_img_upload', array($this, 'img_upload'));
            // add_action('wp_ajax_nopriv_QAPress_img_upload', array($this, 'img_upload'));

            add_action('wp_ajax_QAPress_delete_question', array($this, 'delete_question'));
            // add_action('wp_ajax_nopriv_QAPress_delete_question', array($this, 'delete_question'));

            add_action('wp_ajax_QAPress_set_top', array($this, 'set_top'));
            // add_action('wp_ajax_nopriv_QAPress_set_top', array($this, 'set_top'));

            add_action('wp_ajax_QAPress_delete_answer', array($this, 'delete_answer'));
            // add_action('wp_ajax_nopriv_QAPress_delete_answer', array($this, 'delete_answer'));

            add_action('wp_ajax_QAPress_delete_comment', array($this, 'delete_comment'));
            // add_action('wp_ajax_nopriv_QAPress_delete_comment', array($this, 'delete_comment'));
        }
    }

    function add_views(){
        $data = $_POST;
        if(isset($data['id']) && $data['id'] && is_numeric($data['id'])){
            echo $this->qadb->add_views($data['id']);
        }
        exit;
    }

    function answers_pagination(){
        global $wpcomqadb, $qa_options;
        $data = $_POST;
        $res = array();
        $res['result'] = 1;
        if(isset($data['page']) && isset($data['question']) && is_numeric($data['page']) && is_numeric($data['question'])){
            if(!isset($qa_options)) $qa_options = get_option('qa_options');

            $answers_order = isset($qa_options['answers_order']) && $qa_options['answers_order']=='1' ? 'DESC' : 'ASC';
            $answers_per_page = isset($qa_options['answers_per_page']) && $qa_options['answers_per_page'] ? $qa_options['answers_per_page'] : 20;
            $answers = $wpcomqadb->get_answers($data['question'], $answers_per_page, $data['page'], $answers_order);
            $res['result'] = 0;
            $res['answers'] = array();
            $res['delete'] = current_user_can( 'manage_options' ) ? 1 : 0;
            foreach ($answers as $answer) {
                $a = array();
                $a['ID'] = $answer->ID;
                $a['content'] = wpautop($answer->content);
                $a['time'] = QAPress_format_date(strtotime($answer->date));
                $a['avatar'] = get_avatar( $answer->user, '60' );
                $a['comments'] = $answer->comments;
                $user = get_user_by('ID', $answer->user);
                $author_name = $user->display_name ? $user->display_name : $user->user_nicename;
                if(class_exists('WPCOM_Member')){
                    $url = get_author_posts_url( $user->ID );
                    $author_name = '<a href="'.$url.'" target="_blank">'.$author_name.'</a>';
                }
                $a['name'] = $author_name;
                $res['answers'][] = $a;
            }
        }
        echo json_encode($res);
        exit;
    }

    function get_comments(){
        $data = $_POST;
        $res = array();
        if(isset($data['aid']) && $data['aid'] && is_numeric($data['aid'])){
            $comments = $this->qadb->get_comments($data['aid']);
            if($comments){
                $res_comments = array();
                foreach ($comments as $comment) {
                    $user = get_user_by('ID', $comment->user);
                    $author_name = $user->display_name ? $user->display_name : $user->user_nicename;
                    if(class_exists('WPCOM_Member')){
                        $url = get_author_posts_url( $user->ID );
                        $author_name = '<a class="as-comment-url" href="'.$url.'" target="_blank">'.$author_name.'</a>';
                    }
                    $comment->user = $author_name;
                    $res_comments[] = $comment;
                }
                $res['comments'] = $res_comments;
                $res['delete'] = current_user_can( 'manage_options' ) ? 1 : 0;
            }
            $res['result'] = 0;
        }else{
            $res['result'] = 1;
        }
        echo json_encode($res);
        exit;
    }

    
    function add_comment(){
        global $QAPress;
        $data = $_POST;
        $res = array();
        $user =  wp_get_current_user();
        if($user->ID && $QAPress->is_active()){
            $comment = array();
            $comment['answer'] = $data['id'];
            $comment['date'] = date('Y-m-d H:i:s');
            $comment['content'] = esc_html($data['comment']);
            $comment['user'] = $user->ID;

            if(trim(strip_tags($comment['content'])) == ''){
                $res['result'] = 101;
            }else{
                $id = $this->qadb->insert_comment($comment);
                if($id){
                    $comment['ID'] = $id;
                    $res['result'] = 0;
                    $res['comment'] = $comment;
                    $res['comment']->date = QAPress_format_date(strtotime($comment['date']));
                }else{
                    $res['result'] = 1;
                }
            }
        }else{
            $res['result'] = 2;
        }
        
        echo json_encode($res);
        exit;
    }


    function add_answer(){
        global $QAPress;
        $data = $_POST;
        $res = array();
        $user =  wp_get_current_user();
        if($user->ID && $QAPress->is_active()){ // 已登录
            $answer = array();
            $answer['question'] = $data['id'];
            $question = '';
            if($data['id'] && is_numeric($data['id'])) $question = $this->qadb->get_question($data['id']);;

            if($question){ // 问题存在
                $answer['date'] = date('Y-m-d H:i:s');
                $answer['content'] = wp_kses_post($data['answer']);
                $answer['user'] = $user->ID;

                if(trim($answer['content']) == ''){
                    $res['result'] = 101;
                }else{
                    $id = $this->qadb->insert_answer($answer);
                    if($id){
                        $answer['ID'] = $id;
                        $res['result'] = 0;
                        $res['answer'] = $answer;
                        $res['answer']['date'] = QAPress_format_date(strtotime($answer['date']));
                        $res['user'] = array();
                        $user_name = $user->display_name ? $user->display_name : $user->user_nicename;
                        $res['user']['nickname'] = $user_name;
                        $res['user']['avatar'] = get_avatar($user->ID, 60);

                        //邮件、通知信息，发送问题作者和管理员
                        $qa_options = get_option('qa_options');
                        $subject = isset($qa_options['email_answer_title']) && $qa_options['email_answer_title'] ? $qa_options['email_answer_title'] : '您的问题有了新回复';
                        $color = isset($qa_options['color']) && $qa_options['color'] ? $qa_options['color'] : '#1471CA';
                        $content = '<p>您的问题【'.$question->title.'】有了新回复：</p>';
                        $content .= '<div style="background: #f9f9f9;color:#666;padding: 10px 10px 10px 15px;border-left: 3px solid '.$color.';">'.$answer['content'].'</div>';
                        $content .= '<p>详情请访问：<a href="'.QAPress_single_url($question->ID).'">'.QAPress_single_url($question->ID).'</a></p>';

                        $quser = get_user_by('ID', $question->user);
                        if($quser->ID) QAPress_mail( $quser->data->user_email, $subject, $content );
                        $admin_email = get_bloginfo ('admin_email');
                        if($quser->email != $admin_email) QAPress_mail( $admin_email, $subject, $content );
                    }else{
                        $res['result'] = 1;
                    }
                }
            }else{ // 问题不存在
                $res['result'] = 4;
            }
        }else{ // 未登录
            $res['result'] = 2;
        }

        echo json_encode($res);
        exit;
    }

    function delete_answer(){
        global $QAPress;
        $res = array();
        if( current_user_can( 'manage_options' ) && $QAPress->is_active()){ // 管理员才能执行此操作
            if(isset($_POST['id']) && is_numeric($_POST['id'])){
                $id = $_POST['id'];
                // 删除回复的评论
                $this->qadb->delete_comments( $id );
                // 删除回复
                $this->qadb->delete_answer( $id );
                $res['result'] = 0;
            }else{
                $res['result'] = 1;
            }
        }else{
            $res['result'] = 2;
        }
        echo json_encode($res);
        exit;
    }

    function delete_comment(){
        global $QAPress;
        $res = array();
        if( current_user_can( 'manage_options' ) && $QAPress->is_active() ){ // 管理员才能执行此操作
            if(isset($_POST['id']) && is_numeric($_POST['id'])){
                $id = $_POST['id'];
                // 删除评论
                $this->qadb->delete_comment( $id );
                $res['result'] = 0;
            }else{
                $res['result'] = 1;
            }
        }else{
            $res['result'] = 2;
        }
        echo json_encode($res);
        exit;
    }

    function add_question(){
        global $QAPress;
        $res = array();
        $nonce = isset($_POST['add_question_nonce']) ? $_POST['add_question_nonce'] : '';

        // Check nonce
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'QAPress_add_question' ) ){
            $res['result'] = 1;
            echo json_encode($res);
            exit;
        }

        $user =  wp_get_current_user();
        if($user->ID && $QAPress->is_active()){
            $post = array();
            $qid = isset($_POST['id']) ? $_POST['id'] : 0;

            $question = $qid ? $this->qadb->get_question($qid) : null;

            if($question && ( $question->user==$user->ID || $user->has_cap( 'edit_others_posts' ) ) ) { // 问题存在，并比对用户权限
                $post['modified'] = date('Y-m-d H:i:s');
                $post['ID'] = $question->ID;
                $post['user'] = $question->user;
            }else{
                $post['date'] = date('Y-m-d H:i:s');
                $post['modified'] = $post['date'];
                $post['user'] = $user->ID;
                $post['views'] = 1;
                $post['answers'] = 0;
            }

            $post['title'] = strip_tags($_POST['title']);
            $post['content'] = wp_kses_post($_POST['content']);
            $post['category'] = $_POST['category'];

            if(trim($post['title']) == '' || trim($post['content']) == '' || trim($post['category']) == ''){
                $res['result'] = 101;
            }else if(mb_strlen(trim($post['content']))<10){ // 内容不能少于10个字符
                $res['result'] = 102;
            }else{
                $id = $this->qadb->insert_question($post);
                
                if($id){
                    if(!$qid){ // 新帖，非修改
                        $res['location'] = QAPress_single_url($id);
                        //邮件、通知信息，发送管理员
                        $qa_options = get_option('qa_options');
                        $subject = isset($qa_options['email_new_title']) && $qa_options['email_new_title'] ? $qa_options['email_new_title'] : '您的网站有新问题发布';
                        $color = isset($qa_options['color']) && $qa_options['color'] ? $qa_options['color'] : '#1471CA';
                        $content = '<p>您的网站有新问题发布：</p>';
                        $content .= '<div style="background: #f9f9f9;color:#666;padding: 10px 10px 10px 15px;border-left: 3px solid '.$color.';">'.$post['title'].'</div>';
                        $content .= '<p>详情请访问：<a href="'.QAPress_single_url($id).'">'.QAPress_single_url($id).'</a></p>';

                        QAPress_mail( get_bloginfo ('admin_email'), $subject, $content );
                    }
                    $res['result'] = 0;
                    $res['id'] = $id;
                }else{
                    $res['result'] = 1;
                }
            }
            
        }else{
            $res['result'] = 2;
        }

        echo json_encode($res);
        exit;
    }

    function delete_question(){
        global $QAPress;
        $res = array();
        if( current_user_can( 'manage_options' ) && $QAPress->is_active() ){ // 管理员才能执行此操作
            if(isset($_POST['id']) && is_numeric($_POST['id'])){
                $id = $_POST['id'];
                $answers = $this->qadb->get_answers($id, 9999);
                if($answers){
                    foreach ($answers as $answer) {
                        // 删除回复的评论
                        $this->qadb->delete_comments( $answer->ID );
                    }
                }
                // 删除回复
                $this->qadb->delete_answers( $id );
                $this->qadb->delete_question( $id );
                $res['result'] = 0;
            }else{
                $res['result'] = 1;
            }
        }else{
            $res['result'] = 2;
        }
        echo json_encode($res);
        exit;
    }

    function set_top(){
        $res = array();
        if( current_user_can( 'manage_options' ) ){ // 管理员才能执行此操作
            if(isset($_POST['id']) && is_numeric($_POST['id'])){
                $id = $_POST['id'];
                $this->qadb->set_top($id);
                $res['result'] = 0;
            }else{
                $res['result'] = 1;
            }
        }else{
            $res['result'] = 2;
        }
        echo json_encode($res);
        exit;
    }

    function img_upload(){
        $res = array();
        
        $user =  wp_get_current_user();
        if($user->ID){
            $upfile = $_FILES['upfile'];
            $upload_overrides = array('test_form' => false);
            $file_return = wp_handle_upload($upfile, $upload_overrides);

            if ($file_return && !isset($file_return['error'])) {
                // 保存到媒体库
                $attachment = array(
                    'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $file_return['file'] ) ),
                    'post_mime_type' => $file_return['type'],
                );
                $attach_id = wp_insert_attachment($attachment, $file_return['file']);
                $attach_data = self::generate_attachment_metadata($attach_id, $file_return['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                $res['result'] = 0;
                $file_return['alt'] = preg_replace( '/\.[^.]+$/', '', basename( $file_return['file'] ) );
                $res['image'] = $file_return;
            } else {
                $res['result'] = 1;
            }
        } else {
            $res['result'] = 2;
        }
        echo json_encode($res);
        exit;
    }

    function generate_attachment_metadata($attachment_id, $file) {
        $attachment = get_post ( $attachment_id );
        $metadata = array ();
        if (!function_exists('file_is_displayable_image')) include( ABSPATH . 'wp-admin/includes/image.php' );

        if (preg_match ( '!^image/!', get_post_mime_type ( $attachment ) ) && file_is_displayable_image ( $file )) {
            $imagesize = getimagesize ( $file );
            $metadata ['width'] = $imagesize [0];
            $metadata ['height'] = $imagesize [1];
            list ( $uwidth, $uheight ) = wp_constrain_dimensions ( $metadata ['width'], $metadata ['height'], 128, 96 );
            $metadata ['hwstring_small'] = "height='$uheight' width='$uwidth'";

            // Make the file path relative to the upload dir
            $metadata ['file'] = _wp_relative_upload_path ( $file );
            // work with some watermark plugin
            $metadata = apply_filters ( 'wp_generate_attachment_metadata', $metadata, $attachment_id );
        }
        return $metadata;
    }
}

new QAPress_AJAX();