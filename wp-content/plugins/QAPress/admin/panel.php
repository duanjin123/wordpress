<?php
class WPCOM_PLUGIN_PANEL{
    function __construct( $args ){
        $this->info = $args;
        $this->key = isset($this->info['key']) ? $this->info['key'] : '';
        $this->version = isset($this->info['ver']) ? $this->info['ver'] : '';
        $this->basename = isset($this->info['basename']) ? $this->info['basename'] : '';
        $this->plugin_slug = isset($this->info['slug']) ? $this->info['slug'] : '';
        $this->updateName = 'wpcom_update_' . $this->info['plugin_id'];
        $this->automaticCheckDone = false;

        add_action('wp_ajax_wpcom_callback', array($this, 'wpcom_callback'));
        add_action('wp_ajax_nopriv_wpcom_callback', array($this, 'wpcom_callback'));
        add_action('admin_menu', array($this, 'init'));
    }

    function init(){
        $title = isset($this->info['title']) ? $this->info['title'] : '';
        $icon = isset($this->info['icon']) ? $this->info['icon'] : '';
        $position = isset($this->info['position']) ? $this->info['position'] : '85';
        add_action('delete_site_transient_update_plugins', array($this, 'updated'));
        if($this->is_active()) add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));

        add_menu_page( $title, $title, 'edit_theme_options', $this->plugin_slug, array( &$this, 'options'), $icon, $position);        
    }

    function options(){
        require_once WPCOM_ADMIN_PATH . 'utils.php';
        
        $this->options = get_option($this->key);
        $this->form_action();
        $pages = WPCPM_ADMIN_UTILS::get_all_pages();

         // Load CSS
        wp_enqueue_style( "panel", WPCOM_ADMIN_URI . "css/panel.css", false, WPCOM_ADMIN_VERSION, "all");
        wp_enqueue_style( 'wp-color-picker' );

        // Load JS
        wp_enqueue_script("panel", WPCOM_ADMIN_URI . "js/panel.min.js", array('jquery', 'jquery-ui-core', 'wp-color-picker'), WPCOM_ADMIN_VERSION, true);
        wp_enqueue_media();

        ?>
        <div class="wrap wpcom-wrap">
            <div class="wpcom-panel-head">
                <div class="wpcom-panel-copy">WPCOM PLUGIN PANEL V<?php echo WPCOM_ADMIN_VERSION;?></div>
                <h1>插件设置<small><?php echo isset($this->info['name'])?$this->info['name']:'';?></small></h1>
            </div>
            <?php echo $this->build_form();?>
        </div>
    <?php }

    private function build_form(){
        if($this->is_active()){ ?>
            <form action="" method="post" id="wpcom-panel-form" class="wpcom-panel-form">
                <?php wp_nonce_field( $this->key . '_options', $this->key . '_nonce', true );?>
                    <?php if(isset($this->settings->option)) { $i=0;foreach ($this->settings->option as $item) {
                        $this->option_item($item, $i);
                        $i++;
                    }} ?>
                <div class="submit" style="padding-left: 25%;">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改">
                </div>
            </form>
        <?php }else{
            $this->active_form();
            $this->updated();
        }
    }

    private function active_form(){
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
                update_option( $this->plugin_slug . "_email", $email );
                update_option( $this->plugin_slug . "_token", wp_hash_password($token) );

                $body = array('email'=>$email, 'token'=>$token, 'version'=>$this->version, 'home'=>get_option('siteurl'), 'themer' => WPCOM_ADMIN_VERSION);
                $result_body = json_decode( $this->send_request('active', $body));
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
        <form class="form-horizontal active-form" id="wpcom-panel-form" method="post" action="">
            <h2 class="active-title">插件激活</h2>
            <div id="wpcom-panel-main" class="clearfix">
                <div class="form-horizontal">
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
                            <input type="submit" class="button button-primary button-active" value="提 交">
                        </div>
                    </div>
                </div>
            </div><!--#wpcom-panel-main-->
        </form>
    <?php }

    private function option_item($option, $i){
        $type = $option->type;
        $title = isset($option->title)?$option->title:'';
        $desc = isset($option->desc)?$option->desc:'';
        $name = isset($option->name)?$option->name:'';
        $id = isset($option->id)?$option->id:$name;
        $rows = isset($option->rows)?$option->rows:3;
        $value = isset($option->std)?$option->std:'';
        $value = isset($this->options[$name]) ? $this->options[$name] : $value;
        $notice = $desc?'<small class="input-notice">'.$desc.'</small>':'';
        $tax = isset($option->tax)?$option->tax:'category';

        switch ($type) {
            case 'title':
                $first = $i==0?' section-hd-first':'';
                echo '<div class="section-hd'.$first.'"><h3 class="section-title">'.$title.' <small>'.$desc.'</small></h3></div>';
                break;

            case 'text':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input"><input type="text" class="form-control" id="wpcom_'.$id.'" name="'.$name.'" value="'.esc_attr($value).'">'.$notice.'</div></div>';
                break;

            case 'radio':
                $html = '';
                foreach ($option->options as $opk=>$opv) {
                    $html.=$opk==$value?'<label class="radio-inline"><input type="radio" name="'.$name.'" checked value="'.$opk.'">'.$opv.'</label>':'<label class="radio-inline"><input type="radio" name="'.$name.'" value="'.$opk.'">'.$opv.'</label>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input">'.$html . $notice.'</div></div>';
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
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input">'.$html . $notice.'</div></div>';
                break;

            case 'info':
                echo '<div class="form-group clearfix"><label class="form-label">'.$title.'</label><div class="form-input" style="padding-top:7px;">'.$value . $notice.'</div></div>';
                break;

            case 'select':
                $html = '';
                foreach ($option->options as $opk=>$opv) {
                    $html.=$opk==$value?'<option selected value="'.$opk.'">'.$opv.'</option>':'<option value="'.$opk.'">'.$opv.'</option>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input"><select class="form-control" id="wpcom_'.$id.'" name="'.$name.'">'.$html.'</select>'.$notice.'</div></div>';
                break;

            case 'textarea':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input"><textarea class="form-control" rows="'.$rows.'" id="wpcom_'.$id.'" name="'.$name.'">'.esc_html($value).'</textarea>'.$notice.'</div></div>';
                break;

            case 'editor':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="col-sm-10">';
                wp_editor( wpautop( $value ), 'wpcom_'.$id, WPCPM_ADMIN_UTILS::editor_settings(array('textarea_name'=>$name, 'textarea_rows'=>$rows)) );
                echo $notice.'</div></div>';
                break;

            case 'upload':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input"><input type="text" class="form-control" id="wpcom_'.$id.'" name="'.$name.'" value="'.esc_attr($value).'">'.$notice.'</div><div class="col-sm-2"><button id="wpcom_'.$id.'_upload" type="button" class="button upload-btn"><i class="fa fa-image"></i> 上传</button></div></div>';
                break;

            case 'color':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input"><input class="color-picker" type="text"  name="'.$name.'" value="'.esc_attr($value).'">'.$notice.'</div></div>';
                break;

            case 'page':
                $html = '<option value="">--请选择--</option>';
                $pages = WPCPM_ADMIN_UTILS::get_all_pages();
                foreach ($pages as $page) {
                    $html.=$page['ID']==$value?'<option selected value="'.$page['ID'].'">'.$page['title'].'</option>':'<option value="'.$page['ID'].'">'.$page['title'].'</option>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input"><select class="form-control" id="wpcom_'.$id.'" name="'.$name.'">'.$html.'</select>'.$notice.'</div></div>';
                break;


            case 'cat_single':
                $html = '<option value="">--请选择--</option>';
                $items = WPCPM_ADMIN_UTILS::category($tax);
                foreach ($items as $key => $val) {
                    $html.=$key==$value?'<option selected value="'.$key.'">'.$val.'</option>':'<option value="'.$key.'">'.$val.'</option>';
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input"><select class="form-control" id="wpcom_'.$id.'" name="'.$name.'">'.$html.'</select>'.$notice.'</div></div>';
                break;

            case 'cat_multi':
                $html = '';
                $items = WPCPM_ADMIN_UTILS::category($tax);
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
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input cat-checkbox-list" data-name="'.$name.'">'.$html.$notice.'</div></div>';
                break;
            case 'cat_multi_sort':
                $html = '';
                $items = WPCPM_ADMIN_UTILS::category($tax);
                $value = $value ? $value : array();
                foreach ($value as $item) {
                    $category = get_term( $item, $tax );
                    $html.='<label class="checkbox-inline"><input name="'.$name.'[]" checked type="checkbox" value="'.$item.'"> '.$category->name.'</label>';
                }
                foreach ($items as $key => $val) {
                    if(!in_array($key, $value)){
                        $html.='<label class="checkbox-inline"><input name="'.$name.'[]" type="checkbox" value="'.$key.'"> '.$val.'</label>';
                    }
                }
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input"><div class="cat-checkbox-list j-cat-sort" data-name="'.$name.'">'.$html.'</div><div>'.$notice.'</div></div></div>';
                break;
            case 'toggle':
                echo '<div class="form-group clearfix"><label for="wpcom_'.$id.'" class="form-label">'.$title.'</label><div class="form-input toggle-wrap">';
                if($value=='1'){
                    echo '<div class="toggle active"></div>';
                }else{
                    echo '<div class="toggle"></div>';
                }
                echo '<input type="hidden" id="wpcom_'.$id.'" name="'.$name.'" value="'.esc_attr($value).'">'.$notice.'</div></div>';
                break;
            default:
                break;
        }
    }



    function form_action(){
        $nonce = isset($_POST[$this->key . '_nonce']) ? $_POST[$this->key . '_nonce'] : '';

        // Check nonce
        if ( ! $nonce || ! wp_verify_nonce( $nonce, $this->key . '_options' ) ){
            return;
        }

        $data = $_POST;
        $this->options = array();

        if(isset($this->settings->option)) { foreach ($this->settings->option as $item) {
            if(isset($item->name) && $item->name!='' && isset($data[$item->name])) {
                $this->options[$item->name] = $data[$item->name];
            }
        }}

        update_option($this->key, $this->options);
    }

    private function get_settings(){
        $ops = base64_decode(get_option('wpcom_' . $this->info['plugin_id']));
        $token = get_option($this->plugin_slug . "_token");
        $ops = base64_decode(str_replace(md5($token.$this->plugin_slug), '', $ops));
        return json_decode($ops);
    }

    private function send_request($type, $body, $method='POST') {
        $url = 'http://www.wpcom.cn/authentication/'.$type.'/' . $this->info['plugin_id'];
        $result = wp_remote_request($url, array('method' => $method, 'body'=>$body));
        if(is_array($result)){
            return $result['body'];
        }
    }

    public function is_active(){
        if(isset($this->is_active) && $this->is_active) return true;
        $this->is_active = false;
        if( !isset($this->settings)) $this->settings = $this->get_settings();
        if($this->settings){
            $domain = $this->settings->domain;
            $version = $this->settings->version;
            $home = parse_url(get_option('siteurl'));
            $host = $home['host'];
        }
        if( $this->settings && $host==$domain && get_option($this->plugin_slug . "_token")) $this->is_active = true;
        return $this->is_active;
    }

    public function wpcom_callback(){
        $post = $_POST;
        $token = get_option($this->plugin_slug . "_token");

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
            $settings = base64_encode(md5($token.$this->plugin_slug) . base64_encode($settings));
            update_option('wpcom_' . $this->info['plugin_id'], $settings);
        }else if(isset($data['package'])){
            $state = get_option($this->updateName);
            if ( empty($state) ){
                $state = new StdClass;
                $state->lastCheck = time();
                $state->checkedVersion = $this->version;
                $state->update = null;
            }
            if(version_compare($this->version, $data['version'])<0) {
                $state->update = new StdClass;
                $state->update->version = $data['version'];
                $state->update->url = $data['url'];
                $state->update->package = $data['package'];
                update_option($this->updateName, $state);
            }
        }

        echo 'success';
        exit;
    }


    private function plugin_update(){
        if( !isset($this->settings)) $this->settings = $this->get_settings();
        if($this->settings){
            $domain = $this->settings->domain;
            $version = $this->settings->version;
            if(is_admin() && version_compare($version, $this->version)<0){
                $body = array('email'=>get_option($this->plugin_slug . "_email"), 'token'=>get_option($this->plugin_slug . "_token"), 'version'=>$this->version, 'home'=>get_option('siteurl'), 'themer' => WPCOM_ADMIN_VERSION);
                $this->send_request('update', $body);
            }
        }
    }

    public function updated(){
        delete_option($this->updateName);
        $this->plugin_update();
    }

    public function check_update($value){
        if ($value && empty( $value->checked ) )
            return $value;

        if ( !current_user_can('update_plugins' ) )
            return $value;

        if ( !$this->automaticCheckDone ) {
            $body = array('email' => get_option($this->plugin_slug . "_email"), 'token' => get_option($this->plugin_slug . "_token"), 'version' => $this->version, 'home' => get_option('siteurl'), 'themer' => WPCOM_ADMIN_VERSION);
            $req = $this->send_request('notify', $body);
            $this->automaticCheckDone = true;

            $this->plugin_update();
        }

        global $plugin_update_state;
        if(!isset($plugin_update_state)) $plugin_update_state = get_option($this->updateName);

        if ( !empty($plugin_update_state) && isset($plugin_update_state->update) && !empty($plugin_update_state->update) ){
            $update = $plugin_update_state->update;
            $value->response[$this->basename] = array(
                'slug' => $this->info['slug'],
                'plugin' => $this->info['basename'],
                'new_version' => $update->version,
                'url' => $update->url,
                'package' => $update->package,
                'upgrade_notice' => ''
            );

            $value->response[$this->basename] = json_decode(json_encode($value->response[$this->basename]));
        }

        return $value;
    }
}