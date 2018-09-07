<?php
if ( post_password_required() ) {
	return;
}
?>
<script type="text/javascript" src='<?php bloginfo('url'); ?>/wp-includes/js/comment-reply.min.js'></script>
<div id="comments" class="comments-area">
<h3 class="comments-title"><?php comments_number('', '1 条评论', '% 条评论' );?></h3>
	<?php
	// You can start editing here -- including this comment!
	if ( have_comments() ) :
	?>
		<ol class="comment-list">
			<?php wp_list_comments('type=comment&callback=wpjam_theme_list_comments'); ?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => '上一页',
				'next_text' => '下一页' ,
			)
		);

	endif; // Check for have_comments().

	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php _e( 'Comments are closed.' ); ?></p>
	<?php
	endif;
	comment_form();
	?>
</div><!-- #comments -->
<style>
.required {color: var(--accent-color);padding: 0 5px 0;}
.comments-area #reply-title.comment-reply-title {font-size: 22px;}
#commentform .comment-form-comment label{display: none;}
.comment-form-author,.comment-form-email,.comment-form-url{flex: 0 0 33.333333%;max-width: 33.333333%;float: left;padding-left: 15px;padding-right: 15px;}
.form-submit{flex: 0 0 33.333333%;max-width: 33.333333%;}
@media (max-width:767px){
	.comment-form-author,.comment-form-email,.comment-form-url{max-width: 100%;padding-left: 0;padding-right: 0;}
}
<?php if( wpjam_get_setting('wpjam_theme', 'comment-form-url') ) : ?>
.comment-form-url{display: none;}
<?php endif; ?>
</style>