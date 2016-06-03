var LSX_TOUR_IMPORTER = {

	init : function() {
		if(jQuery('body').hasClass('tools_page_lsx-tour-importer')){

			this.watchTypeDropdown();
		}
	},
	
	watchTypeDropdown: function() {
		
		jQuery('select').on( 'change', function() {
			console.log(jQuery(this).val());
			if('accommodation' == jQuery(this).val()){
				jQuery('.tour-options' ).hide();
			}else{
				jQuery('.tour-options' ).show();
			}
		});	
	},	
}

jQuery(document).ready( function() {
	LSX_TOUR_IMPORTER.init();
});