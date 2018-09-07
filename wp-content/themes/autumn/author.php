<?php get_header();?>
<div class="site-content container">
	<div class="row">
		<div class="col-lg-12">
			<div class="term-bar">
				<div class="author-image">
					<?php echo get_avatar( get_the_author_meta('email'), '200' );?>
				</div>
				<div class="term-info">
					<h1 class="term-title" style="margin: 0 0 8px;"><?php echo get_the_author() ?></h1>
					<span><?php if(get_the_author_meta('description')){ echo the_author_meta( 'description' );}else{echo'我还没有学会写个人说明！'; }?></span>
				</div>
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