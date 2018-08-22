<?php

class QAPress_SQL {
    function __construct(){
        global $wpdb, $QAPress;
        $this->db = $wpdb;
        $this->table_q = $wpdb->prefix.'wpcom_questions';
        $this->table_a = $wpdb->prefix.'wpcom_answers';
        $this->table_c = $wpdb->prefix.'wpcom_comments';

        add_action('activate_'. $QAPress->basename, array($this, 'create_sql_table'));
    }


    function create_sql_table(){

        $charset_collate = $this->db->get_charset_collate();
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // 问题表
        $create_questions_sql = "CREATE TABLE $this->table_q (".
            "ID BIGINT(20) NOT NULL auto_increment,".
            "user BIGINT(20) NOT NULL,".
            "date datetime NOT NULL,".
            "modified datetime,".
            "title text NOT NULL,".
            "content longtext NOT NULL,".
            "views BIGINT(20) NOT NULL DEFAULT 1,".
            "answers BIGINT(20) NOT NULL DEFAULT 0,".
            "category BIGINT(20) NOT NULL ,".
            "flag INT(11),".
            "last_answer BIGINT(20),".
            "PRIMARY KEY (ID)) $charset_collate;";

        dbDelta( $create_questions_sql );

        // 回答表
        $create_answers_sql = "CREATE TABLE $this->table_a (".
            "ID BIGINT(20) NOT NULL auto_increment,".
            "question BIGINT(20) NOT NULL,".
            "user BIGINT(20) NOT NULL,".
            "content longtext NOT NULL,".
            "date datetime NOT NULL,".
            "comments BIGINT(20) NOT NULL DEFAULT 0,".
            "PRIMARY KEY (ID)) $charset_collate;";

        dbDelta( $create_answers_sql );

        // 评论表
        $create_comment_sql = "CREATE TABLE $this->table_c (".
            "ID BIGINT(20) NOT NULL auto_increment,".
            "answer BIGINT(20) NOT NULL,".
            "user BIGINT(20) NOT NULL,".
            "content longtext NOT NULL,".
            "date datetime NOT NULL,".
            "PRIMARY KEY (ID)) $charset_collate;";

        dbDelta( $create_comment_sql );

        flush_rewrite_rules( true );
    }


    function get_questions_total( $cat=0 ){
        $sql = "SELECT COUNT(ID) FROM `$this->table_q`";
        if($cat) $sql .= " WHERE category='$cat'";
        return $this->db->get_var($sql);
    }

    function get_questions_total_by_user( $user=0 ){
        $sql = "SELECT COUNT(ID) FROM `$this->table_q`";
        if($user) $sql .= " WHERE user='$user'";
        return $this->db->get_var($sql);
    }

    function get_questions( $num=20, $paged=1, $cat=0 ){
        $sql = "SELECT * FROM `$this->table_q`";
        if($cat) $sql .= " WHERE category='$cat'";
        $offset = $num*($paged-1);
        $sql .= " ORDER BY `flag` DESC, modified DESC LIMIT $offset, $num";
        return $this->db->get_results($sql);
    }

    function get_questions_by_user( $user, $num=20, $paged=1 ){
        $sql = "SELECT * FROM `$this->table_q`";
        $sql .= " WHERE user='$user'";
        $offset = $num*($paged-1);
        $sql .= " ORDER BY modified DESC LIMIT $offset, $num";
        return $this->db->get_results($sql);
    }

    function get_question( $id ){
        if($id){
            return $this->db->get_row("SELECT * FROM `$this->table_q` WHERE ID = '$id'");
        }
    }

    function delete_question( $id ){
        if($id){
            return $this->db->delete($this->table_q, array('ID' => $id));
        }
    }

    function insert_question($question){
        if(isset($question['ID'])){
            $update = $this->db->update($this->table_q, $question, array('ID' => $question['ID'], 'user' => $question['user']));
            if($update) { //更新成功
                return $question['ID'];
            }else{
                return false;
            }
        }else{
            if($this->db->insert($this->table_q, $question)){ //插入成功
                return $this->db->insert_id;
            }else{
                return false;
            }
        }
    }

    function add_views($id){
        $question = $this->get_question($id);
        $views = $question->views ? $question->views + 1 : 1;
        $this->db->update( $this->table_q, array( 'views'=> $views), array( 'ID' => $id ), array('%d'), array('%d') );
        return $views;
    }

    function get_answers( $id, $num=20, $paged=1, $order='ASC' ){
        if($id){
            $offset = $num*($paged-1);
            return $this->db->get_results("SELECT * FROM `$this->table_a` WHERE `question` = '$id' ORDER BY `date` $order LIMIT $offset, $num");
        }
    }

    function get_answers_by_user( $user, $num=20, $paged=1, $order='DESC' ){
        if($user){
            $offset = $num*($paged-1);
            return $this->db->get_results("SELECT * FROM `$this->table_a` WHERE `user` = '$user' ORDER BY `date` $order LIMIT $offset, $num");
        }
    }

    function get_answers_total_by_user( $user ){
        if($user){
            return $this->db->get_var("SELECT COUNT(ID) FROM `$this->table_a` WHERE `user` = '$user'");
        }
    }

    function delete_answers( $question ){
        if($question){
            return $this->db->delete($this->table_a, array('question' => $question));
        }
    }

    function delete_answer( $id ){
        if($id){
            $question = $this->db->get_var("SELECT question FROM `$this->table_a` WHERE ID = '$id'");

            $this->db->delete($this->table_a, array('ID' => $id));

            if($question){
                $qss_total = $this->db->get_var("SELECT COUNT(ID) FROM `$this->table_a` WHERE question = '$question'");

                $answers = $this->get_answers($question, 1, 1, 'DESC');
                if($answers && isset($answers[0]->user)){
                    $last_answer = $answers[0]->user;
                }else{
                    $last_answer = null;
                }
                $this->db->update($this->table_q, array('answers' => $qss_total, 'last_answer' => $last_answer), array('ID' => $question));
            }
        }
    }

    function get_comments($id){
        return $this->db->get_results("SELECT * FROM `$this->table_c` WHERE `answer` = '$id' ORDER BY date ASC");
    }

    function delete_comments( $answer ){
        if($answer){
            return $this->db->delete($this->table_c, array('answer' => $answer));
        }
    }

    function delete_comment( $id ){
        if($id){
            $answer = $this->db->get_var("SELECT answer FROM `$this->table_c` WHERE ID = '$id'");
            $this->db->delete($this->table_c, array('ID' => $id));
            if($answer){
                $cms_total = $this->db->get_var("SELECT COUNT(ID) FROM `$this->table_c` WHERE answer = '$answer'");
                $this->db->update($this->table_a, array('comments' => $cms_total), array('ID' => $answer));
            }
        }
    }

    function insert_comment($comment){
        if($this->db->insert($this->table_c, $comment)){ //插入成功
            $id = $this->db->insert_id;
            $answer = $comment['answer'];
            $cms_total = $this->db->get_var("SELECT COUNT(ID) FROM `$this->table_c` WHERE answer = '$answer'");
            $this->db->update($this->table_a, array( 'comments' => $cms_total ), array('ID' => $comment['answer']));
            return $id;
        }else{
            return false;
        }
    }

    function insert_answer($answer){
        if($this->db->insert($this->table_a, $answer)){ //插入成功
            $id = $this->db->insert_id;
            $question = $answer['question'];
            $qss_total = $this->db->get_var("SELECT COUNT(ID) FROM `$this->table_a` WHERE question = '$question'");
            $this->db->update($this->table_q, array('answers' => $qss_total, 'modified' => date('Y-m-d H:i:s'), 'last_answer' => $answer['user']), array('ID' => $answer['question']));
            return $id;
        }else{
            return false;
        }
    }

    function set_top( $question ){
        if($question){
            $flag = $this->db->get_var("SELECT flag FROM `$this->table_q` WHERE ID = '$question'");
            $flag = $flag=='1' ? null : 1;
            return $this->db->update($this->table_q, array('flag'=>$flag ), array('ID' => $question));
        }
    }
}

$wpcomqadb = new QAPress_SQL();
