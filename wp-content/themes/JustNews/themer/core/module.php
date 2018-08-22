<?php
/**
 * 模块注册
 * Created by PhpStorm.
 * User: Lomu
 * Date: 18/1/30
 * Time: 下午5:12
 */

class WPCOM_Module{
    private $options;
    function __construct($id, $name, $options = array(), $icon='') {
        $this->id = $id;
        $this->name = $name;
        $this->options = $options;
        $this->icon = $icon;
        add_action( 'init', array( $this, '_register_module' ) );
    }

    function display( $atts, $depth = 0 ){
        $classes = 'modules-'.$this->id;
        $more_classes = $this->classes( $atts, $depth );
        $classes .= $more_classes ? ' ' . $more_classes : '';
        ?>
        <section class="section wpcom-modules <?php echo $classes;?>" id="modules-<?php echo $atts['modules-id'];?>" <?php echo $this->_style_inline($atts); ?>>
            <?php $this->template($atts, $depth);?>
        </section>
    <?php }

    function style_inline_default( $atts ){
        $style = '';
        if(isset($atts['margin-top'])) $style .= 'margin-top: '.$atts['margin-top'].';';
        if(isset($atts['margin-bottom'])) $style .= 'margin-bottom: '.$atts['margin-bottom'].';';
        if(isset($atts['padding-top'])) $style .= 'padding-top: '.$atts['padding-top'].';';
        if(isset($atts['padding-bottom'])) $style .= 'padding-bottom: '.$atts['padding-bottom'].';';
        return $style;
    }

    function _style_inline( $atts ){
        $style = '';
        $default = $this->style_inline_default( $atts );
        $more = $this->style_inline( $atts );
        if($default || $more) $style = 'style="'.$default.''.$more.'"';
        return $style;
    }

    function classes( $atts, $depth ){
        $classes = $depth==0 ? 'container' : '';
        return $classes;
    }

    function module_options( $modules ){
        $modules->{$this->id} = array(
            'name'  => $this->name,
            'icon'  => $this->icon,
            'options' => $this->options
        );
        return $modules;
    }

    function _register_module(){
        add_action('wpcom_modules_'.$this->id, array( $this, 'display' ), 10, 2);
        add_filter('wpcom_modules', array( $this, 'module_options' ));
    }

    function template($atts, $depth){}
    function style($atts){}
    function style_inline($atts){}
}

if( !function_exists('register_module') ){
    function register_module( $module_class ){
        global $wpcom_modules;
        if(!isset($wpcom_modules)) $wpcom_modules = array();
        $module = new $module_class();
        $wpcom_modules[$module->id] = $module;
    }
}