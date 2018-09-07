<?php
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	check_admin_referer('reset_secret');

	if(!current_user_can('manage_options')){
		wp_die('开玩笑');
	}

	$secret	= WPJAM_API::reset_secret(); 

	wpjam_admin_add_error('新的密钥是： '.$secret);
}

function wpjam_grant_page(){
	global $plugin_page;
	?>
	<h1>开放平台</h1>
	
	<?php if($plugin_page == 'shop-grant'){?>

	<style type="text/css">
	div.card {max-width: 640px;}
	</style>

	<div class="card">
		<h2>开放平台</h2>
		<p>花生小店的“开放平台”，是花生小店“基建+营销+开放”的战略布局。</p>
		<p>我们免费提供平台方小程序的相关开发文档以及开放接口，目前已开放的接口，如：订单管理、商品管理、客服管理等模块的所有功能，让有能力的运营者在面向垂直行业需求时，可以通过二次开发或接入第三方服务商，来完成相关功能模块的能力，满足商户需求的多样性。</p>

		<p>更多功能模块接口，请参考“<a href="<?php echo admin_url('admin.php?page=shop-open');?>">开放文档</a>”。</p>
	</div>
	<?php } ?>
	<div class="card">
		<h2>开发者ID</h2>
		<form method="post" action="#" onsubmit="return confirm('你确定要重置？');">
			<table class="form-table widefat striped" style="border: none;">
				<tbody>
					<tr>
						<td style="width: 80px;">AppID</td>
						<td>
							<p><?php echo WPJAM_API::get_appid(); ?></p>
							<?php if($plugin_page == 'shop-grant'){?><p><small style="color:#666;">此处AppID仅用于本系统第三方开发，与微信小程序官方的AppID无关。<br />请在微信官方小程序后台-开发设置 中获取你的AppID。</small></p><?php } ?>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Secret</td>
						<td><small>出于安全考虑，Secret不再被明文保存，忘记密钥请点击重置：</small></td>
						<td><?php wp_nonce_field('reset_secret');?><input type="submit" name="rest" value="重置" class="button"></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<?php
}