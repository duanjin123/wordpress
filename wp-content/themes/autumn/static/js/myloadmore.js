//AJAX翻页
jQuery(function($){
	$('.misha_loadmore').click(function(){
 
		var button = $(this),
		    data = {
			'action': 'loadmore',
			'query': misha_loadmore_params.posts, // 这就是我们从wp_localize_script()函数中获取参数的方法。
			'page' : misha_loadmore_params.current_page
		};
		
		$.ajax({
			url : misha_loadmore_params.ajaxurl, // AJAX处理程序
			data : data,
			type : 'POST',
			beforeSend : function ( xhr ) {
				button.html('<div class="misha_loadmore infinite-scroll-action"><div class="infinite-scroll-button button">加载中...</div></div>'); // 更改按钮文本，还可以添加预加载图像
			},
			success : function( data ){
				if( data ) { 
					button.html( '<div class="misha_loadmore infinite-scroll-action"><div class="infinite-scroll-button button">加载更多</div></div>' ).before(data); // 插入新的文章
					misha_loadmore_params.current_page++;
 
					if ( misha_loadmore_params.current_page == misha_loadmore_params.max_page ) 
						button.remove(); // 如果最后一页，删除按钮
 
					// 如果你使用一个需要它的插件，你也可以在这里触发“后加载”事件。
					// $( document.body ).trigger( 'post-load' );
				} else {
					button.remove(); // 如果没有数据，也删除按钮
				}
			}

		});
	});
}); 