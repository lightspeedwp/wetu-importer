var WETU_IMPORTER = {

	init : function() {
		if(jQuery('body').hasClass('tour-operator_page_wetu-importer')){
			this.myAccommodationSearch();
			this.watchSearch();
			this.watchAdvancedSearch();
			this.watchImportButton();
			this.watchAddToListButton();
			this.watchClearButton();
			this.watchBannerButton();
			this.watchConnectButton();
            this.watchCheckBoxes();
            this.watchTourUpdate();
		}
	},
	myAccommodationSearch: function() {
		jQuery('#wetu-importer-search-form').on( 'click', '.search-toggle', function(event) {
			event.preventDefault();

			var keyword = '';
			if(jQuery(this).hasClass('published')){
                keyword = 'publish';
			}else if(jQuery(this).hasClass('pending')){
                keyword = 'pending';
            }else if(jQuery(this).hasClass('draft')){
                keyword = 'draft';
            }else if(jQuery(this).hasClass('import')){
                keyword = 'import';
            }
            jQuery(this).parents('#wetu-importer-search-form').find('input.keyword').val(keyword);
			jQuery('#wetu-importer-search-form').submit();
            jQuery(this).parents('#wetu-importer-search-form').find('input.keyword').val('');
		});
	},		
	watchSearch: function() {
		jQuery('#wetu-importer-search-form').on( 'submit', function(event) {
			event.preventDefault();

			jQuery('#posts-filter tbody').html('<tr><td style="text-align:center;" colspan="4">'+jQuery('#wetu-importer-search-form .ajax-loader').html()+'</td></tr>');

			var type = jQuery('#wetu-importer-search-form').attr('data-type');
			var keywords = [];

			if('' != jQuery('#wetu-importer-search-form input.keyword').val()){
				keywords.push(jQuery('#wetu-importer-search-form input.keyword').val());
			}

			if(undefined != jQuery('#wetu-importer-search-form .advanced-search textarea').val()){
				var bulk_keywords = jQuery('#wetu-importer-search-form .advanced-search textarea').val().split('\n');
				var arrayLength = bulk_keywords.length;
				for (var i = 0; i < arrayLength; i++) {
				    keywords.push(bulk_keywords[i]);
				}				
			}		

			console.log(lsx_tour_importer_params.ajax_url);

			jQuery.post(lsx_tour_importer_params.ajax_url,
	        {
	            'action' 	: 			'lsx_tour_importer',
	            'type'		: 			type,
	            'keyword' 	: 			keywords
	        },
	        function(response) {
	        	jQuery('#posts-filter tbody').html(response);
	        });
			return false;
		});	
	},
	watchAdvancedSearch: function() {
		jQuery('#wetu-importer-search-form .advanced-search-toggle').on( 'click', function(event) {
			event.preventDefault();
			if(jQuery('#wetu-importer-search-form .advanced-search').hasClass('hidden')){
				jQuery('#wetu-importer-search-form .advanced-search').fadeIn('fast').removeClass('hidden');
				jQuery('#wetu-importer-search-form .normal-search').hide('fast');
				jQuery('#wetu-importer-search-form .normal-search input.keyword').val('');

			}else{
				jQuery('#wetu-importer-search-form .advanced-search').fadeOut('fast').addClass('hidden');
				jQuery('#wetu-importer-search-form .advanced-search textarea').val('');
				jQuery('#wetu-importer-search-form .normal-search').fadeIn('fast');

			}
		});	
	},	
	watchClearButton: function() {
		jQuery('#posts-filter input.button.clear').on('click',function(event){
			event.preventDefault();
			jQuery('#posts-filter tbody').html('');	
			jQuery('#wetu-importer-search-form input[type="text"]').val('');
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

	importRow: function(args,row) {
		var $this = this;
		var $row = row;

        $row.find('td.post-title strong').css('color','#555');

	    jQuery.ajax( {
	        url : lsx_tour_importer_params.ajax_url,
	        data : args,
	        method : 'POST'
	    } )
        .always( function( data, textStatus, response ) {
            $this.importNext();
        } )	    
        .done( function( data ) {
            if('none' == jQuery('.completed-list-wrapper').css('display')){
                jQuery('.completed-list-wrapper').fadeIn('fast');
            }
            jQuery('.completed-list-wrapper ul').append(data);
            $row.fadeOut('fast',
                function(here){
                    jQuery(this).fadeOut('fast').remove();
                });

        } )
        .fail( function( reason ) {
            // Handles errors only
            $row.find('td.post-title strong').css('color','red');
        } );
	},	

	importNext: function() {
		var checkbox = 	jQuery('#import-list tr input.queued:checked:not(.importing):first');

		if(1 == checkbox.length){
			checkbox.addClass('importing');

			var post_type = jQuery('.post_type').val();
			var array_import = [];
			var type = jQuery('#wetu-importer-search-form').attr('data-type');

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
			var safari_brands = [];
			if('undefined' != jQuery('#import-list input.accommodation-brand').length){
				jQuery('#import-list input.accommodation-brand').each(function(){
					if(jQuery(this).attr('checked')){
						safari_brands.push(jQuery(this).val());
					}
				});
			}	
			
			var wetu_id = checkbox.attr('data-identifier');
			var post_id = checkbox.val();
			var row = checkbox.parents('tr');
			var data = {
	            'action' 	: 			'lsx_import_items',
	            'type'		: 			type,
	            'wetu_id' 	: 			wetu_id,
	            'post_id'	:			post_id,
	            'team_members' : 		team_members,
	            'safari_brands' : 		safari_brands,
	            'content'	: 			content
	        };	
			this.importRow(data,row);
		}
	},
	watchImportButton: function() {
		var $this = this;

		jQuery('#import-list input[type="submit"]').on('click',function(event){

			event.preventDefault();
			counter = 0;
			var false_click = true;

			jQuery('#import-list tr input:checked:not(.queued)').each(function(){
				jQuery(this).hide().addClass('queued');
				jQuery(this).parents('tr').find('.check-column').append(jQuery('#wetu-importer-search-form .ajax-loader-small').html());
				false_click = false;
			});

			if(true != false_click){
				$this.importNext();
			}else{
				alert('Make sure you have some items selected.');
			}
		});
	},
	watchBannerButton: function() {
		jQuery('#banners-filter input.button.download').on('click',function(event){

			event.preventDefault();
			jQuery('#banners-filter tbody tr input:checked').each(function(){
				var post_id = jQuery(this).val();
				var current_row = jQuery(this).parents('tr');

				jQuery(this).hide();
				jQuery(this).parents('tr').find('.check-column').append(jQuery('#banners-filter .ajax-loader-small').html());

				jQuery.post(lsx_tour_importer_params.ajax_url,
		        {
		            'action' 	: 			'lsx_import_sync_banners',
		            'post_id'	:			post_id,
		        },
		        function(response) {
		        	current_row.fadeOut('fast', 
		        	function(here){ 
			            jQuery(this).fadeOut('fast').remove();
			        });
		        });				
			});	
		});
	},
	watchConnectButton: function() {
		jQuery('#connect-accommodation-filter input.button.connect').on('click',function(event){

			event.preventDefault();
			jQuery('#connect-accommodation-filter tbody tr input:checked').each(function(){
				var post_id = jQuery(this).val();
				var type = 'connect_accommodation';
				var wetu_id = jQuery(this).attr('data-identifier');

				var current_row = jQuery(this).parents('tr');

				jQuery(this).hide();
				jQuery(this).parents('tr').find('.check-column').append(jQuery('#connect-accommodation-filter .ajax-loader-small').html());

				jQuery.post(lsx_tour_importer_params.ajax_url,
		        {
		            'action' 	: 			'lsx_import_connect_accommodation',
		            'post_id'	:			post_id,
		            'type'		:			type,
		            'wetu_id'	:			wetu_id,
		        },
		        function(response) {
					if('none' == jQuery('.completed-list-wrapper').css('display')){
						jQuery('.completed-list-wrapper').fadeIn('fast');
					}
					jQuery('.completed-list-wrapper ul').append(response);		        	
		        	current_row.fadeOut('fast', 
		        	function(here){ 
			            jQuery(this).fadeOut('fast').remove();
			        });
		        });				
			});	
		});
	},
    watchCheckBoxes: function() {
        jQuery('form#import-list .settings-all input[type="checkbox"]').on('change',function(event){
            var thisOBJ = jQuery(this);
            if('all' === thisOBJ.val()){
                thisOBJ.parents('form').find('.settings-all input[type="checkbox"]:not([value="all"])').each(function(){

                	if('checked' === thisOBJ.attr('checked')) {
                        jQuery(this).attr('checked', 'checked');
                    }else{
                        jQuery(this).removeAttr('checked');
					}
                });
			}else{

			}


        });

        var lastChecked = null;

        jQuery('#the-list input[type="checkbox"], #import-list tbody input[type="checkbox"]').on('click',function(event){
            if(!lastChecked) {
                lastChecked = this;
                return;
            }

            if(e.shiftKey) {
                var start = $chkboxes.index(this);
                var end = $chkboxes.index(lastChecked);

                $chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked);

            }
            lastChecked = this;
        });
    },

    watchTourUpdate: function() {
        jQuery('.wetu-status.tour-wetu-status h3 a').on('click',function(event){
			event.preventDefault();
			jQuery(this).parents('.wetu-status.tour-wetu-status').find('form').submit();
        });

    }
}
jQuery(document).ready( function() {
	WETU_IMPORTER.init();
});