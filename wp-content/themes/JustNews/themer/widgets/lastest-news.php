<?php

/**
 * 显示最新新闻文章
 * Latest News
 */

class wpcom_lastest_news_widget extends WP_Widget {

    public function __construct() {
        parent::__construct( 'lastest-news', '#WPCOM#文章列表', array(
            'classname'   => 'widget_lastest_news',
            'description' => '选择指定分类文章，适合用于显示文章列表、新闻动态',
        ) );
    }

    public function widget( $args, $instance ) {
        $category = $instance['category'];
        $orderby_id = empty( $instance['orderby'] ) ? 0 :  $instance['orderby'];

        $number = empty( $instance['number'] ) ? 10 : absint( $instance['number'] );

        $orderby = 'date';
        if($orderby_id==1){
            $orderby = 'comment_count';
        }else if($orderby_id==2){
            $orderby = 'meta_value_num';
        }else if($orderby_id==3){
            $orderby = 'rand';
        }

        $parg = array(
            'cat' => $category,
            'showposts' => $number,
            'orderby' => $orderby,
            'ignore_sticky_posts' => 1
        );
        if($orderby=='meta_value_num') $parg['meta_key'] = 'views';

        $posts = new WP_Query( $parg );

        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }

        if ( $posts->have_posts() ) : ?>
            <ul>
                <?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
                    <li><a href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"><?php the_title();?></a></li>
                <?php endwhile; wp_reset_postdata();?>
            </ul>
        <?php

        endif; // End check for ephemeral posts.
        echo $args['after_widget'];
    }

    function update( $new_instance, $instance ) {
        $instance['title']  = strip_tags( $new_instance['title'] );
        $instance['number'] = empty( $new_instance['number'] ) ? 5 : absint( $new_instance['number'] );
        $instance['category'] = $new_instance['category'] ? $new_instance['category'] : 0;
        $instance['orderby'] = $new_instance['orderby'] ? $new_instance['orderby'] : 0;

        return $instance;
    }

    function form( $instance ) {
        $title  = empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );
        $number = empty( $instance['number'] ) ? 10 : absint( $instance['number'] );
        $category = empty( $instance['category'] ) ? 0 :  $instance['category'];
        $orderby = empty( $instance['orderby'] ) ? 0 :  $instance['orderby'];
        ?>
        <p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'wpcom' ); ?></label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></p>

        <p><label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _e( 'Posts number:', 'wpcom' ); ?></label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3"></p>

        <p><label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php _e( 'Category:', 'wpcom' ); ?></label>
        <select id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>">
            <option value="0">全部分类</option>
            <?php foreach ( wpcom::category() as $k => $v ) : ?>
                <option value="<?php echo esc_attr( $k ); ?>"<?php selected( $category, $k ); ?>><?php echo $v; ?></option>
            <?php endforeach; ?>
        </select>
        <p><label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php _e( 'Order by:', 'wpcom' ); ?></label>
        <select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
            <option value="0">发布时间</option>
            <option value="1"<?php selected( $orderby, 1 ); ?>>评论数</option>
            <option value="2"<?php selected( $orderby, 2 ); ?>>浏览数(需安装WP-PostViews插件)</option>
            <option value="3"<?php selected( $orderby, 3 ); ?>>随机排序</option>
        </select>
    <?php
    }
}

// register widget
function register_wpcom_lastest_news_widget() {
    register_widget( 'wpcom_lastest_news_widget' );
}
add_action( 'widgets_init', 'register_wpcom_lastest_news_widget' );

?>