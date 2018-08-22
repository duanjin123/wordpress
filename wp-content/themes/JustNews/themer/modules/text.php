<?php

function wpcom_modules_text($atts, $depth = 0) {
    $style = ' style="';
    if(isset($atts['margin-top'])) $style .= 'margin-top: '.$atts['margin-top'].';';
    if(isset($atts['margin-bottom'])) $style .= 'margin-bottom: '.$atts['margin-bottom'].';';
    $style .= '"';
    ?>
    <section class="section wpcom-modules modules-text<?php echo $depth==0?' container':'';?>" id="modules-<?php echo $atts['modules-id'];?>"<?php echo $style; ?>>
        <?php echo wpautop( do_shortcode($atts['content']) );?>
    </section>
<?php }

function wpcom_text($modules){
    $options = array(
        array(
            'tab-name' => '常规设置',
            'content' => array(
                'name' => '内容',
                'type' => 'editor',
                'desc' => '',
                'value'  => ''
            )
        ),
        array(
            'tab-name' => '风格样式',
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
    $modules->text = array(
        'name'  => '自定义内容',
        'icon'  => 'file-text-o',
        'options' => $options
    );
    return $modules;
}

wpcom::reg_module('text');