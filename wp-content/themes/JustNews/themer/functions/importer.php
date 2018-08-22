<?php

add_action('after_setup_theme', 'wpcom_demo_importer');
function wpcom_demo_importer(){
    new WPCOM_DEMO_Importer();
}

class WPCOM_DEMO_Importer{
    public function __construct(){
        $this->config = array();
        if(is_admin() && current_user_can( 'edit_theme_options' ) && version_compare( phpversion(), '5.3.2', '>=' )){
            global $wpcom_panel;
            $this->config = $wpcom_panel->get_demo_config();
            if(!empty($this->config)){
                $this->config = json_decode(json_encode($this->config),true);
                if(!class_exists('OCDI_Plugin')) require FRAMEWORK_PATH . '/importer/one-click-demo-import.php';
                add_filter( 'pt-ocdi/import_files', array($this, 'import_files') );
                add_filter( 'pt-ocdi/plugin_page_setup', array($this, 'plugin_page_setup') );
                add_action( 'pt-ocdi/after_import', array($this, 'after_import') );
            }
        }
    }

    public function import_files(){
        return $this->config;
    }

    public function plugin_page_setup( $default_settings ) {
        $default_settings['parent_slug'] = 'wpcom-panel';
        $default_settings['capability']  = 'import';
        $default_settings['menu_slug']   = 'wpcom-demo-importer';
        return $default_settings;
    }

    public function after_import( $selected_import ) {
        global $wp_version;
        $theme_options = '';
        $args = array('timeout' => 20, 'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url());
        $result = @wp_remote_get($selected_import['import_options_file_url'], $args);
        if(is_array($result)){
            $theme_options = $result['body'];
        }


        $options = json_decode($theme_options, true);
        if($options && isset($options['options'])) update_option('izt_theme_options', $options['options']);


        // menu
        $menus = $options && isset($options['menu']) ? $options['menu'] : array();

        // Get current locations
        $locations = get_theme_mod( 'nav_menu_locations' );

        // Add demo locations
        foreach ( $menus as $location => $name ) {
            $menu                 = get_term_by( 'slug', $name, 'nav_menu');
            $locations[$location] = $menu->term_id;
        }

        // Set menu locations
        set_theme_mod( 'nav_menu_locations', $locations );


        // Set widgets
        $widgets = $options && isset($options['widgets']) ? $options['widgets'] : array(); // 获取导入的小工具数据
        $sidebars = wp_get_sidebars_widgets(); // 获取边栏数据
        $widgets_options = array(); // 保存导入的小工具信息
        foreach($widgets as $k => $wgt){
            if(!empty($wgt)){
                $sItem = array();
                foreach($wgt as $i=>$v){
                    $sItem[] = $i;
                    preg_match('/(.*)-(\d+)$/i', $i, $matches);
                    if(!isset($widgets_options[$matches[1]])) $widgets_options[$matches[1]] = get_option('widget_'.$matches[1]);
                    $widgets_options[$matches[1]][$matches[2]] = $v;
                    if($matches[1]=='nav_menu'){
                        $mSlug = $v['nav_menu'];
                        if($term2 = get_term_by('slug', $mSlug, 'nav_menu')){
                            $widgets_options[$matches[1]][$matches[2]]['nav_menu'] = $term2->term_id;
                        }
                    }
                }
                $sidebars[$k] = $sItem;
            }else{
                $sidebars[$k] = array();
            }
        }
        wp_set_sidebars_widgets($sidebars);

        foreach($widgets_options as $k => $wops){
            update_option( 'widget_'.$k, $wops );
        }


        // taxonomy options
        foreach($options['taxonomy'] as $tax => $terms){
            foreach($terms as $s => $ops){
                $t = get_term_by('slug', $s, $tax);
                update_option('_'.$tax.'_'.$t->term_id, $ops);
            }
        }


        // Set homepage
        if($options['show_on_front']=='page' && $options['page_on_front']){
            $page = get_page_by_path( $options['page_on_front'] );
            update_option( 'page_on_front', $page->ID );
            update_option( 'show_on_front', 'page' );
        }else{
            update_option( 'show_on_front', 'posts' );
        }

    }
}