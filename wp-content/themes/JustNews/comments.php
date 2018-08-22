<?php
/**
 * The template for displaying comments
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="entry-comments">
    <?php
    if (function_exists('um_get_core_page')) {
        $login_url = um_get_core_page('login');
    } else {
        $login_url = wp_login_url();
    }
    $fields =  array(
        'author' => '<div class="comment-form-author"><label for="author">'.( $req ? '<span class="required">*</span>' : '' ).__('Name: ', 'wpcom').'</label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"'.( $req ? ' class="required"' : '' ).'></div>',
        'email'  => '<div class="comment-form-email"><label for="email">'.( $req ? '<span class="required">*</span>' : '' ).__('Email: ', 'wpcom').'</label><input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"'.( $req ? ' class="required"' : '' ).'></div>'
    );
    $formsubmittext = '';
    if(is_user_logged_in()) {
        $user = wp_get_current_user();
        if (function_exists('um_get_core_page')) {
            um_fetch_user( $user->ID );
            $user_identity = um_user('display_name');
        } else {
            $user_identity = $user->exists() ? $user->display_name : '';
        }
        $formsubmittext = '<div class="pull-left form-submit-text">'.get_avatar( $user->ID, 60 ).'<span>'.$user_identity.'</span></div>';
    }
    comment_form( array(
        'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
        'title_reply_after'  => '</h3>',
        'fields' => apply_filters( 'comment_form_default_fields', $fields ),
        'comment_field' =>  '<div class="comment-form-comment"><textarea id="comment" name="comment" class="required" rows="4"></textarea></div>',
        'must_log_in' => '<div class="comment-form"><div class="comment-must-login">请登录后评论...</div><div class="form-submit"><div class="form-submit-text pull-left"><a href="'.$login_url.'">登录</a>后才能评论</div> <input name="submit" type="submit" id="must-submit" class="submit" value="发表"></div></div>',
        'logged_in_as' => '',
        'submit_field' => '<div class="form-submit">'.$formsubmittext.'%1$s %2$s</div>',
        'label_submit' => '提交',
        'format' => 'html5'
    ) );
    ?>
	<?php if ( have_comments() ) : ?>
		<h3 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			printf(__('Comments(%s)', 'wpcom'), number_format_i18n( $comments_number ));
			?>
		</h3>

		<ul class="comments-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ul',
				'short_ping'  => true,
				'type'        => 'all',
				'avatar_size' => '60',
				'callback'    => 'wpcom_comment'
			) );
			?>
		</ul><!-- .comment-list -->
        <div class="pagination clearfix">
            <?php paginate_comments_links(array('prev_text'=>__("&laquo; Previous", "wpcom"),'next_text'=>__("Next &raquo;", "wpcom")));?>
        </div>
	<?php endif; // Check for have_comments(). ?>
</div><!-- .comments-area -->