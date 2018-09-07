<?php 
$slide_region = wpjam_get_setting('wpjam_theme', 'slide_region');
if ( $slide_region == '4' ) { ?>
<div class="featured-wrapper lazyload" data-bg="<?php echo wpjam_get_setting('wpjam_theme', 'slide_bg_img');?>">
<?php } ?>
<div class="container">
	<?php
		$number = wpjam_get_setting('wpjam_theme', 'slide_number');
		$sticky = get_option( 'sticky_posts' );
		$args = array(
			'posts_per_page' => $number,
			'post__in'  => $sticky,
			'ignore_sticky_posts' => 1
		);
		$the_query = new WP_Query( $args );
		if ( ( isset($sticky[0]) ) && (!get_query_var('paged')) ) {
	?>
	<?php 
	$slide_region = wpjam_get_setting('wpjam_theme', 'slide_region');
	if( $slide_region == '1' ){?>
	<div class="featured-posts v1 owl-carousel with-padding">
		<?php while ( $the_query->have_posts() ) : $the_query->the_post();?>
		<article class="featured-post lazyload visible" data-bg="<?php echo wpjam_get_post_thumbnail_src($post,array(1130,400), $crop=1);?>">
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
			<h2 class="entry-title"><?php the_title(); ?></h2>
			</header>
			<div class="entry-excerpt">
				<p>
					<?php
						$meta_data = get_post_meta($post->ID, 'post_abstract', true);
						$post_abstract = isset($meta_data) ?$meta_data : '';
						if(!empty($post_abstract)){
							echo $post_abstract;
						}else{
							echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 80,"……");
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
		<a class="u-permalink" href="<?php the_permalink(); ?>"></a>
		</article>
		<?php endwhile;wp_reset_query();?>
	</div>
	<?php }elseif( $slide_region == '2' ){?>
	<div class="featured-posts v2 owl-carousel with-padding">
		<?php while ( $the_query->have_posts() ) : $the_query->the_post();?>
		<article class="featured-post lazyload visible">
		<div class="entry-wrapper">
			<div class="entry-thumbnail">
				<img class="lazyload" data-src="<?php echo wpjam_get_post_thumbnail_src($post,array(150,150), $crop=1);?>">
			</div>
			<header class="entry-header">
			<div class="entry-category">
				<?php  
					$category = get_the_category();
					if($category[0]){
					echo '<a href="'.get_category_link($category[0]->term_id ).'" rel="category">'.$category[0]->cat_name.'</a>';
					};
				?>
			</div>
			<h2 class="entry-title"><?php the_title(); ?></h2>
			</header>
			<div class="entry-excerpt">
				<p>
					<?php
						$meta_data = get_post_meta($post->ID, 'post_abstract', true);
						$post_abstract = isset($meta_data) ?$meta_data : '';
						if(!empty($post_abstract)){
							echo $post_abstract;
						}else{
							echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 80,"……");
						}
					?>
				</p>
			</div>
		</div>
		<a class="u-permalink" href="<?php the_permalink(); ?>"></a>
		</article>
		<?php endwhile;wp_reset_query();?>
	</div>
	<?php }elseif( $slide_region == '3' ){?>
	<div class="featured-posts v3 owl-carousel with-padding">
		<?php while ( $the_query->have_posts() ) : $the_query->the_post();?>
		<article class="featured-post lazyload visible" data-bg="<?php echo wpjam_get_post_thumbnail_src($post,array(840,560), $crop=1);?>">
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
			<h2 class="entry-title"><?php the_title(); ?></h2>
			</header>
		</div>
		<a class="u-permalink" href="<?php the_permalink(); ?>"></a>
		</article>
		<?php endwhile;wp_reset_query();?>
	</div>
	<?php }elseif( $slide_region == '4' ){?>
	<div class="featured-posts v2 owl-carousel with-padding">
		<?php while ( $the_query->have_posts() ) : $the_query->the_post();?>
		<article class="featured-post lazyload visible">
		<div class="entry-wrapper">
			<div class="entry-thumbnail">
				<img class="lazyload" data-src="<?php echo wpjam_get_post_thumbnail_src($post,array(150,150), $crop=1);?>">
			</div>
			<header class="entry-header">
			<div class="entry-category">
				<?php  
					$category = get_the_category();
					if($category[0]){
					echo '<a href="'.get_category_link($category[0]->term_id ).'" rel="category">'.$category[0]->cat_name.'</a>';
					};
				?>
			</div>
			<h2 class="entry-title"><?php the_title(); ?></h2>
			</header>
			<div class="entry-excerpt">
				<p>
					<?php
						$meta_data = get_post_meta($post->ID, 'post_abstract', true);
						$post_abstract = isset($meta_data) ?$meta_data : '';
						if(!empty($post_abstract)){
							echo $post_abstract;
						}else{
							echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 80,"……");
						}
					?>
				</p>
			</div>
		</div>
		<a class="u-permalink" href="<?php the_permalink(); ?>"></a>
		</article>
		<?php endwhile;wp_reset_query();?>
	</div>
	<?php }else{?>
	<div class="featured-posts v1 owl-carousel with-padding">
		<?php while ( $the_query->have_posts() ) : $the_query->the_post();?>
		<article class="featured-post lazyload visible" data-bg="<?php echo wpjam_get_post_thumbnail_src($post, $size, $crop);?>">
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
			<h2 class="entry-title"><?php the_title(); ?></h2>
			<div class="entry-meta">
			</div>
			</header>
			<div class="entry-excerpt">
				<p>
					<?php
						$meta_data = get_post_meta($post->ID, 'post_abstract', true);
						$post_abstract = isset($meta_data) ?$meta_data : '';
						if(!empty($post_abstract)){
							echo $post_abstract;
						}else{
							echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 80,"……");
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
		<a class="u-permalink" href="<?php the_permalink(); ?>"></a>
		</article>
		<?php endwhile;?>
	</div>
	<?php }?>
	<?php } wp_reset_query(); wp_reset_postdata(); ?>
</div>
<?php if ( $slide_region == '4' ) { ?>
</div>
<?php } ?>
<?php if( ( wpjam_get_setting('wpjam_theme', 'index_cat')== '2' ) && (!get_query_var('paged')) ) : ?>
<div class="container">
<style>.category-boxes {margin-top: 0}</style>
	<div class="category-boxes owl-carousel with-padding">
		<?php 
		$categories= wpjam_get_setting('wpjam_theme', 'index_cat_id');
		foreach ($categories as $cat=>$catid ){
		?>
		<div class="category-box">
			<div class="entry-thumbnails">
				<?php 	query_posts( 'cat='.$catid.'&posts_per_page=1,&ignore_sticky_posts=1' );
				while( have_posts() ): the_post(); ?>
				<div class="big thumbnail">
					<img class="lazyload" data-src="<?php echo wpjam_get_post_thumbnail_src($post,array(420,280), $crop=1);?>">
				</div>
				<?php endwhile; wp_reset_query(); ?>
				<div class="small">
					<?php query_posts( 'cat='.$catid.'&posts_per_page=1&ignore_sticky_posts=1&offset=1' );
						while( have_posts() ): the_post(); 
					?>
					<div class="thumbnail">
						<img class="lazyload" data-src="<?php echo wpjam_get_post_thumbnail_src($post,array(150,150), $crop=1);?>">
					</div>
					<?php endwhile; wp_reset_query(); ?>
					<?php query_posts( 'cat='.$catid.'&posts_per_page=1&ignore_sticky_posts=1&offset=2' );
						while( have_posts() ): the_post(); 
					?>
					<div class="thumbnail">
						<img class="lazyload" data-src="<?php echo wpjam_get_post_thumbnail_src($post,array(150,150), $crop=1);?>">
						<span><?php echo wt_get_category_count($catid); ?> 篇文章</span>
					</div>
					<?php endwhile; wp_reset_query(); ?>
				</div>
			</div>
			<div class="entry-content">
				<div class="left">
					<h3 class="entry-title"><?php $cat = get_category($catid);echo $cat->name; ?></h3>
				</div>
				<div class="right">
					<a class="arrow" href="<?php echo get_category_link($catid);?>"><i class="iconfont icon-zuo"></i></a>
				</div>
			</div>
			<a class="u-permalink" href="<?php echo get_category_link($catid);?>"></a>
		</div>
		<?php } ?>
	</div>
</div>
<?php endif; ?>