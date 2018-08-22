<?php

class WPCOM_Panel{
    private $settings;
    private $updateName;
    protected $automaticCheckDone = false;

    public function __construct() {
        $this->options = $this->get_theme_options();
        $GLOBALS['options'] = $this->options;
        $this->init_hooks();
    }

    private function init_hooks(){
        add_action('admin_init', array($this, 'panel_init'));
        add_action('admin_menu', array($this, 'panel_menu'));
        add_action('wp_ajax_wpcom_panel', array($this, 'panel_callback'));
        add_action('wp_ajax_wpcom_check_version', array($this, 'check_version'));
        add_action('wp_ajax_wpcom_post_fun', array($this, 'post_callback'));
        add_action('wp_ajax_nopriv_wpcom_post_fun', array($this, 'post_callback'));
        add_action('wp_ajax_wpcom_demo_export', array($this, 'theme_options_demo_export'));

        $this->updateName = 'theme_update_'.THEME_ID;

        add_action('delete_site_transient_update_themes', array($this, 'updated'));
    }

    public function panel_menu() {
        if(function_exists('add_menu_page')) {
            $this->settings = $this->get_panel_settings();

            if( $this->settings && get_option('izt_theme_token') ){
                $wpcom_settings = apply_filters( 'wpcom_settings', array() );
                if(!empty($wpcom_settings)) array_splice($this->settings, -1, 0, json_decode(json_encode($wpcom_settings)));

                add_menu_page('主题设置', '主题设置', 'edit_theme_options', 'wpcom-panel', array( $this, 'panel_admin' ), 'dashicons-art');
            }else{
                add_menu_page('主题激活', '主题激活', 'edit_theme_options', 'wpcom-panel', array( $this, 'panel_active' ), 'dashicons-admin-network');
            }
        }
    }

    public function panel_init() {

        if ( get_option('izt_theme_token') && (isset($this->options['auto_check_update']) && $this->options['auto_check_update']=='1' ) || !isset($this->options['auto_check_update'])){
            add_filter('pre_set_site_transient_update_themes', array($this, 'check_update'));
        }else{
            delete_option($this->updateName);
        }

        if (is_admin() && isset($_GET['page']) && ( $_GET['page'] == 'wpcom-panel' ) ){

            // Load CSS
            wp_enqueue_style("panel", FRAMEWORK_URI."/assets/css/panel.css", false, FRAMEWORK_VERSION, "all");
            wp_enqueue_style( 'wp-color-picker' );

            // Load JS
            wp_enqueue_script("panel", FRAMEWORK_URI."/assets/js/panel.js", array('jquery', 'jquery-ui-core', 'wp-color-picker'), FRAMEWORK_VERSION, true);
            wp_enqueue_media();
        }
    }

    public function panel_admin(){
        ?>
        <div class="wrap" id="wpcom-panel">
            <form class="form-horizontal" id="wpcom-panel-form" method="post" action="">
                <?php wp_nonce_field( 'wpcom_theme_options', 'wpcom_theme_options_nonce' ); ?>
                <div id="wpcom-panel-header" class="clearfix">
                    <div class="logo pull-left">
                        <h3 class="panel-title">主题设置<small><?php echo $this->get_current_theme();?></small></h3>
                    </div>
                    <div class="pull-right">
                        <?php echo apply_filters('wpcom_panel_docs_link', '<a class="button" target="_blank" href="https://www.wpcom.cn/docs"><i class="fa fa-file-text-o"></i> 使用文档</a>'); ?>
                    </div>
                </div><!--#wpcom-panel-header-->

                <div id="wpcom-panel-main">
                    <ul id="wpcom-panel-nav" class="nav nav-pills nav-stacked" role="tablist">
                        <?php $this->get_menu();?>
                    </ul>

                    <div id="wpcom-panel-content" class="tab-content">
                        <?php $this->get_tab();?>
                    </div>
                </div><!--#wpcom-panel-main-->

                <div class="wpcom-panel-save clearfix">
                    <div class="col-xs-7" id="alert-info"></div>
                    <div class="col-xs-5 wpcom-panel-btn">
                        <button id="wpcom-panel-reset" type="button" data-loading-text="正在重置..."class="button submit-button reset-button">重置设置</button>
                        <button id="wpcom-panel-submit" type="button"  data-loading-text="正在保存..." class="button button-primary">保存设置</button>
                    </div>
                </div><!--.wpcom-panel-save-->
            </form>
        </div><!--.wrap-->
    <?php }

    public function panel_active(){
        if(isset($_POST['email'])){
            $email = trim($_POST['email']);
            $token = trim($_POST['token']);
            $err = false;
            if($email==''){
                $err = true;
                $err_email = '登录邮箱不能为空';
            }else if(!is_email( $email )){
                $err = true;
                $err_email = '登录邮箱格式不正确';
            }
            if($token==''){
                $err = true;
                $err_token = '激活码不能为空';
            }else if(strlen($token)!=32){
                $err = true;
                $err_token = '激活码不正确';
            }
            if($err==false){
                update_option( "izt_theme_email", $email );
                update_option( "izt_theme_token", wp_hash_password($token) );

                $body = array('email'=>$email, 'token'=>$token, 'version'=>THEME_VERSION, 'home'=>get_option('siteurl'), 'themer' => FRAMEWORK_VERSION);
                $result_body = json_decode($this->send_request('active', $body));
                if($result_body->result=='0'||$result_body->result=='1'){
                    $active = $result_body;
                    echo '<meta http-equiv="refresh" content="0">';
                }else if(isset($result_body->result)){
                    $active = $result_body;
                }else{
                    $active = new stdClass();
                    $active->result = 10;
                    $active->msg = '激活失败，请稍后再试！';
                }
            }
        }
        ?>
        <div class="wrap" id="wpcom-panel">
            <form class="form-horizontal" id="wpcom-panel-form" method="post" action="">
                <div id="wpcom-panel-header" class="clearfix">
                    <div class="logo pull-left">
                        <h3 class="panel-title">主题激活<small><?php echo $this->get_current_theme();?></small></h3>
                    </div>
                </div><!--#wpcom-panel-header-->

                <div id="wpcom-panel-main" class="clearfix">
                    <div class="form-horizontal" style="width:400px;margin:80px auto;">
                        <?php if (isset($active)) { ?><p class="col-xs-offset-3 col-xs-9" style="<?php echo ($active->result==0||$active->result==1?'color:green;':'color:#F33A3A;');?>"><?php echo $active->msg; ?></p><?php } ?>
                        <div class="form-group">
                            <label for="email" class="col-xs-3 control-label">登录邮箱</label>
                            <div class="col-xs-9">
                                <input type="email" name="email" class="form-control" id="email" value="<?php echo isset($email)?$email:''; ?>" placeholder="请输入WPCOM登录邮箱">
                                <?php if(isset($err_email)){ ?><div class="j-msg" style="color:#F33A3A;font-size:12px;margin-top:3px;margin-left:3px;"><?php echo $err_email;?></div><?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="token" class="col-xs-3 control-label">激活码</label>
                            <div class="col-xs-9">
                                <input type="text" name="token" class="form-control" id="token" value="<?php echo isset($token)?$token:'';?>" placeholder="请输入主题激活码" autocomplete="off">
                                <?php if(isset($err_token)){ ?><div class="j-msg" style="color:#F33A3A;font-size:12px;margin-top:3px;margin-left:3px;"><?php echo $err_token;?></div><?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-3 control-label"></label>
                            <div class="col-xs-9">
                                <input type="submit" class="button button-primary" value="提 交">
                            </div>
                        </div>
                    </div>
                </div><!--#wpcom-panel-main-->
            </form>
        </div><!--.wrap-->
        <script>(function($){$('.form-control').focus(function(){$(this).next('.j-msg').hide();});})(jQuery);</script>
    <?php
    }

    public function panel_callback(){
        $post = isset($_POST['data']) ? $_POST['data'] : '';
        parse_str($post, $data);

        if ( ! isset( $data['wpcom_theme_options_nonce'] ) )
            return ;

        $nonce = $data['wpcom_theme_options_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'wpcom_theme_options' ) )
            return ;

        unset($data['wpcom_theme_options_nonce']);
        unset($data['_wp_http_referer']);

        // Delete theme options
        if(isset($data['reset'])&&$data['reset']==true){

            // Delete `reset` from array
            unset($data['reset']);

            // Return html
            if($this->remove_theme_options( $data )){
                $output = array(
                    'errcode' => 0,
                    'errmsg' => '重置成功，主题设置信息已恢复初始状态~'
                );
            }else{
                $save = false;
                foreach($data as $key => $value){
                    if( isset($this->options[$key]) && $this->options[$key]!=$value ){
                        $save = true;
                    }
                }
                if($save==false){
                    $output = array(
                        'errcode' => 1,
                        'errmsg' => '已经是初始状态了，不需要重置了~'
                    );
                }else{
                    $output = array(
                        'errcode' => 2,
                        'errmsg' => '重置失败，请稍后再试！'
                    );
                }
            }
            echo wp_json_encode($output);
            die();
        }

        if($this->set_theme_options( $data )){
            $output = array(
                'errcode' => 0,
                'errmsg' => '设置保存成功~'
            );
        }else{
            $save = false;
            foreach($data as $key => $value){
                if( isset($this->options[$key]) && $this->options[$key]!=$value ){
                    $save = true;
                }
            }
            if($save==false){
                $output = array(
                    'errcode' => 1,
                    'errmsg' => '额，你好像什么也没改呢？'
                );
            }else{
                $output = array(
                    'errcode' => 2,
                    'errmsg' => 'Sorry~ 提交失败了，可以再提交一次试试~'
                );
            }
        }
        echo wp_json_encode($output);
        die();
    }

    public function post_callback(){
        $post = $_POST;
        $token = get_option('izt_theme_token');

        $data = isset($post['data']) ? $post['data'] : '';
        $data = maybe_unserialize(stripcslashes($data));

        if(!$data){
            echo 'Data error';
            exit;
        }

        if(!wp_check_password($data['token'], $token)){
            echo 'Token error';
            exit;
        }

        if(isset($data['options'])) {
            $settings = json_encode($data['options']);
            $settings = base64_encode(md5(THEME_ID) . md5($token) . base64_encode($settings));

            update_option("izt_" . THEME_ID . "_panel", $settings);
        }else if(isset($data['package'])){
            $state = get_option($this->updateName);
            if ( empty($state) ){
                $state = new StdClass;
                $state->lastCheck = time();
                $state->checkedVersion = THEME_VERSION;
                $state->update = null;
            }
            if(version_compare(THEME_VERSION, $data['version'])<0) {
                $state->update = new StdClass;
                $state->update->version = $data['version'];
                $state->update->url = urldecode($data['url']);
                $state->update->package = urldecode($data['package']);
                update_option($this->updateName, $state);
            }
        }

        echo 'success';
        exit;
    }

    private function _get_settings(){
        if( !isset($this->_settings) ) {
            $ops = base64_decode(get_option('izt_' . THEME_ID . '_panel'));
            $token = get_option('izt_theme_token');
            $ops = base64_decode(str_replace(md5(THEME_ID) . md5($token), '', $ops));
            $this->_settings = json_decode($ops);

            if(isset($this->_settings->theme) && $this->_settings->theme){
                $count = count($this->_settings->theme)-1;
                $domain = $this->_settings->theme[$count]->domain;
                $home = parse_url(get_option('siteurl'));
                $host = $home['host'];
                if( !($host==$domain && $token) ) $this->_settings = array();
            }
        }
        return $this->_settings;
    }

    private function get_panel_settings(){
        $pos = $this->_get_settings();
        if($pos&&$pos->theme) return $pos->theme;
    }

    public function get_meta_settings(){
        $res = new stdClass();
        if( $mos = $this->_get_settings() ) {
            foreach ($mos as $k => $v) {
                if (!in_array($k, array('theme', 'plugin', 'category_tpl', 'demo'))) {
                    $res->{$k} = $v;
                }
            }
        }
        return $res;
    }

    public function get_required_plugin(){
        $mos = $this->_get_settings();
        if(isset($mos->plugin)) return $mos->plugin;
    }

    public function get_category_tpl(){
        $mos = $this->_get_settings();
        if(isset($mos->category_tpl)) return $mos->category_tpl;
    }

    public function get_demo_config(){
        $mos = $this->_get_settings();
        if(isset($mos->demo)) return $mos->demo;
    }

    public function check_version(){
        $body = array('version'=>THEME_VERSION,'email' => get_option('izt_theme_email'),'home' => get_option('siteurl'),'themer' => FRAMEWORK_VERSION);
        echo $this->send_request('check', $body);
        if(isset($this->options['auto_check_update']) && $this->options['auto_check_update']=='1')
            $this->check_update(0);
        exit;
    }

    private function theme_update(){
        $settings = $this->_get_settings();
        if($settings && isset($settings->theme)){
            $count = count($settings->theme)-1;
            $version = $settings->theme[$count]->version;
            if( version_compare($version, THEME_VERSION) < 0 ) {
                $body = array('email'=>get_option('izt_theme_email'), 'token'=>get_option('izt_theme_token'), 'version'=>THEME_VERSION, 'home'=>get_option('siteurl'), 'themer' => FRAMEWORK_VERSION);
                $this->send_request('update', $body);
            }
        }
    }

    private function send_request($type, $body, $method='POST'){
        $url = 'http://www.wpcom.cn/authentication/'.$type.'/'.THEME_ID;
        $result = wp_remote_request($url, array('method' => $method, 'body'=>$body));
        if(is_array($result)){
            return $result['body'];
        }
    }

    public function get_theme_options() {
        $options = get_option( 'izt_theme_options' );
        if($options){
            foreach ($options as $key => $value) {
                $options[$key] = stripslashes_deep($value);
            }
        }
        return $options;
    }

    public function set_theme_options( $data ) {
        foreach($data as $key => $value){
            $this->options[$key] = $value;
        }
        return update_option( "izt_theme_options", $this->options );
    }

    public function remove_theme_options( $data ) {
        foreach($data as $key => $value){
            unset($this->options[$key]);
        }
        return update_option( "izt_theme_options", $this->options );
    }

    private function get_menu(){
        $output = '';
        $i=-1;
        $active = 0;
        if(isset($_COOKIE['wpcom_panel_nav']) && $_COOKIE['wpcom_panel_nav'])
            $active = $_COOKIE['wpcom_panel_nav'];
        foreach($this->settings as $index => $menu){
            if(isset($menu->require) && $menu->require && !function_exists($menu->require)) {
                continue;
            }else{
                $i++;
            }
            $output .= '<li';
            $output .= $i==$active ? ' class="active"' : '';
            $output .= '><a href="#tab'.$index.'" role="tab" data-toggle="tab"><i class="fa fa-'.$menu->icon.'"></i> '.$menu->title.'</a></li>';
        }
        echo $output;
    }

    private function get_tab(){
        $i=-1;
        $active = 0;
        if(isset($_COOKIE['wpcom_panel_nav']) && $_COOKIE['wpcom_panel_nav'])
            $active = $_COOKIE['wpcom_panel_nav'];
        foreach($this->settings as $index => $tab){
            if(isset($tab->require) && $tab->require && !function_exists($tab->require)) {
                continue;
            }else{
                $i++;
            }
            echo '<div class="tab-pane fade in';
            echo $i==$active?' active':'';
            echo '" id="tab'.$index.'">';
            $this->get_tab_html($index);
            echo '</div>';
        }
    }

    private function get_tab_html($i){
        $j = 0;
        foreach ($this->settings[$i]->option as $option) {
            $this->get_type_html($option,$j);
            $j++;
        }
    }

    private function get_type_html($option, $i, $repeat='-1'){
        $type = $option->type;
        $title = isset($option->title)?$option->title:'';
        $desc = isset($option->desc)?$option->desc:'';
        $name = isset($option->name)?$option->name:'';
        $id = isset($option->id)?$option->id:$name;
        $rows = isset($option->rows)?$option->rows:3;
        $tax = isset($option->tax)?$option->tax:'category';
        $value = isset($option->std)?$option->std:'';
        if($repeat>-1){
            $value = isset($this->options[$option->oname]) && isset($this->options[$option->oname][$repeat]) ? $this->options[$option->oname][$repeat] : $value;
        }else{
            $value = isset($this->options[$name]) ? $this->options[$name] : $value;
        }
        $notice = $desc?'<small class="input-notice">'.$desc.'</small>':'';

        switch ($type) {
            case 'title':
                $first = $i==0?' section-hd-first':'';
                echo '<div class="section-hd'.$first.'"><h3 class="section-title">'.$title.' <small>'.$desc.'</small></h3></div>';
                break;

            case 'text':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8"><input type="text" class="form-control" id="wpcom_'.$id.'" name="'.$name.'" value="'.esc_attr($value).'">'.$notice.'</div></div>';
                break;

            case 'radio':
                $html = '';
                foreach ($option->options as $opk=>$opv) {
                    $opk = $opk=='_empty_'?'':$opk;
                    $html.=$opk==$value?'<label class="radio-inline"><input type="radio" name="'.$name.'" checked value="'.$opk.'">'.$opv.'</label>':'<label class="radio-inline"><input type="radio" name="'.$name.'" value="'.$opk.'">'.$opv.'</label>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8">'.$html . $notice.'</div></div>';
                break;

            case 'checkbox':
                $html = '';
                foreach ($option->options as $opk=>$opv) {
                    $checked = '';
                    if(is_array($value)){
                        foreach($value as $v){
                            if($opk==$v) $checked = ' checked';
                        }
                    }else{
                        if($opk==$value) $checked = ' checked';
                    }
                    $html .= '<label class="checkbox-inline"><input type="checkbox" name="'.$name.'[]"'.$checked.' value="'.$opk.'">'.$opv.'</label>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8">'.$html . $notice.'</div></div>';
                break;

            case 'info':
                echo '<div class="form-group clearfix"><label class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8" style="padding-top:7px;">'.$value . $notice.'</div></div>';
                break;

            case 'alert':
                echo '<div class="clearfix"><div class="alert alert-warning" style="margin:0 0 10px;">'.$value.'</div></div>';
                break;

            case 'version':
                echo '<div class="form-group clearfix"><label class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8" style="padding-top:7px;">'.THEME_VERSION.' <a class="check-version" id="j-check-version" href="javascript:;">检查更新</a>'.$notice.'</div></div>';
                break;

            case 'select':
                $html = '';
                foreach ($option->options as $opk=>$opv) {
                    $opk = $opk=='_empty_'?'':$opk;
                    $html.=$opk==$value?'<option selected value="'.$opk.'">'.$opv.'</option>':'<option value="'.$opk.'">'.$opv.'</option>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8"><select class="form-control" id="wpcom_'.$id.'" name="'.$name.'">'.$html.'</select>'.$notice.'</div></div>';
                break;

            case 'textarea':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8"><textarea class="form-control" rows="'.$rows.'" id="wpcom_'.$id.'" name="'.$name.'">'.esc_html($value).'</textarea>'.$notice.'</div></div>';
                break;

            case 'editor':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-10">';
                wp_editor( wpautop( $value ), 'wpcom_'.$id, WPCOM::editor_settings(array('textarea_name'=>$name, 'textarea_rows'=>$rows)) );
                echo $notice.'</div></div>';
                break;

            case 'upload':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8"><input type="text" class="form-control" id="wpcom_'.$id.'" name="'.$name.'" value="'.esc_attr($value).'">'.$notice.'</div><div class="col-sm-2"><button id="wpcom_'.$id.'_upload" type="button" class="button upload-btn"><i class="fa fa-image"></i> 上传</button></div></div>';
                break;

            case 'color':
                $value = WPCOM::color($value);
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8"><input class="color-picker" type="text"  name="'.$name.'" value="'.esc_attr($value).'">'.$notice.'</div></div>';
                break;

            case 'page':
                $html = '<option value="">--请选择--</option>';
                $pages = $this->get_all_pages();
                foreach ($pages as $page) {
                    $html.=$page['ID']==$value?'<option selected value="'.$page['ID'].'">'.$page['title'].'</option>':'<option value="'.$page['ID'].'">'.$page['title'].'</option>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8"><select class="form-control" id="wpcom_'.$id.'" name="'.$name.'">'.$html.'</select>'.$notice.'</div></div>';
                break;


            case 'cat_single':
                $html = '<option value="">--请选择--</option>';
                $items = WPCOM::category($tax);
                foreach ($items as $key => $val) {
                    $html.=$key==$value?'<option selected value="'.$key.'">'.$val.'</option>':'<option value="'.$key.'">'.$val.'</option>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8"><select class="form-control" id="wpcom_'.$id.'" name="'.$name.'">'.$html.'</select>'.$notice.'</div></div>';
                break;

            case 'cat_multi':
                $html = '';
                $items = WPCOM::category($tax);
                foreach ($items as $key => $val) {
                    $checked = '';
                    if(is_array($value)){
                        foreach($value as $v){
                            if($key==$v) $checked = ' checked';
                        }
                    }else{
                        if($key==$value) $checked = ' checked';
                    }
                    $html.='<label class="checkbox-inline"><input name="'.$name.'[]"'.$checked.' type="checkbox" value="'.$key.'"> '.$val.'</label>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8 cat-checkbox-list" data-name="'.$name.'">'.$html.$notice.'</div></div>';
                break;
            case 'cat_multi_sort':
                $html = '';
                $items = WPCOM::category($tax);
                $value = $value ? $value : array();
                foreach ($value as $item) {
                    $html.='<label class="checkbox-inline"><input name="'.$name.'[]" checked type="checkbox" value="'.$item.'"> '.get_cat_name($item).'</label>';
                }
                foreach ($items as $key => $val) {
                    if(!in_array($key, $value)){
                        $html.='<label class="checkbox-inline"><input name="'.$name.'[]" type="checkbox" value="'.$key.'"> '.$val.'</label>';
                    }
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8"><div class="cat-checkbox-list j-cat-sort" data-name="'.$name.'">'.$html.'</div><div>'.$notice.'</div></div></div>';
                break;
            case 'toggle':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="col-sm-2 control-label">'.$title.'</label><div class="col-sm-8 toggle-wrap">';
                if($value=='1'){
                    echo '<div class="toggle active"></div>';
                }else{
                    echo '<div class="toggle"></div>';
                }
                echo '<input type="hidden" id="wpcom_'.$id.'" name="'.$name.'" value="'.esc_attr($value).'">'.$notice.'</div></div>';
                break;

            case 'repeat':
                /*
                 * $this->options 保存的数据
                 * $this->options[$option->options[0]->name] 重复数据的第一个属性保持的值
                 * 每个属性根据添加个数会有多个，以数组形式保存
                 */
                $len = count(isset($this->options[$option->options[0]->name])?$this->options[$option->options[0]->name]:array());
                $len = $len ? $len : 1;
                $index = array();
                if(isset($this->options[$option->options[0]->name])){
                    foreach ($this->options[$option->options[0]->name] as $a=>$b) {
                        $index[] = $a;
                    }
                }
                echo '<div class="wpcom-panel-repeat">';
                for($i=0; $i<$len; $i++) {
                    $j = isset($index[$i]) ? $index[$i] : $i;
                    echo '<div class="repeat-wrap" data-id="'.$i.'">';
                    $x = 0;
                    $arg = new stdClass();
                    foreach ($option->options as $o) {
                        foreach($o as $k=>$v){
                            $arg->{$k} = $v;
                        }
                        $arg->id = $o->name . '_' . $i;
                        $arg->name = $o->name . '['.$i.']';
                        $arg->oname = $o->name;
                        $this->get_type_html($arg, 1, $j);
                        $x++;
                    }
                    echo $i==0? '</div>':'<div class="repeat-action"><div class="repeat-item repeat-up j-repeat-up"><i class="dashicons dashicons-arrow-up-alt"></i></div><div class="repeat-item repeat-down j-repeat-down"><i class="dashicons dashicons-arrow-down-alt"></i></div><div class="repeat-item repeat-del j-repeat-del"><i class="dashicons dashicons-no-alt"></i></div></div></div>';
                }
                echo '<div class="repeat-btn-wrap"><button type="button" class="button j-repeat-add" id="wpcom_'.$name.'"><i class="dashicons dashicons-plus"></i> 添加选项</button></div></div>';
                break;
            default:
                break;
        }
    }

    function get_all_pages(){
        $pages = get_pages(array('post_type' => 'page','post_status' => 'publish'));
        $res = array();
        if($pages){
            foreach ($pages as $page) {
                $p = array(
                    'ID' => $page->ID,
                    'title' => $page->post_title
                );
                $res[] = $p;
            }
        }
        return $res;
    }

    public function check_update($value){

        if ($value && empty( $value->checked ) )
            return $value;

        if ( !current_user_can('update_themes' ) )
            return $value;

        if ( !$this->automaticCheckDone ) {
            $body = array('email' => get_option('izt_theme_email'), 'token' => get_option('izt_theme_token'), 'version' => THEME_VERSION, 'home' => get_option('siteurl'), 'themer' => FRAMEWORK_VERSION);
            $req = $this->send_request('notify', $body);
            $this->automaticCheckDone = true;

            $this->theme_update();
        }

        if ( !$value ) { // 手动点击更新
            $last_update = get_site_transient( 'update_themes' );
            if ( ! is_object($last_update) ) {
                $last_update = new stdClass;
                $installed_themes = wp_get_themes();
                $checked = array();
                foreach ( $installed_themes as $theme ) {
                    $checked[ $theme->get_stylesheet() ] = $theme->get('Version');
                }
                $last_update->checked = $checked;
                $last_update->response = array();
                $last_update->translations = array();
                $last_update->last_checked = time();
            }

            $this->theme_update();

            return set_site_transient( 'update_themes', $last_update, 3 * HOUR_IN_SECONDS );
        }

        global $theme_update_state;
        if(!isset($theme_update_state)) $theme_update_state = get_option($this->updateName);

        if ( !empty($theme_update_state) && isset($theme_update_state->update) && !empty($theme_update_state->update) ){
            $update = $theme_update_state->update;
            $value->response[$this->get_current_theme()] = array(
                'new_version' => $update->version,
                'url' => $update->url,
                'package' => $update->package
            );
        }

        return $value;
    }

    public function updated(){
        delete_option($this->updateName);
        $this->theme_update();
    }

    private function get_current_theme(){
        $theme = wp_get_theme();
        if($theme->get('Template')){
            return $theme->get('Template');
        }else{
            return $theme->get('Name');
        }
    }

    public function theme_options_demo_export(){
        if(current_user_can( 'edit_theme_options' )){
            header( "Content-type:  application/json" );
            header( 'Content-Disposition: attachment; filename="demo-options.json"' );
            $res = array();

            $nav_menu_locations = get_theme_mod('nav_menu_locations');
            $res['menu'] = array();
            if($nav_menu_locations){
                foreach($nav_menu_locations as $k => $nav){
                    if($term = get_term($nav, 'nav_menu')) $res['menu'][$k] = $term->slug;
                }
            }

            $sidebars_widgets = get_option('sidebars_widgets');
            $res['widgets'] = array();
            if($sidebars_widgets){
                $widgets = array();
                foreach($sidebars_widgets as $k => $wgts){
                    if($k!='wp_inactive_widgets' && $k!='array_version' && !empty($wgts)){
                        $res['widgets'][$k] = array();
                        foreach($wgts as $w){
                            preg_match('/(.*)-(\d+)$/i', $w, $matches);
                            if(!isset($widgets[$matches[1]])) $widgets[$matches[1]] = get_option('widget_'.$matches[1]);
                            $res['widgets'][$k][$w] = $widgets[$matches[1]][$matches[2]];
                            if($matches[1]=='nav_menu'){
                                $mid = $widgets['nav_menu'][$matches[2]]['nav_menu'];
                                if($term2 = get_term($mid, 'nav_menu')){
                                    $res['widgets'][$k][$w]['nav_menu'] = $term2->slug;
                                }
                            }
                        }
                    }
                }
            }


            // taxonomies options
            $res['taxonomy'] = array();
            $taxonomies = get_taxonomies();
            foreach($taxonomies as $tax){
                if($tax!='post_tag' && $tax!='nav_menu' && $tax!='link_category'  && $tax!='post_format' ) {
                    $terms = get_terms($tax);
                    $tOptions = array();
                    foreach($terms as $t){
                        $tOptions[$t->slug] = get_option('_'.$tax.'_'.$t->term_id);
                    }
                    $res['taxonomy'][$tax] = $tOptions;
                }
            }

            // 其他信息，比如分类、首页
            $res['show_on_front'] = get_option( 'show_on_front' );
            if($res['show_on_front']=='page'){
                $page = get_post(get_option( 'page_on_front' ));
                $res['page_on_front'] = $page->post_name;
            }

            $res['options'] = $this->options;
            echo json_encode($res);
            exit;
        }
    }
}

$wpcom_panel = new WPCOM_Panel();