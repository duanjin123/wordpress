<?php
//切换标签插件
//widget xintheme_hotpost
add_action('widgets_init', function(){register_widget('xintheme_hotpost' );});
class xintheme_hotpost extends WP_Widget {

    function __construct() {
        $widget_ops = array('description' => '默认显示3个月内，评论数最多的图文列表');
        parent::__construct('xintheme_hotpost', '热评文章（图文） ', $widget_ops);
    }

    function widget($args, $instance) {
        extract( $args );

		$limit = $instance['limit'];
		$time = $instance['time'];
		$cat = $instance['cat'];
		$title = apply_filters('widget_name', $instance['title']);
		echo $before_widget;
		echo $before_title.$title.$after_title; 
        echo xintheme_widget_hotpost($limit,$cat,$time);
        echo $after_widget;	
    }

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['limit'] = strip_tags($new_instance['limit']);
		$instance['time'] = strip_tags($new_instance['time']);
		$instance['cat'] = strip_tags($new_instance['cat']);
		return $instance;
	}
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 
			'title' => '热评文章',
			'limit' => '5',
			'time' => '3 month ago',
			) 
		);
		$title = strip_tags($instance['title']);
		$limit = strip_tags($instance['limit']);		
		$instance['cat'] = ! empty( $instance['cat'] ) ? esc_attr( $instance['cat'] ) : '';

?>
<p>
	<label> 显示标题：（例如：热门文章）
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
	</label>
</p>

<p>
	<label>
		显示某个时间段：
		
		<input class="widefat" id="<?php echo $this->get_field_id('time'); ?>" name="<?php echo $this->get_field_name('time'); ?>" type="text" value="<?php echo $instance['time']; ?>" />
		<p>填写格式：（注意空格）</p>
		<p>1周内：1 week ago</p>
		<p>1月内：1 month ago</p>
		<p>1年内：1 year ago</p>
		
	</label>
</p>
<p>
	<label> 显示文章数目：
		<input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo $instance['limit']; ?>" />
	</label>
</p>
<p>
	<label>
		分类限制：
		<a style="font-weight:bold;color:#f60;text-decoration:none;" href="javascript:;" title="格式：1,2 &nbsp;表限制ID为1,2分类的文章&#13;格式：-1,-2 &nbsp;表排除分类ID为1,2的文章&#13;也可直接写1或者-1；注意逗号须是英文的">？</a>
		<input style="width:100%;" id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>" type="text" value="<?php echo $instance['cat']; ?>" size="24" />
	</label>
</p>
<?php
	}
}
function xintheme_widget_hotpost($limit,$cat,$time){ 
?>
		<div class="posts">
             <?php 
                $args = array(
                    'post_type'         => 'post',
                    'post_status'       => 'publish',
                    'posts_per_page'    => $limit,
                    'orderby'           => 'comment_count',
                    'order'             => 'DESC',
					'cat'              => $cat,
					'ignore_sticky_posts' => 1,
					'date_query' => array(
						array(
						'after' => $time,
						),
					),
					'tax_query' => array( array( 
						'taxonomy' => 'post_format',
						'field' => 'slug',
						'terms' => array(
							//请根据需要保留要排除的文章形式
							'post-format-aside',
							
							),
						'operator' => 'NOT IN',
						) ),

                );
				$hot_posts = new WP_Query( $args );
				if ( !$hot_posts->have_posts() ) :?>
				<p>暂无文章</p>
				<?php else:
				while ( $hot_posts->have_posts() ) :$hot_posts->the_post(); ?>
				<div>
					<div class="entry-thumbnail">
						<a class="u-permalink" href="<?php the_permalink(); ?>"></a>
						<img class="lazyload" data-src="<?php echo wpjam_get_post_thumbnail_src($post,array(150,150), $crop=1);?>">
					</div>
					<header class="entry-header">
					<h6 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h6>
					<div class="entry-meta">
						<span><?php comments_popup_link ('0 条评论','1 条评论','% 条评论'); ?></span>
					</div>
					</header>
				</div>
				 <?php endwhile; endif; ?>
		</div>	
<?php }?>
