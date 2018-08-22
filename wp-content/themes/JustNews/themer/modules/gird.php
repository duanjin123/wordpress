<?php

class WPCOM_Module_gird extends WPCOM_Module {
    function __construct() {
        parent::__construct( 'gird', '栅格布局', $this->options(), 'columns' );
    }

    function classes( $atts, $depth ){
        $classes = $depth==0 ? 'container' : '';
        $no_padding = isset($atts['no-padding']) && $atts['no-padding']=='1' ? 1 : 0;
        $classes .= $no_padding ? ' gird-no-padding' : '';
        return $classes;
    }

    function template($atts, $depth){
        ?>
        <?php for($i=0;$i<count($atts['columns']);$i++){ ?>
            <div class="col-md-<?php echo $atts['columns'][$i]?> modules-gird-inner">
                <?php if(isset($atts['girds']) && isset($atts['girds'][$i])){ foreach ($atts['girds'][$i] as $v) {
                    $v['settings']['modules-id'] = $v['id'];
                    do_action('wpcom_modules_' . $v['type'], $v['settings'], $depth+1);
                } } ?>
            </div>
        <?php } ?>
    <?php }

    function options(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'columns' => array(
                    'name' => '栅格列数',
                    'type' => 'columns',
                    'desc' => '设置栅格的列数，然后在下面设置每列对应的宽度，页面采用12列计算，下面所有栅格相加等于12即可，超过12将会换行，小于12页面无法填满',
                    'value'  => array('6', '6')
                )
            ),
            array(
                'tab-name' => '风格样式',
                'no-padding' => array(
                    'name' => '取消边距',
                    'type' => 'toggle',
                    'desc' => '取消边距后栅格左右没有间距',
                    'value'  => '0'
                ),
                'margin-top' => array(
                    'name' => '上外边距',
                    'type' => 'text',
                    'desc' => '模块离上一个模块/元素的间距，单位建议为px。即 margin-top 值，例如： 10px',
                    'value'  => '0'
                ),
                'margin-bottom' => array(
                    'name' => '下外边距',
                    'type' => 'text',
                    'desc' => '模块离上一个模块/元素的间距，单位建议为px。即 margin-bottom 值，例如： 10px',
                    'value'  => '20px'
                )
            )
        );
        return $options;
    }
}

register_module( 'WPCOM_Module_gird' );