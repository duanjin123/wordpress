jQuery(document).ready(function() {

	var current_tab = jQuery('.um-account-main').attr('data-current_tab');
	
	if  ( current_tab ) {
		jQuery('.um-account-tab[data-tab='+current_tab+']').show();
	}

	jQuery(document).on('click','.um-account-side li a',function(e){
		e.preventDefault();
		var link = jQuery(this);
		
		link.parents('ul').find('li a').removeClass('current');
		link.addClass('current');
		
		var url_ = jQuery(this).attr('href');
		var tab_ = jQuery(this).attr('data-tab');
		
		jQuery("input[id=_um_account_tab]:hidden").val( tab_ );
		
		window.history.pushState("", "", url_);
		
		jQuery('.um-account-tab').hide();
		jQuery('.um-account-tab[data-tab='+tab_+']').fadeIn();
		
		jQuery('.um-account-nav a').removeClass('current');
		jQuery('.um-account-nav a[data-tab='+tab_+']').addClass('current');

		return false;
	});
});

	jQuery(document).on('click','.um-account-nav a',function(e){
        e.preventDefault();
        var $this = jQuery(this);
        var tab_ = $this.attr('data-tab');
        var div = $this.parents('div');

        
        jQuery("input[id=_um_account_tab]:hidden").val( tab_ );
        
        //jQuery('.um-account-tab').hide();

        if ( $this.hasClass('current') ) {
            jQuery('.um-account-tab-'+tab_).slideUp('normal');
            $this.removeClass('current');
        } else {
            jQuery('.um-account-tab').slideUp('normal');
            $this.parents('div').find('a').removeClass('current');
            $this.addClass('current');
            jQuery('.um-account-tab-'+tab_).slideDown();
        }

        var $el = jQuery('.um-account-side li a[data-tab='+tab_+']');
        var url_ = $el.attr('href');
        jQuery('.um-account-side li a').removeClass('current');
        $el.addClass('current');
        window.history.pushState("", "", url_);

        return false;
    });
	
