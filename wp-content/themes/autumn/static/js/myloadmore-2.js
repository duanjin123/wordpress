//AJAX翻页
jQuery(function($){
	var canBeLoaded = true, // 此PARAM只允许在必要时启动Ajax调用
	    bottomOffset = 2000; // 当你想加载更多的文章时，页面底部的距离（px）
 
	$(window).scroll(function(){
		var data = {
			'action': 'loadmore',
			'query': misha_loadmore_params.posts,
			'page' : misha_loadmore_params.current_page
		};
		if( $(document).scrollTop() > ( $(document).height() - bottomOffset ) && canBeLoaded == true ){
			$.ajax({
				url : misha_loadmore_params.ajaxurl,
				data:data,
				type:'POST',
				beforeSend: function( xhr ){
					// 您也可以在这里添加自己的预加载程序
					// Ajax调用正在进行中，我们不应该再运行它，直到完成
					canBeLoaded = false; 
					
				},
				success:function(data){
					if( data ) {
						$('.posts-wrapper').find('article:last-of-type').after( data ); // 在哪个位置插入文章
						canBeLoaded = true; // AJAX已经完成，现在我们可以再次运行它了
						misha_loadmore_params.current_page++;
					}
				}
			});
		}
	});
});