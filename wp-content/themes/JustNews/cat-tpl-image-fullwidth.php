<?php
get_header();
$cat_options = get_option('_category_'.$cat);
$banner = isset($cat_options['banner']) ? $cat_options['banner'] : get_option('cat_banner_'.$cat);
if($banner){
    $bHeight = intval(isset($cat_options['banner_height']) && $cat_options['banner_height'] ? $cat_options['banner_height'] : 300);
    $bColor = (isset($cat_options['text_color']) ? $cat_options['text_color'] : 0) ? ' banner-white' : '';
    ?>
    <div class="banner<?php echo $bColor;?>" style="height:<?php echo $bHeight;?>px;background-image: url(<?php echo $banner ?>)">
        <div class="banner-inner">
            <h1><?php single_cat_title(); ?></h1>
            <div class="page-description"><?php echo category_description();?></div>
        </div>
    </div>
<?php } ?>
    <div class="main container">
            <div class="sec-panel archive-list">
                <?php if($banner==''){ ?>
                    <div class="sec-panel-head">
                        <h1><?php single_cat_title(); ?></h1>
                    </div>
                <?php } ?>
                <div class="sec-panel-body">
                    <ul class="image-list clearfix">
                        <?php while( have_posts() ) : the_post();?>
                            <?php get_template_part( 'templates/list' , 'image-fullwidth' ); ?>
                        <?php endwhile; ?>
                    </ul>
                    <?php wpcom_pagination(5);?>
                </div>
            </div>
    </div>
<?php get_footer();?>