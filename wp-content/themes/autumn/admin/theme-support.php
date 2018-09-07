<?php 

function wpjam_theme_support_page(){
	echo "<h1>主题支持</h1>";

	echo '<p>禁止删除网站页脚的链接（Powered by XinTheme + WordPress 果酱），如果你实在看这个链接碍眼就支付 <b>200元RMB</b> ，或放弃使用这个主题，谢谢！</p>';
	echo '<p>在使用过程中遇到任何问题都可以在网站后台的【讨论组】中发布问题，看到了就会回复你。</p>';
	echo '<p>如果你是个小白，什么都不会的那种，我们提供服务器以及主题安装调试服务，请私聊。</p>';
	
	echo "<h2>主题更新</h2>";

	$theme_info	= wpjam_remote_request('http://www.xintheme.com/api?id=4893');
	$version	= $theme_info['Version'];

	$current_theme	= wp_get_theme();

	if($version > $current_theme->get( 'Version' )){
		echo '<p>主题有更新，<a href="'.$theme_info['Link'].'" target="_blank">请及时查看！</a></p>';
	}else{
		echo '<p>你的主题目前已经是最新版了！</p>';
	}
	
	echo "<h2>常见问题</h2>";
	
	echo '<p><b>问：</b>评论框以及后台登陆界面显示英文。</p>';
	echo '<p><b style="color: #e12020;">答：</b>作者比较懒，所以没有单独写评论框，调用WordPress默认的评论框，如果你勾选了插件里面的【前台不加载语言包】评论和后台登陆界面就会显示英文。</p>';

	echo '<p><b>问：</b>小工具显示不出来了。</p>';
	echo '<p><b style="color: #e12020;">答：</b>插件的优化设置里面有个【主题 Widget】的选项，不要勾选就好了。</p>';
	
	echo '<p><b>问：</b>使用七牛云储存以后图片有噪点（模糊）怎么办？</p>';
	echo '<p><b style="color: #e12020;">答：</b>后台七牛设置 - 本地设置，将图片质量调整为<b>100</b>即可。</p>';
	
	echo '<p><b>问：</b>这个主题不能自定义SEO设置？</p>';
	echo '<p><b style="color: #e12020;">答：</b>【WPJAM插件 - 扩展管理 - SEO】勾选即可。</p>';
	
	
	echo "<h2>其他问题</h2>";

	echo '<p>使用过程有什么问题，请到 <a href="http://97866.com/s/zsxq/">WordPress 果酱知识星球</a>反馈或者提问。</p>';
	
	echo "<h2>打赏作者</h2>";
	
	echo '<img src="http://cdn.xintheme.com/wechat.png" alt="微信扫一扫">';
	echo '<img src="http://cdn.xintheme.com/alipay.png" alt="微信扫一扫">';
	
}
