var LSX_TOUR_IMPORTER = {

	init : function() {
		if(jQuery('body').hasClass('tools_page_lsx-tour-importer')){
			//this.watchTypeDropdown();
			this.watchSearch();
		}
	},
	
	watchTypeDropdown: function() {
		
		jQuery('#lsx-tour-importer-search-form select').on( 'change', function() {
			console.log(jQuery(this).val());
			if('accommodation' == jQuery(this).val()){
				jQuery('.tour-options' ).hide();
			}else{
				jQuery('.tour-options' ).show();
			}
		});	
	},	
	watchSearch: function() {
		jQuery('#lsx-tour-importer-search-form').on( 'submit', function(event) {
			event.preventDefault();

			jQuery('#the-list').html(jQuery('#lsx-tour-importer-search-form .ajax-loader').html());

			var type = jQuery('#lsx-tour-importer-search-form').attr('data-type');
			var keyword = jQuery('#lsx-tour-importer-search-form input.keyword').val();
			jQuery.post(lsx_tour_importer_params.ajax_url,
	        {
	            'action' 	: 			'lsx_tour_importer',
	            'type'		: 			type,
	            'keyword' 	: 			keyword
	        },
	        function(response) {
	        	//jQuery('#the-list').html(response);
	        });
			return false;
		});	
	},	
		
}

jQuery(document).ready( function() {
	LSX_TOUR_IMPORTER.init();
});