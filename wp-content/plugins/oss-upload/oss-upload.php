<?php
/*
 * Plugin Name: OSS Upload
 * Version: 3.6
 * Description: Upload with Aliyun OSS, with modified OSS Wrapper and fully native image edit function support.
 * Plugin URI: https://www.xiaomac.com/2016121895.html
 * Author: Link
 * Author URI: https://www.xiaomac.com
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: oss-upload
 * Domain Path: /lang
*/

add_action('init', 'oss_upload_init', 1);
function oss_upload_init(){
    if(ouop('oss') && ouop('oss_akey') && ouop('oss_skey') && ouop('oss_endpoint')){
        if(!ouop('oss_thumbnail', 1) && ($width = ouop('oss_size_width')) && ($height = ouop('oss_size_height'))){
            add_theme_support('post-thumbnails');
            set_post_thumbnail_size(intval($width), intval($height), array('center', 'center'));
        }
        define('OSS_ACCESS_ID', trim(ouop('oss_akey')));
        define('OSS_ACCESS_KEY', trim(ouop('oss_skey')));
        define('OSS_ENDPOINT', trim(ouop('oss_endpoint')));
        require_once('lib/OSSWrapper.php');
    }
}

function ouop($k, $v=null){
    $op = isset($GLOBALS['oss_upload_options']) ? $GLOBALS['oss_upload_options'] : $GLOBALS['oss_upload_options'] = get_option('ouop');
    return isset($op[$k]) ? (isset($v) ? $op[$k] == $v : $op[$k]) : '';
}

function oss_upload_dir($param){
    $action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
    if(in_array($action, array('upload-plugin', 'upload-theme'))) return $param;
    if(ouop('oss') && ouop('oss_path') && ouop('oss_url')){
        if(empty($param['default'])) $param['default'] = $param;
        $param['basedir'] = trim(ouop('oss_path'), '/');
        $param['path'] = $param['basedir'] . $param['subdir'];
        $param['baseurl'] = trim(ouop('oss_url'), '/');
        $param['url'] = $param['baseurl'] . $param['subdir'];
    }
    return $param;
}

function oss_upload_dir_loader(){
    add_filter('upload_dir', 'oss_upload_dir');
}

add_action('admin_init', 'oss_upload_admin_init', 1);
function oss_upload_admin_init() {
    load_plugin_textdomain('oss-upload', '', dirname(plugin_basename( __FILE__ )) . '/lang');
    $GLOBALS['oss_upload_data'] = get_plugin_data( __FILE__ );
    register_setting('oss_upload_admin_options_group', 'ouop');
    add_action('wp_ajax_image-editor', 'oss_upload_dir_loader', 1);
    add_action('wp_ajax_query-attachments', 'oss_upload_dir_loader', 1);
}

add_action('admin_menu', 'oss_upload_admin_menu');
function oss_upload_admin_menu() {
    add_options_page(__('OSS Upload','oss-upload'), __('OSS Upload','oss-upload'), 'manage_options', 'oss-upload', 'oss_upload_options_page');
}

add_filter('plugin_action_links_'.plugin_basename( __FILE__ ), 'oss_upload_settings_link');
function oss_upload_settings_link($links) {
    return array_merge(array(oss_upload_link('options-general.php?page=oss-upload', __('Settings'))), $links);
}

register_uninstall_hook(__FILE__, 'oss_upload_uninstall_hook');
function oss_upload_uninstall_hook(){
    $files = get_posts(array('post_type'=>'attachment'));
    foreach ($files as $file){
        delete_post_meta($file->ID, '_wp_attached_oss');
        delete_post_meta($file->ID, 'oss_upload');
    }
}

function oss_upload_data($key){
    $data = $GLOBALS['oss_upload_data'];
    return isset($data) && is_array($data) && isset($data[$key]) ? $data[$key] : '';
}

function oss_upload_show_more($cols, $ret=false){
    static $header = array();
    $arr  = get_user_option('managesettings_page_oss-uploadcolumnshidden');
    $hide = (is_array($arr) && in_array($cols, $arr)) ? ' hidden' : '';
    $head = in_array($cols, $header) ? " class='{$cols}" : " id='{$cols}' class='manage-column";
    $out = "{$head} column-{$cols}{$hide}'";
    if(!in_array($cols, $header)) $header[] = $cols;
    if($ret) return $out;
    echo $out;
}

add_filter('manage_settings_page_oss-upload_columns', 'oss_upload_setting_columns');
function oss_upload_setting_columns($cols){
    $cols['_title'] = __('Show More','oss-upload');
    $cols['oss_upload_desc'] = __('Descriptions', 'oss-upload');
    $cols['oss_upload_example'] = __('Examples', 'oss-upload');
    return $cols;
}

function oss_upload_link($url, $text='', $ext=''){
    if(empty($text)) $text = $url;
    $button = stripos($ext, 'button') !== false ? " class='button'" : "";
    $target = stripos($ext, 'blank') !== false ? " target='_blank'" : "";
    $link = "<a href='{$url}'{$button}{$target}>{$text}</a>";
    return stripos($ext, 'p') !== false ? "<p>{$link}</p>" : "{$link} ";
}

add_filter('wp_handle_upload_prefilter', 'oss_handle_upload_prefilter', 10, 2);
function oss_handle_upload_prefilter($file){
    oss_upload_dir_loader();
    return $file;
}

add_action('delete_attachment', 'oss_upload_delete_thumbnail');
function oss_upload_delete_thumbnail($id, $data=array()) {
    if(!ouop('oss')) return;
    if(!$data) $data = wp_get_attachment_metadata($id, 1);
    if($data && !empty($data['sizes'])){
        $dir = preg_match('/\//', dirname($data['file'])) ? '/'.dirname($data['file']) : '';
        $uploads = oss_upload_dir(wp_get_upload_dir());
        foreach ($data['sizes'] as $k => $v) {
            if(basename($data['file']) == preg_replace('/\?.*$/', '', $v['file'])) continue;
            $file = $uploads['basedir'].$dir.'/'.preg_replace('/\?.*$/', '', $v['file']);
            if(@file_exists($file)) @unlink($file);
        }
    }
}

add_filter('wp_generate_attachment_metadata', 'oss_upload_generate_metadata', 10, 2);
function oss_upload_generate_metadata($data, $id){
    if(ouop('oss_thumbnail') && !empty($data['sizes'])) oss_upload_delete_thumbnail($id, $data);
    if(ouop('oss')){
        $data = oss_upload_set_metadata($data);
        update_post_meta($id, '_wp_attached_oss', '1');
    }
    return $data;
}

add_filter('wp_get_attachment_metadata', 'oss_upload_get_metadata', 9999);
function oss_upload_get_metadata($data){
    if(ouop('oss') && ouop('oss_thumbnail') && !empty($data['sizes'])) $data = oss_upload_set_metadata($data);
    return $data;
}

function oss_upload_set_metadata($data){
    $thumb = ouop('oss_thumbnail');
    if($thumb == 1){
        unset($data['sizes']);
    }else{
        $ss = ouop('oss_style_separator') ? ouop('oss_style_separator') : '?x-oss-process=style/';
        foreach ($data['sizes'] as $k => $v){
            if(!isset($v['file'])) break;
            if($thumb == 0) $postfix = ouop('oss_default_style') ? ouop('oss_default_style') : '';
            if($thumb == 2) $postfix = "?x-oss-process=image/resize,m_fill,w_{$v['width']},h_{$v['height']}";
            if($thumb == 3) $postfix = "{$ss}{$k}";
            $file = $thumb ? $data['file'] : $data['sizes'][$k]['file'];
            if(isset($postfix)) $data['sizes'][$k]['file'] = basename($file).$postfix;
        }
    }
    return $data;
}

add_filter('wp_get_attachment_url', 'oss_upload_attachment_url', 9999, 2);
function oss_upload_attachment_url($url, $id){
    if(ouop('oss') && ouop('oss_url')){
        $upload = oss_upload_dir(wp_get_upload_dir());
        $oss = oss_upload_get_attachment_oss($id, $upload);
        $find = $oss ? $upload['default']['baseurl'] : $upload['baseurl'];
        $replace = $oss ? $upload['baseurl'] : $upload['default']['baseurl'];
        $url = str_replace($find, $replace, $url);
        $url = oss_upload_url_fixer($url);
    }
    if(wp_attachment_is_image($id) && ouop('oss_default_style')) $url .= ouop('oss_default_style');
    return $url;
}

add_filter('get_attached_file', 'oss_upload_attached_file', 9999, 2);
function oss_upload_attached_file($file, $id){
    if(ouop('oss') && ouop('oss_path')){
        $upload = oss_upload_dir(wp_get_upload_dir());
        $oss = oss_upload_get_attachment_oss($id, $upload);
        $find = $oss ? $upload['default']['basedir'] : $upload['basedir'];
        $replace = $oss ? $upload['basedir'] : $upload['default']['basedir'];
        $file = str_replace($find, $replace, $file);
    }
    return $file;
}

function oss_upload_get_attachment_oss($id, $upload=array()){
    $oss = get_post_meta($id, '_wp_attached_oss', true);
    if($oss != '') return $oss;
    if(!$upload) $upload = oss_upload_dir(wp_get_upload_dir());
    if(!ouop('oss') || empty($upload['default'])) return false;
    $url = get_the_guid($id);
    if(0 === stripos($url, $upload['default']['baseurl'])){
        $file = get_attached_file($id, true);
        if(0 === stripos($file, $upload['default']['basedir']) && @file_exists($file)) $local = 1;
    }
    update_post_meta($id, '_wp_attached_oss', empty($local) ? '1' : '0');
    return empty($local) ? true : false;
}

add_filter('wp_calculate_image_srcset', 'oss_upload_image_srcset', 9999, 5);
function oss_upload_image_srcset($sources, $size_array, $image_src, $meta, $id){
    if(!ouop('oss') || empty($meta['sizes'])) return $sources;
    if(ouop('oss_thumbnail', 1)) return array();
    $upload = oss_upload_dir(wp_get_upload_dir());
    $oss = oss_upload_get_attachment_oss($id, $upload);
    $find = $oss ? $upload['default']['baseurl'] : $upload['baseurl'];
    $replace = $oss ? $upload['baseurl'] : $upload['default']['baseurl'];
    foreach ($sources as $k => $v){
        $url = str_replace($find, $replace, $sources[$k]['url']);
        $url = oss_upload_url_fixer($url);
        if(basename($meta['file']) == basename($url) && ouop('oss_default_style')) $url .= ouop('oss_default_style');
        $sources[$k]['url'] = $url;
    }
    return $sources;
}

add_filter('intermediate_image_sizes_advanced', 'oss_upload_intermediate', 9999);
function oss_upload_intermediate($sizes){
    if(ouop('oss') && ouop('oss_thumbnail', 1)) return array();
    return $sizes;
}

add_filter('wp_image_editors', 'oss_upload_image_editors');
function oss_upload_image_editors($arr){
    if(ouop('oss_editor')){
        $arr = array_diff($arr, array('WP_Image_Editor_GD'));
        array_unshift($arr, 'WP_Image_Editor_GD');  
    }
    return $arr;
}

function oss_upload_url_fixer($url){
    if(trim(ouop('oss_url_find')) && trim(ouop('oss_url_replace'))){
        $find  = explode(',', trim(ouop('oss_url_find')));
        $replace = explode(',', trim(ouop('oss_url_replace')));
        $url = str_replace($find, $replace, $url);
    }
    return $url;
}

add_filter('views_upload', 'oss_upload_views_upload');
function oss_upload_views_upload($views){
    if(is_super_admin()) $views['actions'] = oss_upload_link('options-general.php?page=oss-upload', __('OSS Upload','oss-upload'), 'button');
    return $views;
}

add_action('current_screen', 'oss_upload_setting_screen');
function oss_upload_setting_screen() {
    $screen = get_current_screen();
    if($screen->id != 'settings_page_oss-upload') return;
    $help_content = '<p>'.oss_upload_data('Description').'</p><br/>'.
        '<p>'.oss_upload_link('http://www.aliyun.com/product/oss/', 'Aliyun OSS', 'button,blank').
        oss_upload_link('https://oss.console.aliyun.com/index', 'OSS Console', 'button,blank').
        oss_upload_link('https://help.aliyun.com/document_detail/32174.html', 'PHP SDK', 'button,blank').
        oss_upload_link('https://docs-aliyun.cn-hangzhou.oss.aliyun-inc.com/internal/oss/0.0.4/assets/pdf/oss_sdk_php20150819.pdf', 'SDK PDF', 'button,blank').
        oss_upload_link('https://wordpress.org/plugins/oss-upload/', __('Plugin Rating', 'oss-upload'), 'button,blank').'</p>';
    $help_sidebar = '<p><strong>'.__('For more information', 'oss-upload').'</strong></p>'.
        oss_upload_link(oss_upload_data('PluginURI'), __('Support', 'oss-upload'), 'p,blank').
        oss_upload_link('https://www.xiaomac.com/about', __('Donate', 'oss-upload'), 'p,blank').
        oss_upload_link('https://www.xiaomac.com/tag/work', __('More Plugins', 'oss-upload'), 'p,blank');
    $screen->add_help_tab(array('id' => 'oss_upload_help', 'title' => __('About', 'oss-upload'), 'content' => $help_content));
    $screen->set_help_sidebar($help_sidebar);
}

add_action('admin_notices', 'oss_upload_admin_note');
function oss_upload_admin_note(){
    if(isset($_GET['settings-updated']) && $_GET['settings-updated']=='test'){
        try{
            $ok = false;
            $rnd = md5(time());
            $file = ouop('oss_path').'/oss_upload_'.$rnd.'.txt';
            $try = file_put_contents($file, $rnd);
            if($try == strlen($rnd)){
                $out = __('Write OK, ','oss-upload');
                $try = file_get_contents($file);
                if($try == $rnd){
                    $out .= __('Read OK, ', 'oss-upload');
                    $try = unlink($file);
                    if($try === true){
                        $out .= __('Delete OK', 'oss-upload');
                        $ok = true;
                    }else{
                        throw new RequestCore_Exception($out . __('Delete Error: ', 'oss-upload') . $try);
                    }
                }else{
                    throw new RequestCore_Exception($out . __('Read Error: ', 'oss-upload') . $try);
                }
            }else{
                throw new RequestCore_Exception($out . __('Write Error: ', 'oss-upload') . $try);
            }
        }catch(Exception $ex){
            $out = esc_html($ex->message);
        }
        echo '<div class="'. ($ok ? 'updated fade' : 'error') . '"><p>'.$out.'</p></div>';
    }
}

function oss_upload_options_page(){?>
    <div class="wrap">
        <h2><?php _e('OSS Upload','oss-upload')?>
            <a class="page-title-action" href="<?php echo oss_upload_data('PluginURI');?>" target="_blank"><?php echo oss_upload_data('Version');?></a>
        </h2>
        <form action="options.php" method="post">
        <?php settings_fields('oss_upload_admin_options_group'); ?>
        <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e('Enable','oss-upload')?></th>
        <td>
            <label><input name="ouop[oss]" type="checkbox" value="1" <?php checked(ouop('oss'),1);?> />
            <?php _e('Use OSS as media library storage','oss-upload')?></label>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Access Key','oss-upload')?></th>
        <td>
            <input name="ouop[oss_akey]" size="60" placeholder="Access Key" value="<?php echo ouop('oss_akey')?>" required />
            <?php echo oss_upload_link('https://ak-console.aliyun.com/', '?', 'blank'); ?>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Secret Key','oss-upload')?></th>
        <td>
            <input name="ouop[oss_skey]" size="60" placeholder="Secret Key" value="<?php echo ouop('oss_skey')?>" required />
            <?php echo oss_upload_link('https://ak-console.aliyun.com/', '?', 'blank'); ?>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Upload Path','oss-upload')?></th>
        <td>
            <input name="ouop[oss_path]" size="60" placeholder="oss://{BUCKET}/{PATH}" value="<?php echo ouop('oss_path')?>" required />
            <?php echo oss_upload_link('https://help.aliyun.com/document_detail/31902.html', '?', 'blank'); ?>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('<code>{PATH}</code> can be empty, with no slash at the end','oss-upload')?></small></p>
            <div <?php oss_upload_show_more('oss_upload_example'); ?>>
            <p><small><code>oss://my-bucket</code></small></p>
            <p><small><code>oss://my-bucket/uploads</code></small></p>
            </div>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Visit URL','oss-upload')?></th>
        <td>
            <input name="ouop[oss_url]" type="url" size="60" placeholder="http://oss.aliyuncs.com/{BUCKET}/{PATH}" value="<?php echo ouop('oss_url')?>" required />
            <?php echo oss_upload_link('https://help.aliyun.com/document_detail/31902.html', '?', 'blank'); ?>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('<code>{PATH}</code> can be empty, <code>{BUCKET}</code> can be directory or domain, also can be https','oss-upload')?></small></p>
            <div <?php oss_upload_show_more('oss_upload_example'); ?>>
            <p><small><code>http://my-bucket.oss-cn-shenzhen.aliyuncs.com</code></small></p>
            <p><small><code>http://my-bucket.oss-cn-shenzhen.aliyuncs.com/uploads</code></small></p>
            <p><small><code>http://www.my-oss-domain.com</code></small></p>
            <p><small><code>http://www.my-oss-domain.com/uploads</code></small></p>
            </div>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Upload EndPoint','oss-upload')?></th>
        <td>
            <input name="ouop[oss_endpoint]" size="60" placeholder="oss-{cn-xxxxxx}.aliyuncs.com" value="<?php echo ouop('oss_endpoint')?>" required />
            <?php echo oss_upload_link('https://help.aliyun.com/document_detail/31837.html', '?', 'blank'); ?>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('<code>{BUCKET}</code> not included, can be internal endpoint if your server is in the same area with oss','oss-upload')?></small></p>
            <div <?php oss_upload_show_more('oss_upload_example'); ?>>
            <p><small><code>oss-cn-hangzhou.aliyuncs.com</code></small></p>
            <p><small><code>oss-cn-shenzhen.aliyuncs.com</code></small></p>
            <p><small><code>oss-cn-shanghai.aliyuncs.com</code></small></p>
            <p><small><code>oss-us-west-1.aliyuncs.com</code></small></p>
            <p><small><code>oss-cn-hangzhou-internal.aliyuncs.com</code></small></p>
            </div>
        </td></tr>
        <tr valign="top">
        <th scope="row"></th>
        <td>
            <?php 
            if(ouop('oss') && ouop('oss_akey') && ouop('oss_skey') && ouop('oss_endpoint')){
                echo oss_upload_link('options-general.php?page=oss-upload&settings-updated=test', __('Run a test', 'oss-upload'), 'p,button');
            } ?>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Image Thumbnails','oss-upload')?></th>
        <td>
            <p><label><input name="ouop[oss_thumbnail]" type="radio" value="1" <?php checked(ouop('oss_thumbnail'),1);?> /> <?php _e('Disable thumbnails','oss-upload')?></label></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Use origin image for display in all scenarios','oss-upload')?></small></p><br/>
            <p><label><input name="ouop[oss_thumbnail]" type="radio" value="0" <?php if(0 == ouop('oss_thumbnail')) echo 'checked="checked"';?>/> <?php _e('Generate thumbnails file in a violent way','oss-upload')?></label></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Default but not recommended, which occupy lots of storage and mess up files structure','oss-upload')?></small></p>
            <p <?php oss_upload_show_more('oss_upload_example'); ?>><small><code>photo-{width}x{height}.jpg</code></small></p><br/>
            <p><label><input name="ouop[oss_thumbnail]" type="radio" value="2" <?php checked(ouop('oss_thumbnail'),2);?> /> <?php _e('Use OSS Image Service for a easy and simple way','oss-upload')?></label>
            <?php echo oss_upload_link('https://help.aliyun.com/document_detail/44688.html', '?', 'blank'); ?></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Most recommended, which is compatible when you change your oss or thumbnails setting frequently','oss-upload')?></small></p>
            <p <?php oss_upload_show_more('oss_upload_example'); ?>><small><code>photo.jpg?x-oss-process=image/resize,m_fill,w_{width},h_{height}</code></small></p><br/>
            <p><label><input name="ouop[oss_thumbnail]" type="radio" value="3" <?php checked(ouop('oss_thumbnail'),3);?> /> <?php _e('Use OSS Image Service for a intelligent and complicated style way','oss-upload')?></label>
            <?php echo oss_upload_link('https://help.aliyun.com/document_detail/44687.html', '?', 'blank'); ?></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Less recommended, which is more powerful but require more styles setting on your oss','oss-upload')?></small></p>
            <p <?php oss_upload_show_more('oss_upload_example'); ?>><small><code>photo.jpg?x-oss-process=style/{style}</code>: <code>thumbnail</code> <code>medium</code> <code>large</code> <code>medium_large</code> <code>post-thumbnail</code></small></p>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Featured Image', 'oss-upload')?></th>
        <td>
            <p><label>
                <input type="text" name="ouop[oss_size_width]" size="10" value="<?php echo ouop('oss_size_width')?>" <?php disabled(ouop('oss_thumbnail', 1),1);?> /> x
                <input type="text" name="ouop[oss_size_height]" size="10" value="<?php echo ouop('oss_size_height')?>" <?php disabled(ouop('oss_thumbnail', 1),1);?> />
            </label></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Set the featured image dimensions when thumbnails enabled (width x height)');?>: <code>300</code> x <code>300</code></small></p>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('URL Fixer', 'oss-upload')?></th>
        <td>
            <p><label><input name="ouop[oss_url_find]" size="60" placeholder="x,y,z" value="<?php echo ouop('oss_url_find')?>" /></label></p>
            <p><label><input name="ouop[oss_url_replace]" size="60" placeholder="a,b,c" value="<?php echo ouop('oss_url_replace')?>" /></label></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Find and replace whatever strings you want to fix the attachment url','oss-upload')?></small></p>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Default Style', 'oss-upload')?></th>
        <td>
            <p><label><input name="ouop[oss_default_style]" size="60" value="<?php echo ouop('oss_default_style')?>" />
            <?php echo oss_upload_link('https://help.aliyun.com/document_detail/44686.html', '?', 'blank'); ?></label></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Suffix added after full size image for OSS Image Service style','oss-upload')?>: <code>?abc</code> <code>-xyz</code> <code>/123</code></small></p>
            <?php if(ouop('oss_default_style')):?>
            <p <?php oss_upload_show_more('oss_upload_example'); ?>><small><code>photo.jpg<?php echo ouop('oss_default_style');?></code></small></p>
            <?php endif;?>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Style Separator', 'oss-upload')?></th>
        <td>
            <p><label><input name="ouop[oss_style_separator]" size="60" value="<?php echo ouop('oss_style_separator')?>" /> <?php echo oss_upload_link('https://help.aliyun.com/document_detail/48884.html', '?', 'blank'); ?></label></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Custom style separator for OSS Image Service style','oss-upload')?>: <code>-</code> <code>_</code> <code>/</code> <code>!</code></small></p>
            <?php if(ouop('oss_style_separator')):?>
            <p <?php oss_upload_show_more('oss_upload_example'); ?>><small><code>photo.jpg<span style="color:red"><?php echo ouop('oss_style_separator');?></span>thumbnail</code></small></p>
            <?php endif;?>
        </td></tr>
        <tr valign="top">
        <th scope="row"><?php _e('Image Editor Class', 'oss-upload')?></th>
        <td>
            <p><label><input name="ouop[oss_editor]" type="checkbox" value="1" <?php checked(ouop('oss_editor'),1);?> />
            <?php _e('Use <code>WP_Image_Editor_GD</code> as default','oss-upload')?></label></p>
            <p <?php oss_upload_show_more('oss_upload_desc'); ?>><small><?php _e('Which is more compatible and stable, check if error occurred when edit image','oss-upload')?></small></p>
        </td></tr>
        </table>
        <?php submit_button();?>
    </div>
    <?php
}

?>