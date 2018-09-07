<?php
add_filter('wpjam_basic_setting', function(){
	$smtp_fields = [
		'smtp_mail_from_name'	=> ['title'=>'发送者姓名',	'type'=>'text'],

		'smtp'					=> ['title'=>'SMTP 设置',	'type'=>'fieldset','fields'=>[
			'smtp_host'	=> ['title'=>'地址',		'type'=>'text',		'value'=>'smtp.gmail.com'],
			'smtp_ssl'	=> ['title'=>'发送协议',	'type'=>'text',		'value'=>'ssl'],
			'smtp_port'	=> ['title'=>'SSL端口',	'type'=>'number',	'value'=>'465'],
			'smtp_user'	=> ['title'=>'邮箱账号',	'type'=>'email'],
			'smtp_pass'	=> ['title'=>'邮箱密码',	'type'=>'password'],
		]],

		'smtp_reply'			=> ['title'=>'默认回复',	'type'=>'fieldset','fields'=>[
			'smtp_reply_to_mail'	=> ['title'=>'邮箱地址',	'type'=>'email'],
			'smtp_reply_to_name'	=> ['title'=>'邮箱姓名',	'type'=>'text'],
		]],
	];

	$sections	= [
		'wpjam-smtp'	=> [
			'title'		=>'', 
			'fields'	=>$smtp_fields, 
			'summary'	=>'<p>点击这里查看：<a target="_blank" href="http://blog.wpjam.com/m/gmail-qmail-163mail-imap-smtp-pop3/">常用邮箱的 SMTP 设置</a>。</p>'
		]
	];

	return compact('sections');
});

function wpjam_smtp_send_page(){
	global $current_admin_url;

	$form_fields = array(
		'to'		=> array('title'=>'收件人',	'type'=>'email'),
		'subject'	=> array('title'=>'主题',	'type'=>'text'),
		'message'	=> array('title'=>'内容',	'type'=>'textarea',	'style'=>'max-width:640px;',	'rows'=>8),
	);

	$nonce_action = 'send_mail';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data	= wpjam_get_form_post($form_fields, $nonce_action);
		foreach ($form_fields as $key => $form_field) {
			$form_fields[$key]['value']	= $data[$key];
		}

		extract($data);
		
		if(wp_mail($to, $subject, $message)){
			wpjam_admin_add_error('发送成功');
		}else{
			wpjam_admin_add_error('发送失败','error');
		}
	}
	?>

	<h2>发送测试</h2>

	<?php wpjam_form($form_fields, $current_admin_url, $nonce_action, '发送'); ?>
	<?php
}

add_action('wp_mail_failed', function ($mail_failed){
	trigger_error($mail_failed->get_error_code().$mail_failed->get_error_message());
	var_dump($mail_failed);
});