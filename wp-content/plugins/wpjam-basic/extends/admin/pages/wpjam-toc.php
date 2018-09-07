<?php
add_filter('wpjam_basic_setting', function($sections){
	$script = "
jQuery(document).ready(function(){
	jQuery('#toc span').on('click',function(){
		if(jQuery('#toc span').html() == '[显示]'){
			jQuery('#toc span').html('[隐藏]');
		}else{
			jQuery('#toc span').html('[显示]');
		}
		jQuery('#toc ul').toggle();
		jQuery('#toc small').toggle();
	});
});
";

	$css = "
#toc {
	float:right;
	max-width:240px;
	min-width:120px;
	padding:6px;
	margin:0 0 20px 20px;
	border:1px solid #EDF3DE;
	background:white;
	border-radius:6px;
}
#toc p {
	margin:0 4px;
}
#toc strong {
	border-bottom:1px solid #EDF3DE;
	display:block;
}
#toc span {
	display:block;
	margin:4px 0;
	cursor:pointer;
}
#toc ul{
	margin-bottom:0;
}
#toc li{
	margin:2px 0;
}
#toc small {
	float:right;
}";
	
	$toc_fields = array(
		'toc_depth'		=> array('title'=>'显示到第几级',	'type'=>'select',	'value'=>'6',		'options'=>array('1'=>'h1','2'=>'h2','3'=>'h3','4'=>'h4','5'=>'h5','6'=>'h6')),
    	'toc_individual'=> array('title'=>'目录单独设置',	'type'=>'checkbox',	'value'=>'1',		'description'=>'在每篇文章编辑页面单独设置是否显示文章目录以及显示到第几级。'),
		'toc_auto'		=> array('title'=>'脚本自动插入',	'type'=>'checkbox', 'value'=>'1',		'description'=>'自动插入文章目录的 JavaScript 和 CSS 代码。'),
		'toc_script'	=> array('title'=>'JS代码',		'type'=>'textarea',	'value'=>$script,	'description'=>'如果你没有选择自动插入脚本，可以将下面的 JavaScript 代码复制你主题的 JavaScript 文件中。'),
		'toc_css'		=> array('title'=>'CSS代码',		'type'=>'textarea',	'value'=>$css,		'description'=>'根据你的主题对下面的 CSS 代码做适当的修改。<br />如果你没有选择自动插入脚本，可以将下面的 CSS 代码复制你主题的 CSS 文件中。'),
    	'toc_copyright'	=> array('title'=>'版权信息',		'type'=>'checkbox', 'value'=>'1',		'description'=>'在文章目录下面显示版权信息。')
	);


	$sections	= [
		'wpjam-toc'	=> [
			'title'		=>'', 
			'fields'	=>$toc_fields, 
		]
	];

	return compact('sections');
});