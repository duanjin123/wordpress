<?php
global $options,$current_user;
$dashang_display = isset($options['dashang_display']) ? $options['dashang_display'] : 0;
$show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
get_header();?>
    <div class="main container">
        <div class="content">
            <?php while( have_posts() ) : the_post();?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry">
                        <div class="entry-head">
                            <h1 class="entry-title"><?php the_title();?></h1>
                            <div class="entry-info">
                                <?php
                                $author = get_the_author_meta( 'ID' );
                                $author_url = get_author_posts_url( $author );
                                if(function_exists('um_edit_profile_url')){
                                    um_fetch_user($author);
                                    $author_name = um_user('display_name');
                                    $author_url = um_user_profile_url();
                                }else{
                                    $author_name = get_the_author();
                                }
                                ?>
                                <a class="nickname" href="<?php echo $author_url; ?>"><?php echo $author_name;?></a>
                                <span class="dot">•</span>
                                <span><?php echo format_date(get_post_time( 'U', false, $post ));?></span>
                                <span class="dot">•</span>
                                <?php the_category( ', ', '', false ); ?>
                                <?php if(function_exists('the_views')) {
                                    $views = intval(get_post_meta($post->ID, 'views', true));
                                    ?>
                                    <span class="dot">•</span>
                                    <span>阅读 <?php echo $views; ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        <?php do_action('wpcom_echo_ad', 'ad_single_1');?>
                        <?php if($post->post_excerpt){ ?>
                        <div class="entry-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        <?php } ?>
                        <div class="entry-content clearfix">
                            <?php the_content();?>
                            <?php wpcom_pagination();?>
                            <?php wpcom_post_copyright();?>
                        </div>
                        <div class="entry-footer">
                            <div class="entry-tag"><?php the_tags('', '');?></div>
                            <div class="entry-action">
                                <div class="btn-zan" data-id="<?php the_ID(); ?>"><i class="fa fa-thumbs-up"></i> <?php _e( 'Like', 'wpcom' );?> <span class="entry-action-num">(<?php $likes = get_post_meta($post->ID, 'wpcom_likes', true); echo $likes?$likes:0;?>)</span></div>

                                <?php if($dashang_display==1 && isset($options['dashang_1_img']) && ($options['dashang_1_img'] || $options['dashang_2_img'])){ ?>
                                    <div class="btn-dashang">
                                        <i class="fa fa-usd"></i> 打赏
                                                <span class="dashang-img<?php if($options['dashang_1_img']&&$options['dashang_2_img']){echo ' dashang-img2';}?>">
                                                    <?php if($options['dashang_1_img']){ ?>
                                                        <span>
                                                        <img src="<?php echo esc_url($options['dashang_1_img'])?>" alt="<?php echo esc_attr($options['dashang_1_title'])?>"/>
                                                            <?php echo $options['dashang_1_title'];?>
                                                    </span>
                                                    <?php } ?>
                                                    <?php if($options['dashang_2_img']){ ?>
                                                        <span>
                                                        <img src="<?php echo esc_url($options['dashang_2_img'])?>" alt="<?php echo esc_attr($options['dashang_2_title'])?>"/>
                                                            <?php echo $options['dashang_2_title'];?>
                                                    </span>
                                                    <?php } ?>
                                                </span>
                                    </div>
                                <?php } ?>

                            </div>
                            <div class="entry-bar">
                                <div class="entry-bar-inner clearfix">
                                    <?php if($show_author) { ?>
                                        <div class="author pull-left">
                                            <a data-user="<?php echo $author;?>" target="_blank" href="<?php echo $author_url; ?>" class="avatar">
                                                <?php echo get_avatar( $author, 60 );?>
                                                <?php echo $author_name; ?>
                                            </a>
                                            <?php if(function_exists('um_get_core_page')){ ?><span class="author-title"><?php echo get_role_name($author);?></span> <?php } ?>
                                        </div>
                                    <?php } ?>
                                    <div class="info <?php echo $show_author?'pull-right':'text-center';?>">
                                        <div class="info-item meta">
                                            <a class="meta-item j-heart" href="javascript:;" data-id="<?php the_ID(); ?>"><i class="fa fa-heart"></i> <span class="data"><?php $favorites = get_post_meta($post->ID, 'wpcom_favorites', true); echo $favorites?$favorites:0;?></span></a>
                                            <?php if ( isset($options['comments_open']) && $options['comments_open']=='1' ) { ?><a class="meta-item" href="#comments"><i class="fa fa-comment"></i> <span class="data"><?php echo get_comments_number();?></span></a><?php } ?>
                                            <?php if($dashang_display==0 && isset($options['dashang_1_img']) && ($options['dashang_1_img'] || $options['dashang_2_img'])){ ?>
                                            <a class="meta-item dashang" href="javascript:;">
                                                <i class="icon-dashang"></i>
                                                <span class="dashang-img<?php if($options['dashang_1_img']&&$options['dashang_2_img']){echo ' dashang-img2';}?>">
                                                    <?php if($options['dashang_1_img']){ ?>
                                                        <span>
                                                        <img src="<?php echo esc_url($options['dashang_1_img'])?>" alt="<?php echo esc_attr($options['dashang_1_title'])?>"/>
                                                            <?php echo $options['dashang_1_title'];?>
                                                    </span>
                                                    <?php } ?>
                                                    <?php if($options['dashang_2_img']){ ?>
                                                        <span>
                                                        <img src="<?php echo esc_url($options['dashang_2_img'])?>" alt="<?php echo esc_attr($options['dashang_2_title'])?>"/>
                                                            <?php echo $options['dashang_2_title'];?>
                                                    </span>
                                                    <?php } ?>
                                                </span>
                                            </a>
                                            <?php } ?>
                                        </div>
                                        <div class="info-item share">
                                            <a class="meta-item" href="javascript:;">
                                                <i class="fa fa-wechat"></i>
                                                <span class="wx-wrap">
                                                    <img src="//pan.baidu.com/share/qrcode?w=320&h=320&url=<?php the_permalink();?>">
                                                    <span>扫码分享到微信</span>
                                                </span>
                                            </a>
                                            <a class="meta-item" href="http://share.baidu.com/s?type=text&searchPic=1&sign=on&to=tsina&key=&url=<?php echo urlencode(get_permalink());?>&title=<?php echo urlencode(get_the_title());?>" target="_blank"><i class="fa fa-weibo"></i></a>
                                            <a class="meta-item" href="http://share.baidu.com/s?type=text&searchPic=1&sign=on&to=qzone&key=&url=<?php echo urlencode(get_permalink());?>&title=<?php echo urlencode(get_the_title());?>" target="_blank"><i class="fa fa-qq"></i></a>
                                        </div>
                                        <div class="info-item act">
                                            <a href="javascript:;" id="j-reading"><i class="fa fa-file-text"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php do_action('wpcom_echo_ad', 'ad_single_2');?>
                            <?php wpcom_related_post( ($options['related_num']?$options['related_num']:6), ($related_news=$options['related_news'])?$related_news:__('Related posts', 'wpcom'), ($options['related_show_type']?$options['related_show_type']:0)); ?>
                        </div>
                        <?php
                        if ( isset($options['comments_open']) && $options['comments_open']=='1' ) {
                            comments_template();
                        }
                        ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <aside class="sidebar">
            <?php get_sidebar();?>
        </aside>
    </div>
<?php
if(!$current_user->ID){
if (function_exists('um_get_core_page')) {
    $login_url = um_get_core_page('login');
    $reg_url = um_get_core_page('register');
} else {
    $login_url = wp_login_url();
    $reg_url = wp_registration_url();
}
?>
<div class="modal" id="login-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">请登录</h4>
            </div>
            <div class="modal-body login-modal-body">
                <p>您还未登录，请登录后再进行相关操作！</p>
                <div class="login-btn">
                    <a class="btn btn-login" href="<?php echo $login_url;?>">登 录</a>
                    <a class="btn btn-register" href="<?php echo $reg_url;?>">注 册</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } get_footer();?>