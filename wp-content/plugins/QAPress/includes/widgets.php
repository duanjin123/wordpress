<?php

class QAPress_Widget_New extends WP_Widget {

    public function __construct() {
        parent::__construct( 'qapress-new', '#QAPress#发布新帖', array(
            'classname'   => 'widget_qapress_new',
            'description' => '问答插件发布新帖按钮',
        ) );
    }

    public function widget( $args, $instance ) {
        global $qa_options;
        if(!isset($qa_options)) $qa_options = get_option('qa_options');

        $text = empty( $instance['text'] ) ? '发布新帖' : $instance['text'];
        $show = isset($instance['show']) && $instance['show']=='1' ? '1' :  '0';
    
        if($show=='0'){
            $qa_page_id = $qa_options['list_page'];
            if(!is_page($qa_page_id)) return false;
        }
        
        $new_page_id = $qa_options['new_page'];
        $new_url = get_permalink($new_page_id);

        echo $args['before_widget'];
        echo '<a class="q-btn-new" href="'.$new_url.'">'.$text.'</a>';
        echo $args['after_widget'];
    }

    function update( $new_instance, $instance ) {
        $instance['text'] = $new_instance['text'];
        $instance['show'] = $new_instance['show'];
        return $instance;
    }

    function form( $instance ) {
        $text = empty( $instance['text'] ) ? '发布新帖' :  $instance['text'];
        $show = isset($instance['show']) && $instance['show']=='1' ? '1' :  '0';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>">按钮名：</label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>"><br>
            <span>支持html代码，如网站支持Font Awesome图标，可以填写：<br><code><?php echo esc_attr('<i class="fa fa-edit"></i> 发布新帖');?></code></span>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show' ) ); ?>">按钮显示：</label>
            <br>
            <input id="<?php echo esc_attr( $this->get_field_id( 'show' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show' ) ); ?>" type="radio" value="0"<?php echo $show=='0'?' checked':''?>>仅问答页面
            <br>
            <input id="<?php echo esc_attr( $this->get_field_id( 'show' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show' ) ); ?>" type="radio" value="1"<?php echo $show=='1'?' checked':''?>>全部显示
        </p>
    <?php
    }
}

// register widget
function QAPress_widget_new() {
    register_widget( 'QAPress_Widget_New' );
}
add_action( 'widgets_init', 'QAPress_widget_new' );