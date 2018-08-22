<?php
/**
 * 最新评论小工具
 * Created by PhpStorm.
 * User: Lomu
 * Date: 17/4/22
 * Time: 下午6:33
 */


class wpcom_comments_widget extends WP_Widget {

    public function __construct() {
        parent::__construct( 'comments', '#WPCOM#最新评论', array(
            'classname'   => 'widget_comments',
            'description' => '显示网站最新的评论列表',
        ) );
    }

    public function widget( $args, $instance ) {
        $number = empty( $instance['number'] ) ? 10 : absint( $instance['number'] );

        $comments_query = new WP_Comment_Query();
        $comments = $comments_query->query( array( 'number' => $number ) );

        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }

        if ( $comments ) : ?>
            <ul>
                <?php foreach ( $comments as $comment ) :
                    if($comment->user_id){
                        if(function_exists('um_get_core_page')){
                            um_fetch_user( $comment->user_id );
                            $author_url = um_user_profile_url();
                            $display_name = um_user('display_name');
                        }else{
                            $author_url = get_author_posts_url( $comment->user_id );
                            $display_name = get_comment_author( $comment->comment_ID );
                        }
                    }else{
                        $author_url = 'javascript:;';
                        $display_name = $comment->comment_author;
                    }
                    ?>
                    <li>
                        <div class="comment-info">
                            <a href="<?php echo $author_url;?>" target="_blank">
                                <?php echo get_avatar( ($comment->user_id?$comment->user_id:$comment->comment_author_email), 60 );?>
                            </a>
                            <a class="comment-author" href="<?php echo $author_url;?>" target="_blank">
                                <?php echo $display_name?$display_name:'匿名';?>
                            </a>
                            <span><?php echo date('n月j日',strtotime($comment->comment_date)); ?></span>
                        </div>
                        <div class="comment-excerpt">
                            <p><?php echo utf8_excerpt($comment->comment_content, 55);?></p>
                        </div>
                        <p class="comment-post">
                            评论于 <a href="<?php echo get_permalink($comment->comment_post_ID); ?>" target="_blank"><?php echo get_the_title($comment->comment_post_ID);?></a>
                        </p>
                    </li>
                <?php endforeach;?>
            </ul>
        <?php
        else:
            echo '<p style="color:#999;font-size: 12px;text-align: center;padding: 10px 0;margin:0;">暂无评论</p>';
        endif; // End check for ephemeral posts.
        echo $args['after_widget'];
    }

    function update( $new_instance, $instance ) {
        $instance['title']  = strip_tags( $new_instance['title'] );
        $instance['number'] = empty( $new_instance['number'] ) ? 5 : absint( $new_instance['number'] );

        return $instance;
    }

    function form( $instance ) {
        $title  = empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );
        $number = empty( $instance['number'] ) ? 10 : absint( $instance['number'] );
        ?>
        <p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'wpcom' ); ?></label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></p>

        <p><label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _e( 'Posts number:', 'wpcom' ); ?></label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3"></p>

    <?php
    }
}

// register widget
function register_wpcom_comments_widget() {
    register_widget( 'wpcom_comments_widget' );
}
add_action( 'widgets_init', 'register_wpcom_comments_widget' );

?>