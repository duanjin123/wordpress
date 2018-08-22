<?php

function wpcom_modules_slider($atts, $depth = 0) {
    $style = ' style="';
    if(isset($atts['margin-top'])) $style .= 'margin-top: '.$atts['margin-top'].';';
    if(isset($atts['margin-bottom'])) $style .= 'margin-bottom: '.$atts['margin-bottom'].';';
    $style .= '"';
    if($atts['alias']) {
        echo do_shortcode('<section class="section wpcom-modules modules-slider'.($depth==0?' container':'').'" id="modules-' . $atts['modules-id'] . '"'.$style.'>[rev_slider alias="' . $atts['alias'] . '"]</section>');
    }
}

function wpcom_slider($modules){
    $options = array(
        array(
            'tab-name' => '常规设置',
            'alias' => array(
                'name' => '选择滑块',
                'type' => 'select',
                'desc' => '',
                'value'  => 'home',
                'options' => wpcom::get_all_sliders()
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
                'value'  => '0'
            )
        )
    );
    $modules->slider = array(
        'name'  => 'Slider Revolution',
        'icon'  => 'desktop',
        'options' => $options
    );
    return $modules;
}

if(shortcode_exists("rev_slider")) wpcom::reg_module('slider');