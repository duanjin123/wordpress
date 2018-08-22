<?php

/**
 * 显示最新新闻文章
 * Latest News
 */

class wpcom_profile_widget extends WP_Widget {

    public function __construct() {
        parent::__construct( 'profile', '#WPCOM#用户信息', array(
            'classname'   => 'widget_profile',
            'description' => '边栏用户信息简介，只在文章详情页显示',
        ) );
    }

    public function widget( $args, $instance ) {
        $num = empty( $instance['number'] ) ? 5 : absint( $instance['number'] );

        if ( is_singular('post') ) :
            $author = get_the_author_meta( 'ID' );
            um_fetch_user( $author );
            $author_url = um_user_profile_url();
            $cover_photo = um_user('cover_photo', 600);
            echo $args['before_widget'];
            if($cover_photo) {
                echo um_user('cover_photo', 600);
            } else { ?>
                <div class="cover_photo"></div>
            <?php } ?>
            <div class="avatar-wrap">
                <a target="_blank" href="<?php echo $author_url; ?>" class="avatar-link"><?php echo get_avatar( $author, 120 );?></a></div>
            <div class="profile-info">
                <p><span class="author-name"><?php echo um_user('display_name'); ?></span><span class="author-title"><?php echo get_role_name($author);?></span></p>
                <p class="author-description"><?php the_author_meta('description');?></p>
            </div>
            <div class="profile-posts">
                <h3 class="widget-title"><span>最近文章</span></h3>
                <?php
                global $post;
                $posts = get_posts( 'author='.$author.'&posts_per_page='.$num );
                if ($posts) : echo '<ul>'; foreach ( $posts as $post ) { setup_postdata( $post ); ?>
                    <li><a href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"><?php the_title();?></a></li>
                <?php } echo '</ul>'; else :?>
                    <p style="color:#999;font-size: 12px;text-align: center;padding: 10px 0;margin:0;">暂无内容</p>
                <?php endif; wp_reset_postdata(); ?>
            </div>
            <?php echo $args['after_widget'];
        endif; // End check for ephemeral posts.
    }

    function update( $new_instance, $instance ) {
        $instance['number'] = empty( $new_instance['number'] ) ? 5 : absint( $new_instance['number'] );
        return $instance;
    }

    function form( $instance ) {
        $number = empty( $instance['number'] ) ? 5 : absint( $instance['number'] );
        ?>
        <p><label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>">文章数量：</label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3"></p>
    <?php
    }
}

// register widget
function register_wpcom_profile_widget() {
    if(function_exists('um_get_core_page'))
        register_widget( 'wpcom_profile_widget' );
}
add_action( 'widgets_init', 'register_wpcom_profile_widget' );

?>