<?php
add_action('admin_head',function(){ ?>
<style type="text/css">
	#list_region_options input,#dark_mode_options input {margin-top: 0px;}
	#list_region_options label,#dark_mode_options label {margin-right: 10px;}

	#slide_region_options label{
	display:inline-block;
	width:156px;
	height:111px;
	background-repeat:no-repeat;
	background-size: contain;
	margin-right:10px;
	}

	#slide_region_options input{
		display: none;
	}
	
	<?php for ($i=1; $i<=4; $i++) { ?>

	#label_slide_region_<?php echo $i; ?>{	
	background-image: url(<?php echo get_stylesheet_directory_uri().'/static/images/set/slide-'.$i.'.png';?>);
	}

	#slide_region_<?php echo $i; ?>:checked + #label_slide_region_<?php echo $i; ?> {
	border:1px solid #000;
	}

	<?php } ?>
	
</style>
	<script type="text/javascript">
	jQuery(function($){
		
		$('tr#tr_slide_bg_img').hide();
		
		$('body').on('change', '#slide_region_options input', function(){
			$('tr#tr_slide_bg_img').show();

			if ($(this).is(':checked')) {
				if($(this).val() != '4'){
					$('tr#tr_slide_bg_img').hide();
				}
			}			
		});

		$('select#slide_region').change();
	});

	</script>
<?php });?>
	
<?php
if(!WPJAM_Verify::verify()){
	wp_redirect(admin_url('admin.php?page=wpjam-basic'));
	exit;		
}

add_filter('wpjam_theme_setting', function(){
	
	$list_sub_fields		= [
		'list_read'			=> '文章阅读数量',
		'list_comment'		=> '文章评论数量',
		'list_like'			=> '文章点赞数量',
	];

	$single_sub_fields		= [
		'xintheme_author'	=> '作者模块',
		'single_tag'		=> '文章标签',
	];
	$list_sub_fields		= array_map(function($desc){return ['title'=>'','type'=>'checkbox','description'=>$desc]; }, $list_sub_fields);
	$single_sub_fields		= array_map(function($desc){return ['title'=>'','type'=>'checkbox','description'=>$desc]; }, $single_sub_fields);
	
	$sections	= [ 
		'icon'	=>[
			'title'		=>'网站图标', 
			'fields'	=>[
				'logo'		=> ['title'=>'网站 LOGO',			'type'=>'img',	'item_type'=>'url',	'size'=>'120*40', 'description'=>'尺寸：120x40'],
				'favicon'	=> ['title'=>'网站 Favicon图标',	'type'=>'img',	'item_type'=>'url'],
			]
		],
		'layout'	=>[
			'title'		=>'布局设置', 
			'fields'	=>[
				'navbar_sticky'		=> ['title'=>'固定导航栏',			'type'=>'checkbox',	'description'=>'导航栏一直固定显示在网站顶部'],
				'slide_region'		=> ['title'=>'首页轮播区域（置顶文章）',	'type'=>'radio',	'options'=>['1'=>'','2'=>'','4'=>'','3'=>''], 'description'=>'幻灯片为【置顶文章】，不设置【置顶文章】则不显示。', 'show_admin_column'=>true],
				'slide_bg_img'		=>['title'=>'背景图像',				'type'=>'img',		'item_type'=>'url',	'description'=>'上传一张背景图像'],
				'slide_number'		=> ['title'=>'置顶文章显示数量',	'type'=>'number',	'description'=>'自定义首页轮播区（置顶文章）显示数量，建议10篇内','mim'=>1,'max'=>100],
				'index_cat'			=> ['title'=>'分类模块',			'type'=>'select',	'options'=>['1'=>'关闭','2'=>'开启']],
				'index_cat_id'		=> ['title'=>'填写分类id',			'type'=>'mu-text',	'description'=>'可添加多个id，拖动排序【此项仅在分类模块（上一条选项）开启后生效】'],
				'list_region'		=> ['title'=>'文章列表',			'type'=>'radio',	'options'=>['col_3'=>'三列','col_4'=>'四列']],
				'title_hidden'		=> ['title'=>'隐藏标题超出部分',	'type'=>'checkbox',	'description'=>'文章标题有长有短就造成了文章列表里面的标题有的两行有的一行，影响美观，介意的用户可以勾选这个选项，利用css隐藏超出的标题，使其只显示一行'],
				'paging_xintheme'	=> ['title'=>'分页样式',			'type'=>'select',	'options'=>['1'=>'数字分页','2'=>'上一页|下一页','3'=>'点击按钮加载','4'=>'滚动页面自动加载']],
			]
		],
		'foot_setting'	=>[
			'title'		=>'底部设置', 
			'fields'	=>[
				'footer_icp'		=> ['title'=>'网站备案号',			'type'=>'text',		'rows'=>4],
				'foot_link'			=> ['title'=>'友情链接',			'type'=>'checkbox',	'description'=>'激活“友情链接”，显示在首页底部，在【后台 - 连接】中添加友情链接'],
				'foot_timer'		=> ['title'=>'页面加载时间',		'type'=>'checkbox',	'description'=>'页脚显示当前页面加载时间'],
			],	
		],
		'extend'	=>[
			'title'		=>'扩展选项', 
			'summary'	=>'<p>下面的选项，可以让你选择性显示或关闭一些功能。</p>',
			'fields'	=>[
				'list'		=>['title'=>'文章列表页面',	'type'=>'fieldset',	'fields'=>$list_sub_fields],
				'single'	=>['title'=>'文章详情页面',	'type'=>'fieldset',	'fields'=>$single_sub_fields],
			],	
		],
		'social'	=>[
			'title'		=>'社交工具', 
			'fields'	=>[
				'social'			=> ['title'=>'显示社交工具',		'type'=>'checkbox', 'description'=>'社交工具显示在网站头部',],
				'autumn_weixin'		=> ['title'=>'上传微信二维码',		'type'=>'img',		'item_type'=>'url'],
				'autumn_qq'			=> ['title'=>'输入QQ号码',			'type'=>'text',		'rows'=>4],
				'autumn_weibo'		=> ['title'=>'输入微博链接',		'type'=>'text',		'rows'=>4],
				'autumn_mail'		=> ['title'=>'输入邮箱账号',		'type'=>'text',		'rows'=>4],
				'cool_qq'			=> ['title'=>'炫酷的客服按钮',		'type'=>'checkbox', 'description'=>'在全站悬浮一个客服按钮',],
			],	
		],
/* 		'single-ad'	=>[
			'title'		=>'广告设置', 
			'fields'	=>[
				//'ad_type'		=> ['title'=>'选择广告类型',			'type'=>'select',	'options'=>['img'=>'图片广告','code'=>'广告代码']],
				'ad_tips'			=> ['title'=>'显示广告标识',		'type'=>'checkbox', 'description'=>'广告位置会显示“广告”两个字',],
				'single_ad_pc'		=> ['title'=>'广告代码(电脑端)',	'type'=>'textarea', 'description'=>'广告位于文章页内容底部',	'rows'=>4],
				'single_ad_mobile'	=> ['title'=>'广告代码(手机端)',	'type'=>'textarea', 'description'=>'广告位于文章页内容底部',	'rows'=>4],
			],	
		], */
		'optimization'	=>[
			'title'		=>'优化加速', 
			'fields'	=>[
				'xintheme_v2ex'		=> ['title'=>'Gravatar镜像服务',		'type'=>'checkbox',	'description'=>'使用国内的Gravatar镜像服务，提高网站加载速度，https://cdn.v2ex.com/gravatar'],
				'xintheme_copy'		=> ['title'=>'整站禁止复制',			'type'=>'checkbox',	'description'=>'用js onselectstart事件禁止选中文字，有效防止内容被访客复制'],
				'xintheme_article'	=> ['title'=>'登陆后台跳转到文章列表',	'type'=>'checkbox',	'description'=>'WordPress登陆后台后默认是显示仪表盘页面，开启这个功能登陆后台默认显示文章列表'],
				'xintheme_feed'		=> ['title'=>'关闭Feed',				'type'=>'checkbox',	'description'=>'Feed易被利用采集，造成不必要的资源消耗，建议关闭'],
			],	
		],
		'comments'	=>[
			'title'		=>'评论框扩展', 
			'summary'	=>'<p>主题调用WordPress默认评论框，如果你勾选了插件里面的【前台不加载语言包】评论和后台登陆界面就会显示英文。</p>',
			'fields'	=>[
				'comment_flower'	=> ['title'=>'礼花特效',				'type'=>'checkbox', 'description'=>'网站评论框，输入内容时增加礼花特效（此功能对手机端无效）',],
				'comment_shock'		=> ['title'=>'震动特效',				'type'=>'checkbox', 'description'=>'需同时开启礼花特效，不然不会生效',],
				'comment-form-url'	=> ['title'=>'隐藏评论框网址栏',		'type'=>'checkbox',	'description'=>'勾选后评论框将不显示网址输入框'],
			],	
		],
		'click_effect'	=>[
			'title'		=>'鼠标点击特效', 
			'summary'	=>'<p>在网站任意位置点击鼠标，会不断的跳出你输入的词汇或者符号，留空则不显示。</p>',
			'fields'	=>[
				'click_effect'			=> ['title'=>'鼠标点击特效',			'type'=>'mu-text',	'description'=>'可添加多个词汇'],
				'click_effect_color'	=> ['title'=>'字体颜色',				'type'=>'color'],
			],	
		],
		'color'	=>[
			'title'		=>'主题配色', 
			'fields'	=>[
				'theme_color'			=> ['title'=>'选择主题配色',		'type'=>'color'],
				'dark_mode'				=> ['title'=>'暗黑模式',			'type'=>'radio',	'options'=>['0'=>'关闭','1'=>'开启']],
			],	
		]
	];
	
	return compact('sections');
});