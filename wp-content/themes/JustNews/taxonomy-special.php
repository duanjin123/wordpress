<?php
get_header();
$term = get_queried_object();
$spe_options = get_option('_special_'.$term->term_id);
$banner = isset($spe_options['banner']) ? $spe_options['banner'] : '';
if($banner){
    $bHeight = intval(isset($spe_options['banner_height']) && $spe_options['banner_height'] ? $spe_options['banner_height'] : 300);
    $bColor = (isset($spe_options['text_color']) ? $spe_options['text_color'] : 0) ? ' banner-white' : '';
    ?>
    <div class="banner<?php echo $bColor;?>" style="height:<?php echo $bHeight;?>px;background-image: url(<?php echo $banner ?>)">
        <div class="banner-inner">
            <h1><?php single_cat_title(); ?></h1>
            <div class="page-description"><?php echo term_description();?></div>
        </div>
    </div>
<?php } ?>
    <div class="main container">
        <?php if($banner==''){ ?>
            <div class="special-head">
                <h1 class="special-title"><?php single_cat_title(); ?></h1>
                <div class="page-description"><?php echo term_description();?></div>
            </div>
        <?php } ?>
        <div class="content">
            <div class="sec-panel archive-list">
                <ul class="article-list">
                    <?php if(have_posts()) : while( have_posts() ) : the_post();?>
                        <?php get_template_part( 'templates/list' , 'default' ); ?>
                    <?php endwhile; else : ?>
                        <li class="item" style="border: 0;">
                            <p class="text-center" style="padding: 15px 0;margin: 0;color: #999;"><?php _e('No posts.', 'wpcom');?></p>
                        </li>
                    <?php endif;?>
                </ul>
                <?php wpcom_pagination(5);?>
            </div>
        </div>
        <aside class="sidebar">
            <?php get_sidebar();?>
        </aside>
    </div>
<?php get_footer();?>