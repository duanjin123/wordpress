<?php get_header();?>
<div class="site-content container">
	<div class="row">
		<div class="col-lg-12">
			<div class="term-bar">
				<div class="term-info">
				<span>浏览标签</span><h1 class="term-title"><?php single_cat_title(); ?></h1>  </div>
			</div>
			<div class="content-area">
				<main class="site-main">
				<div class="row posts-wrapper">
					<?php while(have_posts()) : the_post();
							get_template_part( 'template-parts/content-list' );
						endwhile;?>
					<?php get_template_part( 'template-parts/paging' );?>
					</div>
				</main>
			</div>
		</div>
	</div>
</div>
<?php get_footer();?>