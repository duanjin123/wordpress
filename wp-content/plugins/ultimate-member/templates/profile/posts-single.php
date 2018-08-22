<?php
global $is_author, $options;
$show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
?>
	<ul class="article-list">
	<?php while ($ultimatemember->shortcodes->loop->have_posts()) { global $post; $ultimatemember->shortcodes->loop->the_post(); $post_id = get_the_ID(); ?>
		<li class="item">
		    <?php $has_thumb = get_the_post_thumbnail(); if($has_thumb){ ?>
		    <div class="item-img">
		        <a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>" target="_blank">
		            <?php the_post_thumbnail(); ?>
		        </a>
		        <?php
		        $category = get_the_category();
		        $cat = $category?$category[0]:'';
		        if($cat){
		        ?>
		        <a class="item-category" href="<?php echo get_category_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a>
		        <?php } ?>
		    </div>
		    <?php } ?>
		    <div class="item-content<?php echo $is_author ? ' item-edit' : '';?>"<?php echo ($has_thumb?'':' style="margin-left: 0;"');?>>
		    	<?php if($is_author){?>
		    	<a class="edit-link" href="<?php echo get_edit_link($post->ID);?>" target="_blank">编辑</a>
		    	<?php } ?>
		        <h2 class="item-title">
		        	<a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>" target="_blank">
		        	<?php if($is_author && $post->post_status=='draft'){ echo '<span>【草稿】</span>'; }else if($is_author && $post->post_status=='pending'){ echo '<span>【待审核】</span>'; }?><?php the_title();?>
		        	</a>
		        </h2>
		        <div class="item-excerpt">
		            <?php the_excerpt(); ?>
		        </div>
		        <div class="item-meta">
		        	<?php if($show_author) { ?>
		            <div class="item-meta-li author">
		                <?php
		                $author = get_the_author_meta( 'ID' );
		                $author_url = um_user_profile_url();
		                ?>
		                <a data-user="<?php echo $author;?>" target="_blank" href="<?php echo $author_url; ?>" class="avatar">
		                    <?php echo get_avatar( $author, 60 );?>
		                </a>
		                <a class="nickname" href="<?php echo $author_url; ?>" target="_blank"><?php echo um_user('display_name'); ?></a>
		            </div>
		            <?php } ?>
		            <?php
		            $time = get_post_time( 'U', true, $post );
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
				    }else if( $t >= 604800 || $t < 0){
				        $human_time = date(get_option('date_format'), $time);
				    }else{
				        foreach ($f as $k=>$v)    {
				            if (0 !=$c=floor($t/(int)$k)) {
				                $human_time = $c.$v.'前';
				                break;
				            }
				        }
				    }
		            ?>
		            <span class="item-meta-li date"><?php echo $human_time;?></span>
		            <?php
		            if(function_exists('the_views')) {
		                $views = intval(get_post_meta($post->ID, 'views', true));
		            ?>
		            <span class="item-meta-li views" title="阅读数"><i class="fa fa-eye"></i> <span class="data"><?php echo $views; ?></span></span>
		            <?php } ?>
		            <span class="item-meta-li likes" title="点赞数"><i class="fa fa-thumbs-up"></i> <span class="data"><?php $likes = get_post_meta($post->ID, 'wpcom_likes', true); echo $likes?$likes:0;?></span></span>
		            <a class="item-meta-li comments" href="<?php the_permalink();?>#comments" target="_blank" title="评论数"><i class="fa fa-comment"></i> <span class="data"><?php echo get_comments_number();?></span></a>
		            <span class="item-meta-li hearts" title="喜欢数"><i class="fa fa-heart"></i> <span class="data"><?php $favorites = get_post_meta($post->ID, 'wpcom_favorites', true); echo $favorites?$favorites:0;?></span></span>
		        </div>
		    </div>
		</li>
	<?php } ?>
	</ul>
	
	<?php if ( isset($ultimatemember->shortcodes->modified_args) && $ultimatemember->shortcodes->loop->have_posts() && $ultimatemember->shortcodes->loop->found_posts >= 10 ) { ?>
	
		<div class="um-load-items load-more-wrap">
			<a href="#" class="um-ajax-paginate load-more" data-hook="um_load_posts" data-args="<?php echo $ultimatemember->shortcodes->modified_args; ?>"><?php _e('load more posts','ultimatemember'); ?></a>
		</div>
		
	<?php } ?>