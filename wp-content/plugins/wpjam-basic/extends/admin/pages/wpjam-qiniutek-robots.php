<?php

function wpjam_qiniutek_robots_page(){
	global $current_admin_url;

	$qiniutek_robots = get_option('qiniutek_robots');

	if(!$qiniutek_robots){
		$qiniutek_robots = '
User-agent: *
Disallow: /
User-agent: Googlebot-Image
Allow: /
User-agent: Baiduspider-image
Allow: /
		';
	}

	$form_fields = [
		'robots'	=> ['title'=>'', 'type'=>'textarea', 'rows'=>10, 'value'=>$qiniutek_robots, 'description'=>'上传 Robots.txt 文件，防止搜索引擎索引镜像的网页。！'],
	];

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		$data = wpjam_get_form_post($form_fields);

		$robots = $data['robots'];

		if($robots){

			update_option('qiniutek_robots',$robots);

			wpjam_qiniutek_delete_file('robots.txt'); // 如果有，先清理。
			$response = wpjam_qiniutek_put('robots.txt', $robots); // 再上传
			if(is_wp_error( $response )){
				wpjam_admin_add_error('：（'.$response->get_error_code().'）'.$response->get_error_message(), 'error');
			}else{
				wpjam_admin_add_error('上传成功');
			}

		}
	}

	?>
	
	<h2>上传 Robots.txt</h2>

	<?php wpjam_form($form_fields, $current_admin_url, '', '更新Robots.txt'); ?>

	<?php
}