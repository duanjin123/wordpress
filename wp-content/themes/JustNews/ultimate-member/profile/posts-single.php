<?php
global $is_author, $options;
$show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
?>
	<ul class="article-list">
	<?php while ($ultimatemember->shortcodes->loop->have_posts()) { global $post; $ultimatemember->shortcodes->loop->the_post(); $post_id = get_the_ID(); ?>
        <?php get_template_part( 'templates/list' , 'default' ); ?>
	<?php } ?>
	</ul>
	
	<?php if ( isset($ultimatemember->shortcodes->modified_args) && $ultimatemember->shortcodes->loop->have_posts() && $ultimatemember->shortcodes->loop->found_posts >= 10 ) { ?>
	
		<div class="um-load-items load-more-wrap">
			<a href="#" class="um-ajax-paginate load-more" data-hook="um_load_posts" data-args="<?php echo $ultimatemember->shortcodes->modified_args; ?>"><?php _e('load more posts','ultimatemember'); ?></a>
		</div>
		
	<?php } ?>