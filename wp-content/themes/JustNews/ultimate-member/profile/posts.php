<?php
$current_user = wp_get_current_user();
global $is_author;
$is_author = 0;
if($current_user->ID && um_user('ID') == $current_user->ID ){
    if( current_user_can('edit_published_posts') )
        $is_author = 1;
	$query_posts = $ultimatemember->query->make('post_type=post&post_status=draft,pending,publish&posts_per_page=10&offset=0&author=' . um_user('ID') );
}else{
	$query_posts = $ultimatemember->query->make('post_type=post&posts_per_page=10&offset=0&author=' . um_user('ID') );
}

?>

<?php $ultimatemember->shortcodes->loop = apply_filters('um_profile_query_make_posts', $query_posts ); ?>

<?php if ( $ultimatemember->shortcodes->loop->have_posts()) { ?>
			
	<?php $ultimatemember->shortcodes->load_template('profile/posts-single'); ?>
	
	<div class="um-ajax-items">
	
		<!--Ajax output-->
		
		<?php if ( $ultimatemember->shortcodes->loop->found_posts >= 10 ) { ?>
		
		<div class="um-load-items load-more-wrap">
			<a href="#" class="um-ajax-paginate load-more" data-hook="um_load_posts" data-args="post,10,10,<?php echo um_user('ID'); ?>"><?php _e('load more posts','ultimatemember'); ?></a>
		</div>
		
		<?php } ?>
		
	</div>
		
<?php } else {
	$emo = um_get_option('profile_empty_text_emo');
	if ( $emo ) {
		$emo = '<i class="um-faicon-frown-o"></i>';
	} else {
		$emo = false;
	}
	?>

	<div class="um-profile-note"><?php echo $emo;?><span><?php echo ( um_profile_id() == get_current_user_id() ) ? __('You have not created any posts.','ultimatemember') : __('This user has not created any posts.','ultimatemember'); ?></span></div>
	
<?php } wp_reset_postdata(); ?>