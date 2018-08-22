<?php
/**
 * 图片广告
 * Created by PhpStorm.
 * User: Lomu
 * Date: 17/4/23
 * Time: 下午2:36
 */


class wpcom_image_ad_widget extends WP_Widget {

    public function __construct() {
        parent::__construct( 'image-ad', '#WPCOM#图片广告', array(
            'classname'   => 'widget_image_ad',
            'description' => '可以添加图片链接广告',
        ) );
    }

    public function widget( $args, $instance ) {

        $title  = empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );
        $image = empty( $instance['image'] ) ? '' : esc_url( $instance['image'] );
        $url = empty( $instance['url'] ) ? '' :  esc_url( $instance['url'] );
        $target = $instance['target'] ? ' target="_blank"' : '';
        $nofollow = $instance['nofollow'] ? ' rel="nofollow"' : '';

        echo $args['before_widget'];
        if($url){ ?>
            <a href="<?php echo $url;?>"<?php echo $target.$nofollow;?>>
                <img src="<?php echo $image;?>" alt="<?php echo $title;?>">
            </a>
        <?php } else { ?>
            <img src="<?php echo $image;?>" alt="<?php echo $title;?>">
        <?php }

        echo $args['after_widget'];
    }

    function update( $new_instance, $instance ) {
        $instance['title']  = strip_tags( $new_instance['title'] );
        $instance['image']  = esc_url( $new_instance['image'] );
        $instance['url']  = esc_url( $new_instance['url'] );
        $instance['target']  = $new_instance['target'];
        $instance['nofollow']  = $new_instance['nofollow'];

        return $instance;
    }

    function form( $instance ) {
        wp_enqueue_script("panel", FRAMEWORK_URI."/assets/js/panel.js", array('jquery', 'jquery-ui-core', 'wp-color-picker'), FRAMEWORK_VERSION, true);
        wp_enqueue_media();
        $title  = empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );
        $image = empty( $instance['image'] ) ? '' : esc_url( $instance['image'] );
        $url = empty( $instance['url'] ) ? '' :  esc_url( $instance['url'] );
        $target = empty( $instance['target'] ) ? '' :  $instance['target'];
        $nofollow = empty( $instance['nofollow'] ) ? '' :  $instance['nofollow'];
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'wpcom' ); ?></label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $title; ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'image' ) ); ?>">图片：</label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image' ) ); ?>" type="text" value="<?php echo $image; ?>">
            <button id="<?php echo esc_attr( $this->get_field_id( 'image' ) ); ?>_upload" type="button" class="button upload-btn">上传</button>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>">链接：</label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'url' ) ); ?>" type="text" value="<?php echo $url; ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>">新窗口打开：</label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'target' ) ); ?>" type="checkbox" value="1"<?php echo ($target?' checked':'')?>>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'nofollow' ) ); ?>">nofollow：</label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'nofollow' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nofollow' ) ); ?>" type="checkbox" value="1"<?php echo ($nofollow?' checked':'')?>>
        </p>
    <?php
    }
}

// register widget
function wpcom_image_ad_widget() {
    register_widget( 'wpcom_image_ad_widget' );
}
add_action( 'widgets_init', 'wpcom_image_ad_widget' );