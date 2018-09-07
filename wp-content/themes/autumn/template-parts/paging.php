<?php $paging = wpjam_get_setting('wpjam_theme', 'paging_xintheme');?>
<?php if( $paging == '1' ){?>
<div class="numeric-pagination">
	<ul class="page-numbers">
		<?php wpjam_theme_pagenavi();?>
	</ul>
</div>
<?php }elseif( $paging == '2' ){?>
<nav class="navigation posts-navigation">
	<div class="nav-links">
		<div class="nav-previous">
			<?php next_posts_link('下一页 »') ?>
		</div>
		<div class="nav-next">
			<?php previous_posts_link('« 上一页') ?>
			<?php if ( is_paged() ) { ?>
			<script type="text/javascript">
			document.getElementById("body").className="paged-previous paged-next"; 
			</script>
			<?php } ?>
		</div>
	</div>
</nav>
<?php }elseif( $paging == '3' ){?>
<div class="misha_loadmore infinite-scroll-action">
	<?php
	global $wp_query;
	// 如果没有更多文章 不显示按钮
	if (  $wp_query->max_num_pages > 1 )
		echo '<div class="infinite-scroll-button button">加载更多</div>';
	?>
</div>
<?php }elseif( $paging == '4' ){?>
<?php }else{?>
<div class="misha_loadmore infinite-scroll-action">
	<?php
	global $wp_query;
	// 如果没有更多文章 不显示按钮
	if (  $wp_query->max_num_pages > 1 )
		echo '<div class="infinite-scroll-button button">加载更多</div>';
	?>
</div>
<?php }?>