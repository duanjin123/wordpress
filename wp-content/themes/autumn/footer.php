<div class="social-bar">
<?php if( wpjam_get_setting('wpjam_theme', 'foot_link') ) : ?>
<?php if ( is_home()&&!wp_is_mobile() ) { ?>
	<li style="color: #777;font-size: 14px;">友情链接：</li><?php wp_list_bookmarks('title_li=&categorize=0'); ?>
<?php } ?>
<?php endif; ?>
</div>
<footer class="site-footer">
<div class="site-info">
	Copyright <?php the_time('Y') ?>. All rights reserved.&nbsp;<a rel="nofollow" target="_blank" href="http://www.miibeian.gov.cn/"><?php echo wpjam_get_setting('wpjam_theme', 'footer_icp');?></a>&nbsp;Powered by&nbsp;
	<a href="http://www.xintheme.com" target="_blank">XinTheme</a>&nbsp;+&nbsp;<a href="https://blog.wpjam.com/" target="_blank">WordPress 果酱</a><?php if( wpjam_get_setting('wpjam_theme', 'foot_timer') ) : ?>.&nbsp;页面加载时间：<?php timer_stop(1);?> 秒<?php endif; ?>
</div>
</footer>
</div>
<?php if( wpjam_get_setting('wpjam_theme', 'autumn_weixin') ) : ?>
<div class="f-weixin-dropdown">
	<div class="tooltip-weixin-inner">
		<h3>微信扫一扫</h3>
		<div class="qcode"> 
			<img src="<?php echo wpjam_get_setting('wpjam_theme', 'autumn_weixin');?>" alt="微信扫一扫">
		</div>
	</div>
	<div class="close-weixin">
		<span class="close-top"></span>
			<span class="close-bottom"></span>
    </div>
</div>
<?php endif; ?>
<!--以下是分享-->
<div class="dimmer"></div>
<div class="modal">
  <div class="modal-thumbnail">
    <img class="lazyloaded" data-src="" src="">
  </div>
  <h6 class="modal-title"></h6>
  <div class="modal-share">
    <a class="weibo_share" href="#" target="_blank"><i class="iconfont icon-weibo"></i></a>
    <a class="qq_share" href="#" target="_blank"><i class="iconfont icon-QQ"></i></a>
    <a href="javascript:;" data-module="miPopup" data-selector="#post_qrcode" class="weixin"><i class="iconfont icon-weixin"></i></a>
  </div>
  <form class="modal-form inline">
    <input class="modal-permalink inline-field" value="" type="text">
    <button data-clipboard-text="" type="submit"><i class="iconfont icon-fuzhi"></i></button>
  </form>
</div>
<div class="dialog-xintheme" id="post_qrcode">
	<div class="dialog-content dialog-wechat-content">
		<p>
			微信扫一扫,分享到朋友圈
		</p>
		<img class="weixin_share" src="https://bshare.optimix.asia/barCode?site=weixin&url=<?php the_permalink(); ?>" alt="<?php the_title_attribute(); ?>">
		<div class="btn-close">
			<i class="iconfont icon-guanbi1"></i>
		</div>
	</div>
</div>
<!--禁止选中-->
<script type="text/javascript">
<?php if( wpjam_get_setting('wpjam_theme', 'xintheme_copy') ) :?>
document.getElementById("body").onselectstart = function(){return false;};
<?php endif;?>
</script>
<?php if( wpjam_get_setting('wpjam_theme', 'cool_qq') ) :?>
<div class="container mobile-hide" id="J_container">
	<a class="livechat-girl js-livechat-girl animated" id="lc-girl-block-en_2" target="_blank" rel="nofollow" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo wpjam_get_setting('wpjam_theme', 'autumn_qq'); ?>&site=qq&menu=yes">
		<img class="girl" src="<?php bloginfo('template_directory'); ?>/static/images/qq.png" title="点击这里给我发消息" border="0">
	<div class="js-livechat-hint livechat-hint rd-notice rd-notice-tooltip single-line hide_hint">
		<div class="popover-content rd-notice-content">
			嘿！有什么能帮到您的吗？
		</div>
	</div>
	<div class="animated-circles js-animated-circles animated">
		<div class="circle c-1">
		</div>
		<div class="circle c-2">
		</div>
		<div class="circle c-3">
		</div>
	</div>
	</a>
</div>
<?php endif;?>
<div class="gotop">         
    <a id="goTopBtn" href="javascript:;"><i class="iconfont icon-shang"></i><em>返回顶部</em></a>
</div>
<?php wp_footer(); ?>
<?php if ( is_single() ) { ?>
<?php if( wpjam_get_setting('wpjam_theme', 'comment_flower') ) {?>
<script type="text/javascript">
	POWERMODE.colorful = true;
	<?php if( wpjam_get_setting('wpjam_theme', 'comment_shock') ) {?>
	POWERMODE.shake = true;
	<?php }else{ ?>
	POWERMODE.shake = false;
	<?php } ?>
	document.body.addEventListener('input', POWERMODE);
</script>
<?php } ?>
<?php } ?>
<?php if( wpjam_get_setting('wpjam_theme', 'click_effect') ) { ?>
<script type="text/javascript">
    var a_idx = 0;
    jQuery(document).ready(function($) {
        $("body").click(function(e) {
    var a = new Array(<?php
	$click_effect= wpjam_get_setting('wpjam_theme', 'click_effect');
    if(is_array( wpjam_get_setting('wpjam_theme', 'click_effect') )):
        $i=0;
        foreach ( $click_effect as $value ):
            $i++;
            if($i!=1){echo ',';}
            echo '"'.$value.'"';
    endforeach;endif;?>
	);
    var $i = $("<span/>").text(a[a_idx]);
            a_idx = (a_idx + 1) % a.length;
    var x = e.pageX,
            y = e.pageY;
            $i.css({
    "z-index": 999999999999999999999999999,
    "top": y - 20,
    "left": x,
    "position": "absolute",
    "font-weight": "bold",
    "color": "<?php echo wpjam_get_setting('wpjam_theme', 'click_effect_color');?>"
            });
            $("body").append($i);
            $i.animate({
    "top": y - 180,
    "opacity": 0
            },
            1500,
    function() {
                $i.remove();
            });
        });
    });	
</script>
<?php } ?>
</body>
</html>