<?php
global $options;
$show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
?>
    <li class="item item3">
        <div class="item-content">
            <h2 class="item-title">
                <a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>" target="_blank">
                    <?php if(is_sticky()){ ?><span class="sticky-post">置顶</span><?php } ?> <?php the_title();?>
                </a>
            </h2>
            <a class="item-images" href="<?php the_permalink();?>" target="_blank">
                <?php
                preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', get_the_content(), $matches);
                $imgs = array_slice($matches[1], 0, 4);
                $lazy_img = isset($options['lazyload_img']) && $options['lazyload_img'] ? $options['lazyload_img'] : FRAMEWORK_URI.'/assets/images/lazy.png';
                foreach($imgs as $img){
                    echo '<span><img class="j-lazy" src="'.$lazy_img.'" data-original="'.$img.'" alt="'.esc_attr(get_the_title()).'"></span>';
                }?>
            </a>
            <div class="item-excerpt">
                <?php the_excerpt(); ?>
            </div>
            <div class="item-meta">
                <?php if($show_author && function_exists('um_get_core_page')){ ?>
                    <div class="item-meta-li author">
                        <?php
                        $author = get_the_author_meta( 'ID' );
                        um_fetch_user( $author );
                        $author_url = um_user_profile_url();
                        ?>
                        <a data-user="<?php echo $author;?>" target="_blank" href="<?php echo $author_url; ?>" class="avatar">
                            <?php echo get_avatar( $author, 60 );?>
                        </a>
                        <a class="nickname" href="<?php echo $author_url; ?>" target="_blank"><?php echo um_user('display_name'); ?></a>
                    </div>
                <?php } ?>
                <?php
                $category = get_the_category();
                $cat = $category?$category[0]:'';
                if($cat){
                    ?>
                <a class="item-meta-li" href="<?php echo get_category_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a>
                <?php } ?>
                <span class="item-meta-li date"><?php echo format_date(get_post_time( 'U', false, $post ));?></span>
                <?php
                if(function_exists('the_views')) {
                    $views = intval(get_post_meta($post->ID, 'views', true));
                    ?>
                    <span class="item-meta-li views" title="阅读数"><i class="fa fa-eye"></i> <span class="data"><?php echo $views; ?></span></span>
                <?php } ?>
                <span class="item-meta-li likes" title="点赞数"><i class="fa fa-thumbs-up"></i> <span class="data"><?php $likes = get_post_meta($post->ID, 'wpcom_likes', true); echo $likes?$likes:0;?></span></span>
                <?php if ( isset($options['comments_open']) && $options['comments_open']=='1' ) { ?><a class="item-meta-li comments" href="<?php the_permalink();?>#comments" target="_blank" title="评论数"><i class="fa fa-comment"></i> <span class="data"><?php echo get_comments_number();?></span></a><?php } ?>
                <span class="item-meta-li hearts" title="喜欢数"><i class="fa fa-heart"></i> <span class="data"><?php $favorites = get_post_meta($post->ID, 'wpcom_favorites', true); echo $favorites?$favorites:0;?></span></span>
            </div>
        </div>
    </li>
<?php do_action('wpcom_echo_ad', 'ad_flow');?>