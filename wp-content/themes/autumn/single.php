<?php get_header();?>
<?php get_template_part( 'template-parts/single-top' );?>
<div class="site-content container">
	<div class="row">
		<div class="col-lg-9">
			<div class="content-area">
				<main class="site-main">
				<article class="type-post post">
				<header class="entry-header">
				<div class="entry-category">
				<?php  
					$category = get_the_category();
					if($category[0]){
					echo '<a href="'.get_category_link($category[0]->term_id ).'" rel="category">'.$category[0]->cat_name.'</a>';
					};
				?>
				</div>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				</header>
				<div class="entry-action">
					<div>
						<?php wpjam_theme_postlike2();?>
						<a class="view" href="<?php the_permalink(); ?>"><i class="iconfont icon-liulan"></i><span class="count"><?php wpjam_theme_post_views('',''); ?></span></a>
						<a class="comment" href="<?php the_permalink(); ?>#comments"><i class="iconfont icon-pinglun"></i><span class="count"><?php echo get_post($post->ID)->comment_count; ?></span></a>
						<?php edit_post_link('[编辑文章]'); ?>
					</div>
					<div>
						<a class="share" href="<?php the_permalink(); ?>" data-url="<?php the_permalink(); ?>" data-title="<?php the_title(); ?>" data-thumbnail="<?php echo wpjam_get_post_thumbnail_src($post,array(150,150), $crop=1);?>" data-image="<?php echo wpjam_get_post_thumbnail_src($post,array(1130,848), $crop=1);?>">
						<i class="iconfont icon-icon_share_normal"></i>
						<span>Share</span>
						</a>
					</div>
				</div>
				<div class="entry-wrapper">
					<div class="entry-content u-clearfix">
					<?php while( have_posts() ): the_post(); $p_id = get_the_ID(); ?>
						<?php the_content();?>
					<?php endwhile; ?>
					</div>
					<?php if( wpjam_get_setting('wpjam_theme', 'single_tag') ) : ?>
					<div class="entry-tags">
						<?php the_tags('标签：', ' · ', ''); ?>
					</div>
					<?php endif; ?>
					<div class="entry-share">
						<a rel="nofollow" href="https://service.weibo.com/share/share.php?url=<?php the_permalink(); ?>&amp;type=button&amp;language=zh_cn&amp;title=<?php the_title_attribute(); ?>&amp;pic=<?php echo wpjam_get_post_thumbnail_src($post,array(840,520), $crop=1);?>&amp;searchPic=true" target="_blank"><i class="iconfont icon-weibo"></i></a>
						<a rel="nofollow" href="https://connect.qq.com/widget/shareqq/index.html?url=<?php the_permalink(); ?>&amp;title=<?php the_title_attribute(); ?>&amp;pics=<?php echo wpjam_get_post_thumbnail_src($post,array(840,520), $crop=1);?>&amp;summary=<?php echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 200,"……"); ?>" target="_blank"><i class="iconfont icon-QQ"></i></a>
						<a href="javascript:;" data-module="miPopup" data-selector="#post_qrcode" class="weixin"><i class="iconfont icon-weixin"></i></a>
						<!--a href="#" target="_blank"> <i class="iconfont icon-dashang1"></i></a-->
						<?php wpjam_theme_postlike();?>
					</div>
				</div>
				</article>
				<?php if( wpjam_get_setting('wpjam_theme', 'xintheme_author') ) : ?>
				<div class="about-author">
					<div class="author-image">
						<?php echo get_avatar( get_the_author_meta('email'), '200' );?>
					</div>
					<div class="author-info">
						<h4 class="author-name">
						<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ) ?>"><?php echo get_the_author() ?></a>
						</h4>
						<div class="author-bio">
							<?php if(get_the_author_meta('description')){ echo the_author_meta( 'description' );}else{echo'我还没有学会写个人说明！'; }?>
						</div>
						<div class="author-meta">
							<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=<?php the_author_meta('email') ?>" target="_blank"><i class="iconfont icon-youxiang" style="font-size: 14px;"></i></a>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<div class="entry-navigation">
				<?php
					$prev_post = get_previous_post();
					if(!empty($prev_post)):?>					
					<div class="nav previous">
						<img class="lazyload" data-srcset="<?php echo wpjam_get_post_thumbnail_src($prev_post, '690x400'); ?>">
						<span>上一篇</span>
						<h4 class="entry-title"><?php echo $prev_post->post_title;?></h4>
						<a class="u-permalink" href="<?php echo get_permalink($prev_post->ID);?>"></a>
					</div>
					<?php else: ?>
					<div class="nav none">
						<span>没有了，已经是最后一篇了</span>
					</div>
				<?php endif;?>
				<?php
					$next_post = get_next_post();
					if(!empty($next_post)):?>
					<div class="nav next">
						<img class="lazyload" data-srcset="<?php echo wpjam_get_post_thumbnail_src($next_post, '690x400'); ?>">
						<span>下一篇</span>
						<h4 class="entry-title"><?php echo $next_post->post_title;?></h4>
						<a class="u-permalink" href="<?php echo get_permalink($next_post->ID);?>"></a>
					</div>
					<?php else: ?>
					<div class="nav none">
						<span>没有了，已经是最新一篇了</span>
					</div>
				<?php endif;?>
				</div>
				<?php comments_template( '', true ); ?>
				</main>
			</div>
		</div>
		<?php get_sidebar();?>
	</div>
</div>
<?php get_footer();?>