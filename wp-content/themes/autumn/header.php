<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php
// Print the <title> tag based on what is being viewed.
global $page, $paged;
wp_title( '|', true, 'right' );
// Add the blog name.
bloginfo( 'name' );
// Add the blog description for the home/front page.
$site_description = get_bloginfo( 'description', 'display' );
if ( $site_description && ( is_home() || is_front_page() ) ) {
	echo " | $site_description";
}
// Add a page number if necessary:
if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
	echo esc_html( ' | ' . sprintf( __( 'Page %s', 'xintheme' ), max( $paged, $page ) ) );
}
?></title>
<?php if( wpjam_get_setting('wpjam_theme', 'favicon') ) { ?>
<link rel="shortcut icon" href="<?php echo wpjam_get_setting('wpjam_theme', 'favicon');?>"/>
<?php }else{ ?>
<link rel="shortcut icon" href="<?php bloginfo('template_url');?>/static/images/favicon.ico"/>
<?php }?>
<?php wp_head();?>
<style type='text/css'>
<?php if( wpjam_get_setting('wpjam_theme', 'theme_color') ) { ?>
html{--accent-color:<?php echo wpjam_get_setting('wpjam_theme', 'theme_color');?>}
<?php }else{ ?>
html{--accent-color:#f16b6f;}
<?php } ?>
<?php if( wpjam_get_setting('wpjam_theme', 'title_hidden')&&!is_single()&&!is_page() ) { ?>
.entry-header .entry-title {text-overflow: ellipsis;white-space: nowrap;overflow: hidden;}
<?php } ?>
</style>
</head>
<body id="body" <?php body_class(); ?>>
<div class="site">
	<header class="site-header">
	<div class="container">
		<div class="navbar">
			<div class="branding-within">
				<?php if( wpjam_get_setting('wpjam_theme', 'logo') ) { ?>
				<a class="logo text" href="<?php bloginfo('url'); ?>" rel="home"><img src="<?php echo wpjam_get_setting('wpjam_theme', 'logo');?>"></a>
				<?php }else{ ?>
				<a class="logo text" href="<?php bloginfo('url'); ?>" rel="home"><?php bloginfo('name'); ?></a>
				<?php }?>
			</div>
			<nav class="main-menu hidden-xs hidden-sm hidden-md">
			<ul id="menu-primary" class="nav-list u-plain-list">
				<?php if(function_exists('wp_nav_menu')) wp_nav_menu(array('container' => false, 'items_wrap' => '%3$s', 'theme_location' => 'main')); ?>
			</ul>
			</nav>
			<?php if( wpjam_get_setting('wpjam_theme', 'social') ) { ?>
			<?php }else{ ?>
			<?php if ( ! wp_is_mobile() ) {?>
			<div class="search-open navbar-button">
				<i class="iconfont icon-sousuo"></i>
			</div>
			<style>.iconfont.icon-sousuo{font-size: 20px;font-weight: 700}.search-open.navbar-button{background-color: #fff;border: none;}</style>
			<?php } ?>
			<?php } ?>
			<div class="main-search">
				<form method="get" class="search-form inline" action="<?php bloginfo('url'); ?>">
					<input type="search" class="search-field inline-field" placeholder="输入关键词进行搜索…" autocomplete="off" value="" name="s" required="true">
					<button type="submit" class="search-submit"><i class="iconfont icon-sousuo"></i></button>
				</form>
				<div class="search-close navbar-button">
					<i class="iconfont icon-guanbi1"></i>
				</div>
			</div>
			<div class="col-hamburger hidden-lg hidden-xl">
				<div class="hamburger">
				</div>
				<div class="search-open navbar-button">
					<i class="iconfont icon-sousuo"></i>
				</div>
			</div>
			<?php if( wpjam_get_setting('wpjam_theme', 'social') ) { ?>
			<div class="col-social hidden-xs hidden-sm hidden-md">
				<div>
					<div class="social-links">
						<?php if( wpjam_get_setting('wpjam_theme', 'autumn_weibo') ) : ?>
						<a href="<?php echo wpjam_get_setting('wpjam_theme', 'autumn_weibo'); ?>" title="微博" target="_blank" rel="nofollow">
							<i class="iconfont icon-weibo"></i>
						</a>
						<?php endif; ?>
						<?php if( wpjam_get_setting('wpjam_theme', 'autumn_qq') ) : ?>
						<a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo wpjam_get_setting('wpjam_theme', 'autumn_qq'); ?>&site=qq&menu=yes" title="QQ" target="_blank" rel="nofollow">
							<i class="iconfont icon-QQ"></i>
						</a>
						<?php endif; ?>
						<?php if( wpjam_get_setting('wpjam_theme', 'autumn_weixin') ) : ?>
						<a id="tooltip-f-weixin" href="javascript:void(0);" title="微信">
							<i class="iconfont icon-weixin"></i>
						</a>
						<?php endif; ?>
						<?php if( wpjam_get_setting('wpjam_theme', 'autumn_mail') ) : ?>
						<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=<?php echo wpjam_get_setting('wpjam_theme', 'autumn_mail'); ?>" title="QQ邮箱" target="_blank" rel="nofollow">
							<i class="iconfont icon-youxiang"></i>
						</a>
						<?php endif; ?>
					</div>
					<div class="search-open navbar-button">
						<i class="iconfont icon-sousuo"></i>
					</div>
				</div>
			</div>
			<?php }else{ ?>
			<?php if ( wp_is_mobile() ) {?>
			<div class="col-social hidden-xs hidden-sm hidden-md">
				<div>
					<div class="search-open navbar-button">
						<i class="iconfont icon-sousuo"></i>
					</div>
				</div>
			</div>
			<?php } ?>
			<?php } ?>
		</div>
	</div>
	</header>
<div class="off-canvas">
	<div class="mobile-menu">
	</div>
	<div class="close">
		<i class="iconfont icon-guanbi1"></i>
	</div>
</div>