<?php

class wpcom_term_meta{
    public function __construct( $tax, $metas ) {

        $this->metas = $metas;
        $this->tax = $tax;

        add_action( $tax . '_add_form_fields', array($this, 'add'), 10, 2 );
        add_action( $tax . '_edit_form_fields', array($this, 'edit'), 10, 2 );
        add_action( 'created_' . $tax, array($this, 'save'), 10, 2 );
        add_action( 'edited_' . $tax, array($this, 'save'), 10, 2 );
    }

    function add(){
        if(empty($this->metas)) return;
        foreach($this->metas as $meta) {
            if ($meta['type'] == 'upload') {
                wp_enqueue_script("panel", FRAMEWORK_URI . "/assets/js/panel.js", array('jquery', 'jquery-ui-core', 'wp-color-picker'), FRAMEWORK_VERSION, true);
                wp_enqueue_media();
            }

            $thml = '<div class="form-field">
            <label for="wpcom_' . $meta['name'] . '">' . $meta['title'] . '</label>
            ' . $this->get_html( $meta ) . '
            ' . (isset($meta['desc']) ? '<p>' . $meta['desc'] . '</p>' : '') . '</div>';

            echo $thml;
        }
    }

    function edit($term){
        if(empty($this->metas)) return;
        foreach($this->metas as $meta) {
            if ($meta['type'] == 'upload') {
                wp_enqueue_script("panel", FRAMEWORK_URI . "/assets/js/panel.js", array('jquery', 'jquery-ui-core', 'wp-color-picker'), FRAMEWORK_VERSION, true);
                wp_enqueue_media();
            }
            $html = '<tr class="form-field">
            <th scope="row" valign="top"><label for="wpcom_' . $meta['name'] . '">' . $meta['title'] . '</label></th>
            <td>
            ' . $this->get_html($meta, $term->term_id) . '
            ' . (isset($meta['desc']) ? '<p class="description">' . $meta['desc'] . '</p>' : '') . '
            </td>
        </tr>';
            echo $html;
        }
    }

    function save($term_id){
        if(empty($this->metas)) return;
        $values = array();
        foreach($this->metas as $meta) {
            if (isset($_POST[$meta['name']])) {
                $values[$meta['name']] = $_POST[$meta['name']];
            }
        }
        if(!empty($values)){
            $key = '_' . $this->tax . '_' . $term_id;
            $ax_options = $values;
            update_option($key, $ax_options);
        }
    }

    function get_html( $meta, $term_id = 0 ){
        $html = '';
        $val = '';
        if($term_id){
            $term_options = get_option('_'.$this->tax.'_'.$term_id);
            if(isset($term_options[$meta['name']])){
                $val = $term_options[$meta['name']];
            }else{
                $val = get_option(($this->tax=='category'?'cat':$this->tax).'_'.$meta['name'].'_'.$term_id);
            }
        }
        switch($meta['type']){
            case 'select':
                $html = '<select name="'.$meta['name'].'" id="wpcom_'.$meta['name'].'">';
                if($meta['options']){
                    foreach($meta['options'] as $k=>$v){
                        $html .= '<option value="'.$k.'"'.($k==$val?' selected':'').'>'.$v.'</option>';
                    }
                }
                $html .= '</select>';
                break;
            case 'input':
                $html = '<input type="text" name="'.$meta['name'].'" id="wpcom_'.$meta['name'].'" value="'.$val.'">';
                break;
            case 'textarea':
                $html = '<textarea rows="4" name="'.$meta['name'].'" id="wpcom_'.$meta['name'].'">'.$val.'</textarea>';
                break;
            case 'upload':
                $html = '<input style="width: 50%;" type="text" name="'.$meta['name'].'" id="wpcom_'.$meta['name'].'" value="'.$val.'">
                <button id="wpcom_'.$meta['name'].'_upload" type="button" class="button upload-btn">上传图片</button>';
                break;
        }

        return $html;
    }
}

add_action('admin_init', 'wpcom_tax_meta');
function wpcom_tax_meta(){
    $wpcom_tax_metas = apply_filters( 'wpcom_tax_metas', array() );
    foreach($wpcom_tax_metas as $tax => $metas){
        new wpcom_term_meta($tax, $metas);
    }
}