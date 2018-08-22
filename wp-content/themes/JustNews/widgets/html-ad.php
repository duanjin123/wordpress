<?php
/**
 * HTML 广告代码
 * Created by PhpStorm.
 * User: Lomu
 * Date: 17/4/23
 * Time: 下午2:36
 */


class wpcom_html_ad_widget extends WP_Widget {

    public function __construct() {
        parent::__construct( 'html-ad', '#WPCOM#广告代码', array(
            'classname'   => 'widget_html_ad',
            'description' => '适合添加html广告代码，无边框',
        ) );
    }

    public function widget( $args, $instance ) {

        $html  = empty( $instance['html'] ) ? '' : $instance['html'];

        echo $args['before_widget'];

        echo do_shortcode($html);

        echo $args['after_widget'];
    }

    function update( $new_instance, $instance ) {
        $instance['html']  = $new_instance['html'];
        return $instance;
    }

    function form( $instance ) {
        $html = empty( $instance['html'] ) ? '' :  $instance['html'];
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'html' ) ); ?>">代码：</label>
            <textarea name="<?php echo esc_attr($this->get_field_name('html')); ?>"
                      id="<?php echo esc_attr($this->get_field_id('html')); ?>" style="width: 100%;" rows="10"><?php echo $html;?></textarea>
        </p>
    <?php
    }
}

// register widget
function wpcom_html_ad_widget() {
    register_widget( 'wpcom_html_ad_widget' );
}
add_action( 'widgets_init', 'wpcom_html_ad_widget' );