<?php

function wpjam_qiniutek_update_page(){
	global $current_admin_url;

	$updates = '';

	if(isset($_GET['refresh'])){
		update_option('timestamp',time());
		wpjam_admin_add_error('已经刷新本地JS和CSS浏览器缓存！');
	}

	$form_fields = [
		'updates'	=> ['title'=>'', 'type'=>'textarea', 'rows'=>10, 'description'=>'请输入需要更新的文件，每行一个！'],
	];
	
	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		$data = wpjam_get_form_post($form_fields);

		$updates = $data['updates'];

		$updates_array = explode("\n", $updates);

		foreach ($updates_array as $update) {
			if(trim($update)){
				$update = preg_replace('/\?.*/', '', $update);
				$response = wpjam_qiniutek_delete_file($update);
				if(is_wp_error($response)){
					wpjam_admin_add_error($update.'：（'.$response->get_error_code().'）'.$response->get_error_message(), 'error');
				}else{
					wpjam_admin_add_error($update.'更新成功');
				}
			}
		}
	}

	?>
	<h2>更新文件</h2>

	<?php wpjam_form($form_fields, $current_admin_url, '', '更新文件'); ?>

	<ol>
		<li>点击“更新文件”按钮之后，只要文件后面显示更新成功，即代表更新成功。</li>
		<li>如果实时查看还是旧的文件，可能是你浏览器的缓存，你需要清理下缓存，或者等待自己更新。</li>
		<li>如果你更新的是主题或者插件的JS和CSS文件，可以再次点击下面按钮刷新本地缓存：<br />
		<a class="button" href="<?php echo $current_admin_url.'&refresh'; ?>">刷新本地JS和CSS浏览器缓存</a></li>
		<li>图片缩略图更新七牛是按照按照队列顺序进行的，需要等待一定的时间，只要看到原图更新即可。</li>
	</ol>
	<?php
}