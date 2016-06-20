var LSX_TOUR_IMPORTER = {

	init : function() {
		if(jQuery('body').hasClass('tools_page_lsx-tour-importer')){
			this.watchSearch();
			this.watchAdvancedSearch();
			this.watchImportButton();
			this.watchAddToListButton();
			this.watchClearButton();
		}
	},	
	watchSearch: function() {
		jQuery('#lsx-tour-importer-search-form').on( 'submit', function(event) {
			event.preventDefault();

			jQuery('#posts-filter tbody').html('<tr><td style="text-align:center;" colspan="4">'+jQuery('#lsx-tour-importer-search-form .ajax-loader').html()+'</td></tr>');

			var type = jQuery('#lsx-tour-importer-search-form').attr('data-type');
			var keyword = jQuery('#lsx-tour-importer-search-form input.keyword').val();
			jQuery.post(lsx_tour_importer_params.ajax_url,
	        {
	            'action' 	: 			'lsx_tour_importer',
	            'type'		: 			type,
	            'keyword' 	: 			keyword
	        },
	        function(response) {
	        	jQuery('#posts-filter tbody').html(response);
	        });
			return false;
		});	
	},
	watchAdvancedSearch: function() {
		jQuery('#lsx-tour-importer-search-form .advanced-search-toggle').on( 'click', function(event) {
			event.preventDefault();

			//jQuery('#lsx-tour-importer-search-form .advanced-search').fadeIn('fast');

		});	
	},	
	watchClearButton: function() {
		jQuery('#posts-filter input.button.clear').on('click',function(event){
			event.preventDefault();
			jQuery('#posts-filter tbody').html('');	
			jQuery('#lsx-tour-importer-search-form input[type="text"]').val('');	
		});
	},	
	watchAddToListButton: function() {
		jQuery('#posts-filter input.button.add').on('click',function(event){
			
			event.preventDefault();
			jQuery('.import-list-wrapper').fadeIn('fast');	

			jQuery('#posts-filter tbody tr input:checked').each(function(){
		        jQuery('#import-list tbody').append(jQuery(this).parent().parent());
			});	

			jQuery('#import-list tbody tr input:checked').each(function(){
				jQuery(this).parent().parent().fadeIn('fast');
			});
		});
	},	

	watchImportButton: function() {
		jQuery('#import-list input[type="submit"]').on('click',function(event){
			event.preventDefault();
			var post_type = jQuery('.post_type').val();
			var array_import = [];
			
			counter = 0;
			jQuery('#import-list tr input:checked').each(function(){
				var wetu_id = jQuery(this).attr('data-identifier');
				var post_id = jQuery(this).val();
				var type = jQuery('#lsx-tour-importer-search-form').attr('data-type');

				var team_members = [];
				if('undefined' != jQuery('#import-list input.team').length){
					jQuery('#import-list input.team').each(function(){
						if(jQuery(this).attr('checked')){
							team_members.push(jQuery(this).val());
						}
					});
				}

				var content = [];
				if('undefined' != jQuery('#import-list input.content').length){
					jQuery('#import-list input.content').each(function(){
						if(jQuery(this).attr('checked')){
							content.push(jQuery(this).val());
						}
					});
				}				

				var current_row = jQuery(this);

				jQuery.post(lsx_tour_importer_params.ajax_url,
		        {
		            'action' 	: 			'lsx_import_items',
		            'type'		: 			type,
		            'wetu_id' 	: 			wetu_id,
		            'post_id'	:			post_id,
		            'team_members' : 		team_members,
		            'content'	: 			content
		        },
		        function(response) {
		        	/*current_row.parents('tr').fadeOut('fast', 
		        	function(here){ 
			            jQuery(this).fadeOut('fast').remove();
			        });*/
		        });
			});
		});		
	}
}
jQuery(document).ready( function() {
	LSX_TOUR_IMPORTER.init();
});