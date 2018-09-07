<?php get_header();
get_template_part( 'template-parts/banner' );
?>
<div class="site-content container">
	<div class="row">
		<div class="col-lg-12">
			<div class="content-area">
				<main class="site-main">
				<div class="row posts-wrapper">
					<?php
						$args = array(
						'ignore_sticky_posts' => 1,
						'paged' => $paged,
						//'post__not_in' => get_option( 'sticky_posts' ) //不输出置顶文章
						);	
						query_posts($args);
						if ( have_posts() ) : ?>
					<?php 
						while ( have_posts() ) : the_post();
							get_template_part( 'template-parts/content-list' );
						endwhile; endif;?>
					<?php get_template_part( 'template-parts/paging' );?>
					</div>
				</main>
			</div>
		</div>
	</div>
</div>
<?php get_footer();?>