<?php get_header();?>
<div class="site-content container">
	<div class="row">
		<div class="col-lg-12">
			<div class="term-bar">
				<div class="term-info">
					<span>搜索结果</span>
					<h1 class="term-title">
					“<?php echo $s; ?>” <?php global $wp_query; echo '搜到 ' . $wp_query->found_posts . ' 篇文章';?>
					</h1>
				</div>
			</div>
			<div class="content-area">
				<main class="site-main">
				<?php if ( !have_posts() ) : ?>
					<div class="_404">
						<h2 class="entry-title">姿势不对？换个词搜一下~</h2>
						<div class="entry-content">
							抱歉，没有找到“<?php echo $s; ?>”的相关内容
						</div>
						<form method="get" class="search-form inline" action="<?php bloginfo('url'); ?>">
							<input class="search-field inline-field" placeholder="输入关键词进行搜索…" autocomplete="off" value="" name="s" required="true" type="search">
							<button type="submit" class="search-submit"><i class="iconfont icon-sousuo"></i></button>
						</form>
					</div>
				<?php else: ?>
				<div class="row posts-wrapper">				
					<?php while(have_posts()) : the_post();
							get_template_part( 'template-parts/content-list' );
						endwhile;?>
					<?php get_template_part( 'template-parts/paging' );?>
				</div>
				<?php endif; ?>
				</main>
			</div>
		</div>
	</div>
</div>
<?php get_footer();?>