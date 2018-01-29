function viewMode(mode) {
	jQuery('#vm-products-category .view-mode').find('a').removeClass('active');
	
	if(mode == 'list') {
		jQuery('#vm-products-category').addClass('vm_list_view');		
		jQuery('#vm-products-category').find('a.mode-list').addClass('active');
	} else {
		jQuery('#vm-products-category').removeClass('vm_list_view');		
		jQuery('#vm-products-category').find('a.mode-grid').addClass('active');
	}
	
	jQuery.cookie('listing', mode);
}

jQuery(document).ready(function($){	
	
	//uniform;
	$('.display-number select').uniform();

	/* Goto Top */		
	$(window).scroll(function(event) {	
		if ($(this).scrollTop() > 300) {
			$('.sp-totop').fadeIn();
			$('.sp-totop').css({"visibility": "visible"});
		} else {
			$('.sp-totop').fadeOut();
		}
	});
	
	$('.sp-totop').on('click', function() {
        $('html, body').animate({
            scrollTop: $("body").offset().top
        }, 500);
    });
	
});