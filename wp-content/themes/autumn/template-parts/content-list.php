<?php $list_region = wpjam_get_setting('wpjam_theme', 'list_region');?>
<article class="col-md-6 col-lg-4 <?php if( $list_region == 'col_3' ) { ?>col-xl-4<?php }elseif( $list_region == 'col_4' ){?>col-xl-3<?php }else{ ?>col-xl-3<?php }?> grid-item">
	<div class="post <?php if( get_post_meta($post->ID, 'feature_list', true) ) {?>cover lazyloaded<?php } ?>" <?php if( get_post_meta($post->ID, 'feature_list', true) ) {?>style='background-image: url("<?php echo wpjam_get_post_thumbnail_src($post,array(840,1120), $crop=1);?>");'<?php } ?>>
	<div class="entry-media with-placeholder" style="padding-bottom: 61.904761904762%;">
		<?php if( !get_post_meta($post->ID, 'feature_list', true) ) {?>
		<a href="<?php the_permalink(); ?>">
			<img class="lazyloaded" data-srcset="<?php echo wpjam_get_post_thumbnail_src($post,array(420,260), $crop=1);?>" srcset="<?php echo wpjam_get_post_thumbnail_src($post,array(420,260), $crop=1);?>">
		</a>
		<?php } ?>
		<?php if( has_post_format( 'gallery' )) { //相册 ?>
		<div class="entry-format">
			<i class="iconfont icon-xiangce"></i>
		</div>
		<?php } else if ( has_post_format( 'video' )) { //视频 ?>
		<div class="entry-format">
			<i class="iconfont icon-shipin"></i>
		</div>
		<?php } else if ( has_post_format( 'audio' )) { //音频 ?>
		<div class="entry-format">
			<i class="iconfont icon-yinpin"></i>
		</div>
		<?php } ?>
	</div>
	<div class="entry-wrapper">
		<header class="entry-header">
		<div class="entry-category">
			<?php  
				$category = get_the_category();
				if($category[0]){
				echo '<a href="'.get_category_link($category[0]->term_id ).'" rel="category">'.$category[0]->cat_name.'</a>';
				};
			?>
		</div>
		<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
		</header>
		<div class="entry-excerpt">
			<p>
				<?php
					$meta_data = get_post_meta($post->ID, 'post_abstract', true);
					$post_abstract = isset($meta_data) ?$meta_data : '';
					if(!empty($post_abstract)){
						echo $post_abstract;
					}else{
						echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 85,"……");
					}
				?>
			</p>
		</div>
		<div class="entry-author">
			<?php echo get_avatar( get_the_author_meta('email'), '200' );?>
			<div class="author-info">
				<a class="author-name" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ) ?>"><?php echo get_the_author() ?></a>
				<span class="entry-date"><time datetime="<?php the_time('Y-m-d h:m:s') ?>"><?php the_time('Y-m-d') ?></time></span>
			</div>
		</div>
	</div>
	<div class="entry-action">
		<div>
			<?php if( wpjam_get_setting('wpjam_theme', 'list_like') ) : ?><?php wpjam_theme_postlike2();?><?php endif; ?>
			<?php if( wpjam_get_setting('wpjam_theme', 'list_read') ) : ?><a class="view" href="<?php the_permalink(); ?>"><i class="iconfont icon-liulan"></i><span class="count"><?php wpjam_theme_post_views('',''); ?></span></a><?php endif; ?>
			<?php if( wpjam_get_setting('wpjam_theme', 'list_comment') ) : ?><a class="comment" href="<?php the_permalink(); ?>#comments"><i class="iconfont icon-pinglun"></i><span class="count"><?php echo get_post($post->ID)->comment_count; ?></span></a><?php endif; ?>
		</div>
		<div>
			<a class="share" href="<?php the_permalink(); ?>" data-url="<?php the_permalink(); ?>" data-title="<?php the_title(); ?>" data-thumbnail="<?php echo wpjam_get_post_thumbnail_src($post,array(150,150), $crop=1);?>" data-image="<?php echo wpjam_get_post_thumbnail_src($post,array(1130,848), $crop=1);?>">
			<i class="iconfont icon-icon_share_normal"></i>
			<span>Share</span>
			</a>
		</div>
	</div>
	</div>
</article>