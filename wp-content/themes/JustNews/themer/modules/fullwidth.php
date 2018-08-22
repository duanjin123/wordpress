<?php

function wpcom_modules_fullwidth($atts, $depth = 0) {
    $style = ' style="';
    if(isset($atts['bg-image-shadow']) && $atts['bg-image-shadow']) $style .= 'position: relative;';
    if(isset($atts['margin-top'])) $style .= 'margin-top: '.$atts['margin-top'].';';
    if(isset($atts['margin-bottom'])) $style .= 'margin-bottom: '.$atts['margin-bottom'].';';
    if(isset($atts['padding-top'])) $style .= 'padding-top: '.$atts['padding-top'].';';
    if(isset($atts['padding-bottom'])) $style .= 'padding-bottom: '.$atts['padding-bottom'].';';
    if(isset($atts['bg-color']) && $atts['bg-color']) $style .= 'background-color: '.$atts['bg-color'].';';
    if(isset($atts['bg-image']) && $atts['bg-image']) $style .= 'background-image: url('.$atts['bg-image'].');';
    if(isset($atts['bg-image-repeat']) && $atts['bg-image-repeat']) $style .= 'background-repeat: '.$atts['bg-image-repeat'].';';
    if(isset($atts['bg-image-size']) && $atts['bg-image-size']=='1') $style .= 'background-size: cover;';
    if(isset($atts['bg-image-position']) && $atts['bg-image-position']) $style .= 'background-position: '.$atts['bg-image-position'].';';
    if(isset($atts['bg-image-attachment']) && $atts['bg-image-attachment']=='1') $style .= 'background-attachment: fixed;';
    $style .= '"';
    ?>
    <section class="section wpcom-modules modules-fullwidth" id="modules-<?php echo $atts['modules-id'];?>"<?php echo $style; ?>>
        <?php if(isset($atts['bg-image-shadow']) && $atts['bg-image-shadow']=='1'){?><div class="module-shadow"></div><?php } ?>
        <?php if(isset($atts['bg-image-shadow']) && $atts['bg-image-shadow']=='2'){?><div class="module-shadow module-shadow-white"></div><?php } ?>
        <div class="modules-fw-inner container<?php echo isset($atts['fluid']) && $atts['fluid']?'':'-fluid';?>"<?php     echo isset($atts['bg-image-shadow']) && $atts['bg-image-shadow'] ? ' style="position: relative;"':''; ?>>
            <?php if(isset($atts['modules']) && count($atts['modules'])){ foreach ($atts['modules'] as $module) {
                $module['settings']['modules-id'] = $module['id'];
                $module['settings']['fullwidth'] = isset($atts['fluid']) && $atts['fluid'] ? 0 : 1;
                do_action('wpcom_modules_' . $module['type'], $module['settings'], $depth+1);
            } } ?>
        </div>
    </section>
<?php }

function wpcom_fullwidth($modules){
    $options = array(
        array(
            'tab-name' => '常规设置',
            'fluid' => array(
                'name' => '固定宽度',
                'type' => 'toggle',
                'desc' => '模块内容宽度固定，居中显示；否则内容宽度不固定，为100%',
                'value'  => '1'
            )
        ),
        array(
            'tab-name' => '风格样式',
            'bg-color' => array(
                'name' => '背景颜色',
                'type' => 'color',
                'desc' => '',
                'value'  => ''
            ),
            'bg-image' => array(
                'name' => '背景图片',
                'type' => 'upload',
                'desc' => '',
                'value'  => ''
            ),
            'bg-image-size' => array(
                'name' => '背景铺满',
                'type' => 'toggle',
                'desc' => '自动调整背景图片显示，使背景图片完全覆盖内容区域，选择了背景平铺请关闭此选项',
                'value'  => '1'
            ),
            'bg-image-repeat' => array(
                'name' => '背景平铺',
                'type' => 'select',
                'desc' => '选择了背景平铺的话，则需要关闭<b>背景铺满</b>选项',
                'value'  => 'no-repeat',
                'options' => array(
                    'no-repeat' => '不平铺',
                    'repeat' => '平铺',
                    'repeat-x' => '水平平铺',
                    'repeat-y' => '垂直平铺'
                )
            ),
            'bg-image-position' => array(
                'name' => '背景位置',
                'type' => 'select',
                'desc' => '分别为左右对齐方式和上下对齐方式',
                'value'  => 'center center',
                'options' => array(
                    'left top' => '左 上',
                    'left center' => '左 中',
                    'left bottom' => '左 下',
                    'center top' => '中 上',
                    'center center' => '中 中',
                    'center bottom' => '中 下',
                    'right top' => '右 上',
                    'right center' => '右 中',
                    'right bottom' => '右 下',
                )
            ),
            'bg-image-attachment' => array(
                'name' => '背景固定',
                'type' => 'toggle',
                'desc' => '背景图片固定，不跟随滚动，若开启则需要确保图片高度足够',
                'value'  => ''
            ),
            'bg-image-shadow' => array(
                'name' => '背景处理',
                'type' => 'select',
                'desc' => '优化处理背景图片',
                'value'  => '0',
                'options' => array(
                    '0' => '不处理',
                    '1' => '暗化处理',
                    '2' => '亮化处理'
                )
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
                'value'  => '0'
            ),
            'padding-top' => array(
                'name' => '上内边距',
                'type' => 'text',
                'desc' => '内间距为模块内容区域与边界的距离，单位建议为px。即 padding-top 值，例如： 10px',
                'value'  => '20px'
            ),
            'padding-bottom' => array(
                'name' => '下内边距',
                'type' => 'text',
                'desc' => '内间距为模块内容区域与边界的距离，单位建议为px。即 padding-bottom 值，例如： 10px',
                'value'  => '20px'
            )
        )
    );
    $modules->fullwidth = array(
        'name'  => '全宽模块',
        'icon'  => 'tv',
        'options' => $options
    );
    return $modules;
}

wpcom::reg_module('fullwidth');