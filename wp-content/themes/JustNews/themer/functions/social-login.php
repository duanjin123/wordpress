<?php

class social_login {
    public function __construct() {
        global $options;
        if(function_exists('um_user') && isset($options['social_login_on']) && $options['social_login_on']=='1') {
            $this->type = '';
            $this->options = $options;
            $page_id = isset($options['social_login_page']) ? $options['social_login_page'] : '';
            $this->page = $page_id ? get_permalink($page_id) : '';

            $this->social = array(
                'qq' => '腾讯QQ',
                'sina' => '新浪微博',
                'wechat' => '微信'
            );
            add_action('init', array($this, 'init'), 1);
            add_action('um_after_form', array($this, 'show_social_icon'));
            add_shortcode("wpcom-social-login", array($this, 'wpcom_social_login'));

            add_action('wp_ajax_wpcom_sl_login', array($this, 'login_to_bind'));
            add_action('wp_ajax_nopriv_wpcom_sl_login', array($this, 'login_to_bind'));

            add_action('wp_ajax_wpcom_sl_create', array($this, 'create'));
            add_action('wp_ajax_nopriv_wpcom_sl_create', array($this, 'create'));
        }
    }

    function init(){
        if(!session_id()) session_start();
        if ( isset($_GET['login_type']) && isset($_GET['action']) ) {
            $this->type = $_GET['login_type'];
            if(!in_array($this->type, array_keys($this->social)) || !isset($_GET['action'])){
                return false;
            }

            if ($_GET['action'] == 'login') {
                $this->{$this->type.'_login'}();
            } else if (isset($_GET['code'])) {
                if(!isset($_GET['code']) || isset($_GET['error']) || isset($_GET['error_code']) || isset($_GET['error_description'])){
                    wp_die("<h3>错误：</h3>Code获取出错，请重试！");
                    exit();
                }

                //$this->{$this->type.'_callback'}($_GET['code']);
                $this->{$this->type.'_callback'}($_GET['code']);
                if (!isset($_SESSION['access_token'])||strlen($_SESSION['access_token'])<6||!$this->type){
                    wp_die("<h3>错误：</h3>Token获取出错，请重试！");
                    exit();
                }
		
                $bind_user = $this->is_bind($this->type, $_SESSION['openid']);
                if($bind_user && $bind_user->ID){
                    wp_set_auth_cookie($bind_user->ID);
                    wp_set_current_user($bind_user->ID);
                    unset($_SESSION['openid']);
                    unset($_SESSION['access_token']);
                    wp_redirect(home_url());
                    exit;
                }

                $newuser = $this->{$this->type.'_new_user'}();

                if(!isset($newuser['openid'])||strlen($newuser['openid'])<6){
                    wp_die("<h3>错误：</h3>OpenId获取出错，请重试！");
                    exit();
                }

                if($newuser){
                    unset($_SESSION['openid']);
                    $_SESSION['user'] = json_encode($newuser);
                }
                if($this->page){
                    wp_redirect($this->page);
                }else{
                    wp_die("<h3>错误：</h3>请设置社交绑定页面（主题设置>社交登录>社交绑定页面）");
                }
                exit;
            }
//        }else{

	}
    }

    function qq_login() {
        $params = array(
            'response_type' => 'code',
            'client_id' => isset($this->options['sl_qq_id']) ? $this->options['sl_qq_id'] : '',
            'state' => md5(uniqid(rand(), true)),
            'scope' => 'get_user_info',
            //'redirect_uri' => home_url().'?login_type=qq&action=callback'
            'redirect_uri' => home_url().'/connect/callback/qq?login_type=qq&action=callback'
        );
        header('Location:https://graph.qq.com/oauth2.0/authorize?'.http_build_query($params));
        exit();
    }

    function sina_login() {
        $params = array(
            'response_type' => 'code',
            'client_id' => isset($this->options['sl_sina_id']) ? $this->options['sl_sina_id'] : '',
            'redirect_uri' => home_url().'?login_type=sina&action=callback'
        );
        header('Location:https://api.weibo.com/oauth2/authorize?'.http_build_query($params));
        exit();
    }

    function wechat_login() {
        $params = array(
            'appid' => isset($this->options['sl_wechat_id']) ? $this->options['sl_wechat_id'] : '',
            'redirect_uri' => home_url().'?login_type=wechat&action=callback',
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => md5(uniqid(rand(), true))
        );
        if(wp_is_mobile()){
            header('Location:https://open.weixin.qq.com/connect/oauth2/authorize?'.http_build_query($params).'#wechat_redirect');
        }else{
            header('Location:https://open.weixin.qq.com/connect/qrconnect?'.http_build_query($params).'#wechat_redirect');
        }
        exit();
    }

    function qq_callback($code) {
        $params = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => isset($this->options['sl_qq_id']) ? $this->options['sl_qq_id'] : '',
            'client_secret' => isset($this->options['sl_qq_key']) ? $this->options['sl_qq_key'] : '',
            //'redirect_uri' => home_url().'?login_type=qq&action=callback'
            'redirect_uri' => home_url().'/connect/callback/qq?login_type=qq&action=callback'
        );
        $str = $this->http_request('https://graph.qq.com/oauth2.0/token?'.http_build_query($params));
        $_SESSION['access_token'] = $str['access_token'];
        $str = $this->http_request("https://graph.qq.com/oauth2.0/me?access_token=".$_SESSION['access_token']);
        preg_match('/callback\((.*)\);/i', $str, $matches);
        $str_r = json_decode(trim($matches[1]), true);
        if(isset($str_r['error'])){
            wp_die("<h3>错误：</h3>".$str_r['error']."<h3>错误信息：</h3>".$str_r['error_description']);
            exit();
        }
        $_SESSION['openid'] = $str_r['openid'];
    }

    function sina_callback($code) {
        $params = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => isset($this->options['sl_sina_id']) ? $this->options['sl_sina_id'] : '',
            'client_secret' => isset($this->options['sl_sina_key']) ? $this->options['sl_sina_key'] : '',
            'redirect_uri' => home_url().'?login_type=sina&action=callback'
        );
        $str = $this->http_request('https://api.weibo.com/oauth2/access_token', http_build_query($params), 'POST');
        $_SESSION["access_token"] = $str["access_token"];
        $_SESSION['openid'] = $str["uid"];
    }

    function wechat_callback($code) {
        $params = array(
            'appid' => isset($this->options['sl_wechat_id']) ? $this->options['sl_wechat_id'] : '',
            'secret' => isset($this->options['sl_wechat_key']) ? $this->options['sl_wechat_key'] : '',
            'code' => $code,
            'grant_type' => 'authorization_code'
        );
        $str = $this->http_request('https://api.weixin.qq.com/sns/oauth2/access_token', http_build_query($params), 'POST');
        $_SESSION["access_token"] = $str["access_token"];
        $_SESSION['openid'] = $str["openid"];
    }

    function qq_new_user(){
        $client_id = isset($this->options['sl_qq_id']) ? $this->options['sl_qq_id'] : '';
        $user = $this->http_request('https://graph.qq.com/user/get_user_info?access_token='.$_SESSION['access_token'].'&oauth_consumer_key='.$client_id.'&openid='.$_SESSION['openid']);
        $name = isset($user['nickname']) ? $user['nickname'] : 'QQ'.time();
        return array(
            'nickname' => $name,
            'display_name' => $name,
            'avatar' => $user['figureurl_qq_2'] ? $user['figureurl_qq_2'] : $user['figureurl_qq_1'],
            'type' => 'qq',
            'openid' => $_SESSION['openid']
        );
    }

    function sina_new_user(){
        $user = $this->http_request("https://api.weibo.com/2/users/show.json?access_token=".$_SESSION["access_token"]."&uid=".$_SESSION['openid']);
        return array(
            'nickname' => $user['screen_name'],
            'display_name' => $user['screen_name'],
            'user_url' => 'http://weibo.com/'.$user['profile_url'],
            'avatar' => $user['avatar_large'] ? $user['avatar_large'] : $user['profile_image_url'],
            'type' => 'sina',
            'openid' => $_SESSION['openid']
        );
    }

    function wechat_new_user(){
        $user = $this->http_request("https://api.weixin.qq.com/sns/userinfo?access_token=".$_SESSION["access_token"]."&openid=".$_SESSION['openid']."&lang=zh_CN");
        return array(
            'nickname' => $user['nickname'],
            'display_name' => $user['nickname'],
            'avatar' => $user['headimgurl'],
            'type' => 'wechat',
            'openid' => $_SESSION['openid']
        );
    }

    function wpcom_social_login(){
        $newuser = isset($_SESSION['user']) ? $_SESSION['user'] : '';
        if(!$newuser){
            return '<p style="text-align: center;text-indent: 0;margin: 0;">第三方帐号返回参数错误</p>';
        }else if($newuser && !is_array($newuser)){
            $newuser = json_decode($newuser, true);
        }

        $html = '<div class="social-login-wrap">';

        $html .= '<div class="sl-info-notice">
                        <div class="sl-info-avatar"><img src="'.$newuser['avatar'].'" alt="'.$newuser['nickname'].'"></div>
                        <div class="sl-info-text"><p>欢迎你，<b>'.$newuser['nickname'].'</b>！</p>
                        <p>当前你正在使用<b>'.$this->social[$newuser['type']].'帐号</b>登录，请绑定已有帐户，或者注册新用户绑定。</p></div>
                    </div>
                    <div class="social-login-form">

                    <div class="sl-form-item">
                    <form id="sl-form-bind" class="sl-info-form" method="post"><div id="sl-info2-nonce">
                    ' . wp_nonce_field( 'wpcom_social_login2', 'social_login2_nonce', true, false ) . '</div>
                    <h3 class="sl-form-title">没有帐户，请完善信息</h3>
                        <div class="sl-input-item">
                            <label>电子邮箱</label>
                            <div class="sl-input">
                                <input type="text" name="email" value="" placeholder="请输入电子邮箱">
                            </div>
                        </div>
                        <div class="sl-input-item sl-submit">
                            <div class="sl-result pull-left"></div>
                            <input class="btn sl-input-submit" type="submit" value="提交注册">
                        </div>
                    </form>
                    </div>

                    <div class="sl-form-item">
                    <form id="sl-form-create" class="sl-info-form" method="post"><div id="sl-info-nonce">
                        ' . wp_nonce_field( 'wpcom_social_login', 'social_login_nonce', true, false ) . '</div>
                        <h3 class="sl-form-title">已经拥有帐户，请绑定</h3>
                        <div class="sl-input-item">
                            <label>用户名或邮箱</label>
                            <div class="sl-input">
                                <input type="text" name="username" value="" placeholder="请输入用户名或邮箱">
                            </div>
                        </div>
                        <div class="sl-input-item">
                            <label>密码</label>
                            <div class="sl-input">
                                <input type="password" name="password" value="" placeholder="请输入密码">
                            </div>
                        </div>
                        <div class="sl-input-item sl-submit">
                            <div class="sl-result pull-left"></div>
                            <input class="btn sl-input-submit" type="submit" value="登录并绑定">
                        </div>
                    </form>
                    </div>
        ';
        $html .= '</div></div>';

        return $html;
    }

    function login_to_bind(){
        check_ajax_referer( 'wpcom_social_login', 'social_login_nonce', false );

        if(!session_id()) session_start();

        $newuser = isset($_SESSION['user']) ? $_SESSION['user'] : '';

        if(!$newuser){
            echo json_encode(array('result'=> 3));
            exit;
        }else if($newuser && !is_array($newuser)){
            $newuser = json_decode($newuser, true);
        }

        if( ! (isset($newuser['type']) || $newuser['openid']) ){
            echo json_encode(array('result'=> 3));
            exit;
        }

        $res = array();

        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }

        if($username==''||$password=='')
            $res['result'] = 1;

        if(is_email($username)){
            $user = get_user_by( 'email', $username );
        }else{
            $user = get_user_by( 'login', $username );
        }

        if ( $user && wp_check_password( $password, $user->data->user_pass, $user->ID) ){
            $bind_user = $this->is_bind($newuser['type'], $newuser['openid']);
            if(isset($bind_user->ID) && $bind_user->ID){ // 已绑定用户
                if( (is_email($username) && $bind_user->data->user_email==$username) ||
                    (!is_email($username) && $bind_user->data->user_login==$username) ){ // 绑定的就是这个帐号
                    $res['result'] = 0;
                    $res['redirect'] = home_url();
                    unset($_SESSION['user']);
                    wp_set_auth_cookie($user->ID);
                    wp_set_current_user($user->ID);
                }else{
                    $res['result'] = 4;
                }
            }else{
                update_user_option($user->ID, 'social_type_'.$newuser['type'], $newuser['openid']);
                $res['result'] = 0;
                $res['redirect'] = home_url();
                unset($_SESSION['user']);
                wp_set_auth_cookie($user->ID);
                wp_set_current_user($user->ID);
                $this->set_avatar($user->ID, $newuser['avatar']);
		
            }
        }else{
            $res['result'] = 2;
        }

        echo json_encode($res);
        exit;
    }

    function create(){
        check_ajax_referer( 'wpcom_social_login2', 'social_login2_nonce', false );

        if(!session_id()) session_start();

        $newuser = isset($_SESSION['user']) ? $_SESSION['user'] : '';

        if(!$newuser){
            echo json_encode(array('result'=> 3));
            exit;
        }else if($newuser && !is_array($newuser)){
            $newuser = json_decode($newuser, true);
        }

        if( ! (isset($newuser['type']) || $newuser['openid']) ){
            echo json_encode(array('result'=> 3));
            exit;
        }

        $res = array();

        if(isset($_POST['email'])){
            $email = $_POST['email'];
        }

        if($email=='')
            $res['result'] = 1;

        if(is_email($email)){
            $bind_user = $this->is_bind($newuser['type'], $newuser['openid']);
            if(isset($bind_user->ID) && $bind_user->ID){ // 已绑定用户
                $res['result'] = 4;
            }else{
                $user = get_user_by( 'email', $email );
                if($user->ID){ // 用户已存在
                    $res['result'] = 5;
                }else{
                    $res['result'] = 0;
                    $res['redirect'] = home_url();

                    $userdata = array(
                        'user_pass' => wp_generate_password(),
                        'user_login' => strtoupper($newuser['type']).$newuser['openid'],
                        'user_email' => $email,
                        'nickname' => $newuser['nickname'],
                        'display_name' => $newuser['display_name']
                    );
                    if($newuser['type']=='sina') $userdata['user_url'] = $newuser['user_url'];

                    if(!function_exists('wp_insert_user')){
                        include_once( ABSPATH . WPINC . '/registration.php' );
                    }
                    $user_id = wp_insert_user($userdata);


                    if(function_exists('um_get_option')){
                        $default_role = um_get_option("default_role");
                        $usermeta = array(
                            'account_status'			=> 'approved',
                            'role'						=> isset( $default_role ) ? $default_role: 'member'
                        );
                        foreach ( $usermeta as $key => $value ) {
                            update_user_meta( $user_id, $key, $value );
                        }
                    }else{
                        wp_update_user(array('ID'=>$user_id, 'role'=>'subscriber'));
                    }

                    do_action('wpcom_social_new_user', $user_id, $_POST);
                    update_user_option($user_id, 'social_type_'.$newuser['type'], $newuser['openid']);
                    //unset($_SESSION['user']);
                    wp_set_auth_cookie($user_id);
                    wp_set_current_user($user_id);
                    $this->set_avatar($user_id, $newuser['avatar']);
		    unset($_SESSION['user']);
                }
            }
        }else{
            $res['result'] = 2;
        }

        echo json_encode($res);
        exit;
    }

    function http_request($url, $body=array(), $method='GET'){
        $result = wp_remote_request($url, array('method' => $method, 'body'=>$body));
        if( is_array($result) ){
            $json_r = json_decode($result['body'], true);
            if( count($json_r)==0 ){
                parse_str($result['body'], $json_r);
                if( count($json_r)==1 && current($json_r)==='' ) return $result['body'];
            }
            return $json_r;
        }
    }

    function is_bind($type, $openid) {
        global $wpdb;
        $args = array(
            'meta_key'     => $wpdb->get_blog_prefix() . 'social_type_' . $type,
            'meta_value'   => $openid,
        );
        $users = get_users($args);
        if($users){
            return $users[0];
        }
    }

    function set_avatar($user, $img){
        global $ultimatemember;
        if(!$user || !$img) return false;

        if(isset($ultimatemember)){
            $ultimatemember->user->set( $user );
            if (isset( $ultimatemember->user->profile['profile_photo'] ) && !empty( $ultimatemember->user->profile['profile_photo'] ) ){ //已经设置头像
                return false;
            }

            //Fetch and Store the Image
            $http_options = array(
                'timeout' => 20,
                'redirection' => 20,
                'sslverify' => FALSE
            );
            $get = wp_remote_head( $img, $http_options );
            $response_code = wp_remote_retrieve_response_code ( $get );

            if (200 == $response_code) { // 图片状态需为 200
                $type = $get ['headers'] ['content-type'];

                $mime_to_ext = array(
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/bmp' => 'bmp',
                    'image/tiff' => 'tif'
                );

                $file_ext = isset($mime_to_ext[$type]) ? $mime_to_ext[$type] : '';

                $allowed_filetype = array('jpg', 'gif', 'png', 'bmp');

                if (in_array($file_ext, $allowed_filetype)) { // 仅保存图片格式 'jpg','gif','png', 'bmp'
                    $http = wp_remote_get($img, $http_options);
                    if (!is_wp_error($http) && 200 === $http ['response'] ['code']) { // 请求成功
                        $upload_info = wp_upload_dir();

                        $filename = rawurldecode(wp_basename($img));
                        $ext = substr(strrchr($filename, '.'), 1);
                        $filename = wp_basename($filename, "." . $ext) . '.' . $file_ext;
                        $filename = wp_unique_filename($upload_info['path'], $filename, null);

                        $mirror = wp_upload_bits($filename, '', $http ['body'], '1234/12');
                        $res = $ultimatemember->files->new_user_upload($user, $mirror['file'], 'profile_photo');
                        @unlink( $mirror['file'] );
                        $ultimatemember->user->remove_cache($user);
                        return $res;
                    }
                }
            }
        }
    }

    function show_social_icon($args){
        $mode = $args['mode'];
        if ($mode == 'login' || $mode == 'register') {
            $html = '<div class="more-sign"><h4 class="more-sign-title"><span>第三方帐号登录</span></h4><ul class="more-sign-list">';
            if(isset($this->options['sl_sina_id']) && $this->options['sl_sina_id'] && isset($this->options['sl_sina_key']) && $this->options['sl_sina_key']){
                $html .= '<li><a class="more-sign-item more-sign-weibo" target="_blank" href="?login_type=sina&action=login"><i class="fa fa-weibo"></i> 微博登录</a></li>';
            }
            if(isset($this->options['sl_qq_id']) && $this->options['sl_qq_id'] && isset($this->options['sl_qq_key']) && $this->options['sl_qq_key']){
                $html .= '<li><a class="more-sign-item more-sign-qq" target="_blank" href="?login_type=qq&action=login"><i class="fa fa-qq"></i> QQ登录</a></li>';
            }
            if(isset($this->options['sl_wechat_id']) && $this->options['sl_wechat_id'] && isset($this->options['sl_wechat_key']) && $this->options['sl_wechat_key']){
                $html .= '<li><a class="more-sign-item more-sign-wechat" target="_blank" href="?login_type=wechat&action=login"><i class="fa fa-wechat"></i> 微信登录</a></li>';
            }

            $html .= '</ul></div>';
            echo $html;
        }
    }
}

new social_login();
