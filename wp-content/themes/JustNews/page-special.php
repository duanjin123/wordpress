<?php
// TEMPLATE NAME: 专题列表
global $options;
$num = isset($options['special_per_page']) && $options['special_per_page'] ? $options['special_per_page'] : 10;
$special = get_special_list($num, 1);
get_header();?>
    <div class="main container">
        <?php while( have_posts() ) : the_post();?>
            <div class="special-head">
                <h1 class="special-title"><?php the_title();?></h1>
                <div class="page-description"><?php the_content();?></div>
            </div>
        <?php endwhile; ?>
        <div class="special-wrap">
            <div class="special-list clearfix">
                <?php foreach($special as $sp){
                    $special_options = get_option('_special_'.$sp->term_id);
                    $thumb = isset($special_options['thumb']) ? $special_options['thumb'] : get_option('special_thumb_'.$sp->term_id);
                    $link = get_term_link($sp->term_id);
                    ?>
                    <div class="col-md-6 col-xs-12 special-item-wrap">
                        <div class="special-item">
                            <div class="special-item-top">
                                <div class="special-item-thumb">
                                    <a href="<?php echo $link;?>" target="_blank"><img src="<?php echo esc_url($thumb);?>" alt="<?php echo esc_attr($sp->name);?>"></a>
                                </div>
                                <div class="special-item-title">
                                    <h2><a href="<?php echo $link;?>" target="_blank"><?php echo $sp->name;?></a></h2>
                                    <?php echo term_description($sp->term_id, 'special');?>
                                </div>
                                <a class="special-item-more" href="<?php echo $link;?>"><?php echo _x('Read More', 'topic', 'wpcom');?></a>
                            </div>
                            <ul class="special-item-bottom">
                                <?php
                                $args = array(
                                    'posts_per_page' => 3,
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => 'special',
                                            'field' => 'term_id',
                                            'terms' => $sp->term_id
                                        )
                                    )
                                );
                                $postslist = get_posts( $args );
                                foreach($postslist as $post){ setup_postdata($post);?>
                                    <li><a title="<?php echo esc_attr(get_the_title());?>" href="<?php the_permalink();?>" target="_blank"><?php the_title();?></a></li>
                                <?php } wp_reset_postdata(); ?>
                            </ul>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php if($num<count(get_terms(array('taxonomy' => 'special', 'hide_empty' => false)))){ ?>
            <div class="load-more-wrap">
                <a class="load-more" href="javascript:;"><?php _e('Load more topics', 'wpcom');?></a>
            </div>
            <?php } ?>
        </div>
    </div>
<?php get_footer();?>