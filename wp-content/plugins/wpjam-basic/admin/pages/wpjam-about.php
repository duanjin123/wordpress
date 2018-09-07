<?php
function wpjam_basic_about_page(){
	?>
	<div style="width:600px;">

		<h2>关于WPJAM</h2>

		<p><strong><a href="http://blog.wpjam.com/project/wpjam-basic/">WPJAM Basic</a></strong> 是 <strong><a href="http://blog.wpjam.com/">我爱水煮鱼</a></strong> 博主 Denis 开发的 WordPress 插件，WPJAM Basic 除了能够优化你的 WordPress ，也是 WordPress 果酱团队进行 WordPress 二次开发的基础。</p>

		<p>为了方便开发，WPJAM Basic 使用了最新的 PHP 7.2 语法，所以要使用该插件，需要你的服务器的 PHP 版本是 7.2 或者更高。</p>

		<h3>其他插件</h3>

		<p><strong><a href="http://blog.wpjam.com/project/wpjam-qiniutek/">WPJAM 七牛插件</a></strong> 已经整合到 WPJAM Basic 插件之中，无须独立安装。</p>
		<p><strong><a href="http://blog.wpjam.com/project/weixin-robot-advanced/">微信机器人高级版</a></strong> 也已免费，需要预先安装 WPJAM Basic。</p>
		<p>小程序管理插件，紧张最后制作，即将推出。</p>
		<p>除了 WPJAM Basic ，WPJAM 七牛插件，微信机器人高级版，以及即将推出的小程序插件之外，其他插件将以扩展的模式整合到 WPJAM Basic 插件一并发布。</p>

		<h3>其他问题</h3>

		<p>使用过程有什么问题，请到 <strong><a href="http://97866.com/s/zsxq/">WordPress 果酱知识星球反馈或者提问</a></strong>。</p>

	</div>

	<?php 
}

function wpjam_basic_update_page(){
	$jam_posts	= get_transient('jam_posts');

	if($jam_posts === false){
		$api_url	= 'http://jam.wpweixin.com/api/mag.post.list.json?category_name=wordpress&posts_per_page=20';
		$jam_response	= wpjam_remote_request($api_url);

		if(is_wp_error($jam_response)){
			wpjam_admin_add_error($jam_response->get_error_message(), 'error');
		}else{
			$jam_posts	= $jam_response['posts'];

			set_transient('jam_posts', $jam_posts, HOUR_IN_SECONDS);
		}
	}
	?>

	<h2>WPJAM更新</h2>

	<?php if($jam_posts){ ?>


	<table class="form-table widefat striped" style="border: none; margin-top: 20px;">
		<tbody>
			<?php $i=0; foreach ($jam_posts as $jam_post) { ?>
			<tr>
				<td>
					<a href="https://blog.wpjam.com<?php echo $jam_post['post_url']; ?>" target="_blank">
					<img src="<?php echo $jam_post['thumbnail']; ?>" style="float:left; width:60px; margin-right: 1em;" />
					<h4 style="margin-top: 0;"><?php echo $jam_post['title']; ?></h4>
					<p><?php echo !empty($jam_post['post_tag'])?$jam_post['post_tag'][0]['name']:'';?> <span style="float: right;"><?php echo human_time_diff($jam_post['timestamp']); ?>前</span></p>
					</a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<?php } ?>

	<?php
}