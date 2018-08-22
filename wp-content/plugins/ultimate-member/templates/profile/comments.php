<?php $ultimatemember->shortcodes->loop = $ultimatemember->query->make('post_type=comment&number=10&offset=0&user_id=' . um_user('ID') ); ?>

<?php if ( $ultimatemember->shortcodes->loop ) { ?>
			
	<?php $ultimatemember->shortcodes->load_template('profile/comments-single'); ?>
	
	<div class="um-ajax-items">
	
		<!--Ajax output-->
		
		<?php if ( count($ultimatemember->shortcodes->loop) >= 10 ) { ?>
		
		<div class="um-load-items load-more-wrap">
			<a href="#" class="um-ajax-paginate load-more" data-hook="um_load_comments" data-args="comment,10,10,<?php echo um_user('ID'); ?>"><?php _e('load more comments','ultimatemember'); ?></a>
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
	<div class="um-profile-note"><?php echo $emo;?><span><?php echo ( um_profile_id() == get_current_user_id() ) ? __('You have not made any comments.','ultimatemember') : __('This user has not made any comments.','ultimatemember'); ?></span></div>
	
<?php } ?>