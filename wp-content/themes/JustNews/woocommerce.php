<?php
/**
 * Created by PhpStorm.
 * User: Lomu
 * Date: 17/6/15
 * Time: 下午2:56
 */
global $options;
if(is_singular( 'product' ) && isset($options['related_col']) && $options['related_col']==3) {
    add_filter('body_class', 'wpcom_woo_body_class');
}else if(!is_singular( 'product' ) && isset($options['shop_list_col']) && $options['shop_list_col']==3){
    add_filter('body_class', 'wpcom_woo_body_class');
}

get_header();
if(!is_singular( 'product' )){
    $is_sidebar = isset($options['shop_list_sidebar']) ? $options['shop_list_sidebar'] : 0;
    if(is_tax()){
        $term = get_queried_object();
        $cat_options = get_option('_product_cat_'.$term->term_id);
        $banner = isset($cat_options['banner']) ? $cat_options['banner'] : '';
        $banner = $banner=='' && isset($options['shop_banner']) ? $options['shop_banner'] : $banner;
    }else{
        $banner = isset($options['shop_banner']) ? $options['shop_banner'] : '';
    }
if($banner){
    $bHeight = intval(isset($cat_options['banner_height']) && $cat_options['banner_height'] ? $cat_options['banner_height'] : ($options['shop_banner_height']?$options['shop_banner_height']:300));
    $bColor = (isset($cat_options['text_color']) ? $cat_options['text_color'] : ($options['shop_banner_color']?$options['shop_banner_color']:0)) ? ' banner-white' : '';
    ?>
    <div class="banner<?php echo $bColor;?>" style="height:<?php echo $bHeight;?>px;background-image: url(<?php echo $banner ?>)">
        <div class="banner-inner">
            <h1><?php woocommerce_page_title(); ?></h1>
            <?php do_action( 'woocommerce_archive_description' ); ?>
        </div>
    </div>
    <?php } else { ?>
    <div class="special-head">
        <h1 class="special-title"><?php woocommerce_page_title(); ?></h1>
        <?php do_action( 'woocommerce_archive_description' ); ?>
    </div>
    <?php }
} else {
    $is_sidebar = isset($options['shop_single_sidebar']) ? $options['shop_single_sidebar'] : 0;
} ?>
    <div class="main<?php echo $is_sidebar?'':'-full';?> container">

        <div class="content<?php echo $is_sidebar?'':'-full';?> content-woo">
            <?php
            if ( is_singular( 'product' ) ) {

                while ( have_posts() ) : the_post();

                    wc_get_template_part( 'content', 'single-product' );

                endwhile;

            } else { ?>

                <?php if ( have_posts() ) : ?>

                    <?php do_action( 'woocommerce_before_shop_loop' ); ?>

                    <?php woocommerce_product_loop_start(); ?>

                    <?php woocommerce_product_subcategories(); ?>

                    <?php while ( have_posts() ) : the_post(); ?>

                        <?php wc_get_template_part( 'content', 'product' ); ?>

                    <?php endwhile; // end of the loop. ?>

                    <?php woocommerce_product_loop_end(); ?>

                    <?php do_action( 'woocommerce_after_shop_loop' ); ?>

                <?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

                    <?php do_action( 'woocommerce_no_products_found' ); ?>

                <?php endif;

            }
            ?>
        </div>
        <?php if($is_sidebar){ ?>
            <aside class="sidebar">
                <?php get_sidebar();?>
            </aside>
        <?php } ?>
    </div>
<?php get_footer();?>