	<?php foreach( $ultimatemember->shortcodes->loop as $comment ) { ?>

		<div class="um-item">
			<div class="um-item-link"><a target="_blank" href="<?php echo get_comment_link( $comment->comment_ID ); ?>"><i class="um-icon-chatboxes"></i> <?php echo get_comment_excerpt( $comment->comment_ID ); ?></a></div>
			<div class="um-item-meta">
			<?php
		            $time = strtotime($comment->comment_date_gmt);
		            $t = time() - $time;
				    $f = array(
				        '86400'=>'天',
				        '3600'=>'小时',
				        '60'=>'分钟',
				        '1'=>'秒'
				    );
				    $human_time = '';
				    if($t==0){
				        $human_time = '1秒前';
				    }else if( $t >= 86400 || $t < 0){
				        $human_time = date(get_option('date_format'), strtotime($comment->comment_date));
				    }else{
				        foreach ($f as $k=>$v)    {
				            if (0 !=$c=floor($t/(int)$k)) {
				                $human_time = $c.$v.'前';
				                break;
				            }
				        }
				    }
		            ?>
				<span><?php echo $human_time; printf(__('On <a target="_blank" href="%1$s">%2$s</a>','ultimatemember'), get_permalink($comment->comment_post_ID), get_the_title($comment->comment_post_ID) ); ?></span>
			</div>
		</div>
		
	<?php } ?>
	
	<?php if ( isset($ultimatemember->shortcodes->modified_args) && count($ultimatemember->shortcodes->loop) >= 10 ) { ?>
	
		<div class="um-load-items">
			<a href="#" class="um-ajax-paginate um-button" data-hook="um_load_comments" data-args="<?php echo $ultimatemember->shortcodes->modified_args; ?>"><?php _e('load more comments','ultimatemember'); ?></a>
		</div>
		
	<?php } ?>