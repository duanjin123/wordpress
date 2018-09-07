<?php
add_filter('wpjam_qiniutek_setting', function(){
	$qiniutek_fields = [
		'host'		=> ['title'=>'七牛域名',		'type'=>'url',		'description'=>'设置为七牛提供的测试域名或者在七牛绑定的域名。<strong>注意要域名前面要加上 http://</strong>。<br />如果博客安装的是在子目录下，比如 http://www.xxx.com/blog，这里也需要带上子目录 /blog '],
		'bucket'	=> ['title'=>'七牛空间名',	'type'=>'text',		'description'=>'设置为你在七牛提供的空间名。'],
		'access'	=> ['title'=>'ACCESS KEY',	'type'=>'text'],
		'secret'	=> ['title'=>'SECRET KEY',	'type'=>'text'],
	];

	$local_fields = [		
		'exts'		=> ['title'=>'扩展名',		'type'=>'text',		'value'=>'js|css|png|jpg|jpeg|gif|ico',	'description'=>'设置要缓存静态文件的扩展名，请使用 | 分隔开，|前后都不要留空格。'],
		'dirs'		=> ['title'=>'目录',			'type'=>'text',		'value'=>'wp-content|wp-includes',		'description'=>'设置要缓存静态文件所在的目录，请使用 | 分隔开，|前后都不要留空格。'],
		'local'		=> ['title'=>'本地域名',		'type'=>'url',		'description'=>'如果图片等静态文件存储的域名和网站不同，可通过该字段设置。<br />使用该字段设置静态文件所在的域名之后，请确保 JS 和 CSS 等文件也在该域名下，否则将不会加速。'],
		'locals'	=> ['title'=>'其他域名',		'type'=>'mu-text',	'item_type'=>'url'],
	];

	$image_fields	= [
		// 'webp'		=> ['title'=>'WebP格式',		'type'=>'checkbox',	'description'=>'将图片转换成WebP格式，可以压缩到原来的2/3大小。'),
		'imageslim'	=> ['title'=>'图片瘦身',		'type'=>'checkbox',	'description'=>'将存储在七牛的JPEG、PNG格式的图片实时压缩而尽可能不影响画质。'],
		'interlace'	=> ['title'=>'渐进显示',		'type'=>'checkbox',	'description'=>'是否JPEG格式图片渐进显示。'],
		'quality'	=> ['title'=>'图片质量',		'type'=>'number',	'description'=>'1-100之间图片质量，七牛默认为75。','mim'=>0,'max'=>100]
	];

	$thumb_fields = [
		'default'	=> ['title'=>'默认缩略图',	'type'=>'image',	'description'=>'如果日志没有特色图片，没有第一张图片，也没用高级缩略图的情况下所用的缩略图。可以填本地或者七牛的地址！'],
		'width'		=> ['title'=>'图片最大宽度',	'type'=>'number',	'description'=>'设置博客文章内容中图片的最大宽度，插件会使用将图片缩放到对应宽度，节约流量和加快网站速度加载。'],
	];

	$remote_fields = [
		'remote'	=> ['title'=>'保存远程图片',		'type'=>'checkbox',	'description'=>'自动将远程图片镜像到七牛。'],
		'exceptions'=> ['title'=>'例外',			'type'=>'textarea',	'class'=>'regular-text',	'description'=>'如果远程图片的链接中包含以上字符串或者域名，就不会被保存并镜像到七牛。'],
	];

	$watermark_options = [
		'SouthEast'	=> '右下角',
		'SouthWest'	=> '左下角',
		'NorthEast'	=> '右上角',
		'NorthWest'	=> '左上角',
		'Center'	=> '正中间',
		'West'		=> '左中间',
		'East'		=> '右中间',
		'North'		=> '上中间',
		'South'		=> '下中间',
	];

	$watermark_fields = [
		'watermark'	=> ['title'=>'水印图片',	'type'=>'image',	'description'=>''],
		'disslove'	=> ['title'=>'透明度',	'type'=>'number',	'description'=>'透明度，取值范围1-100，缺省值为100（完全不透明）','min'=>0,	'max'=>100],
		'gravity'	=> ['title'=>'水印位置',	'type'=>'select',	'options'=>$watermark_options],
		'dx'		=> ['title'=>'横轴边距',	'type'=>'number',	'description'=>'横轴边距，单位:像素(px)，缺省值为10'],
		'dy'		=> ['title'=>'纵轴边距',	'type'=>'number',	'description'=>'纵轴边距，单位:像素(px)，缺省值为10'],
	];

	$coupon_div = '
	<div id="qiniu_coupon" style="display:none;">
	<p>简单说使用<strong>WordPress插件用户专属的优惠码</strong>“<strong style="color:red;">d706b222</strong>”充值，一次性充值2000元及以内99折，2000元以上则95折</strong>，建议至少充值2001元。详细使用流程：</p>
	<p>1. 登陆<a href="http://wpjam.com/go/qiniu" target="_blank">七牛开发者平台</a></p>
	<p>2. 然后点击“充值”，进入充值页面</p>
	<p><img srcset="'.WPJAM_BASIC_PLUGIN_URL.'/extends/qiniu/qiniu-coupon.png 2x" src="'. WPJAM_BASIC_PLUGIN_URL.'/extends/qiniu/qiniu-coupon.png" alt="使用七牛优惠码" /></p>
	<p>3. 点击“使用优惠码”，并输入优惠码“<strong><span style="color:red;">d706b222</span></strong>”，点击“使用”。</p>
	<p>4. 输入计划充值的金额，点击“马上充值”，进入支付宝页面，完成支付。</p>
	<p>5. 完成支付后，可至财务->>财务概况->>账户余额 查看实际到账金额。</p>
	</div>';


	$sections = [
    	'qiniutek'	=> ['title'=>'七牛设置',		'fields'=>$qiniutek_fields,	'summary'=>'<p>充值可以使用WordPress插件用户专属的优惠码：“<span style="color:red; font-weight:bold;">d706b222</span>”，点击查看<strong><a title="如何使用七牛云存储的优惠码" class="thickbox" href="#TB_inline?width=600&height=480&inlineId=qiniu_coupon">详细使用指南</a></strong>。</p>'.$coupon_div],
    	'local'		=> ['title'=>'本地设置',		'fields'=>$local_fields],
    	'image'		=> ['title'=>'图片设置',		'fields'=>$image_fields],
    	'thumb'		=> ['title'=>'缩略图设置',	'fields'=>$thumb_fields,	'summary'=>'<p>*文章获取缩略图的顺序为：特色图片 > 标签缩略图 > 第一张图片 > 分类缩略图 > 默认缩略图。</p>'],
    	'remote'	=> ['title'=>'远程图片设置',	'fields'=>$remote_fields,	'summary'=>'<p>*自动将远程图片镜像到七牛需要你的博客支持固定链接。<br />*如果前面设置的静态文件域名和博客域名不一致，该功能也可能出问题。<br />*远程 GIF 图片保存到七牛将失去动画效果，所以目前不支持 GIF 图片。</p>'],
    	'watermark'	=> ['title'=>'水印设置',		'fields'=>$watermark_fields]
	];

	if(is_network_admin()){
		// unset($sections['qiniutek']);
		unset($sections['thumb']);
		unset($sections['local']['fields']['local']);
		unset($sections['watermark']['fields']['watermark']);
	}

	$field_Validate	= function($value){
		flush_rewrite_rules();
		return $value;
	};

	return compact('sections', 'field_Validate');
});