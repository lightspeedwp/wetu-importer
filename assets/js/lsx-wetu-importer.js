var WETU_IMPORTER = {
	data_table: false,
	status_search: false,

	init : function() {

		if (jQuery('body').hasClass('tour-operator_page_lsx-wetu-importer')) {
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
			//this.watchSelectAll();
		}
	},
	myAccommodationSearch: function() {
		jQuery('.subsubsub li a').on( 'click', function(event) {
			event.preventDefault();

			jQuery('.subsubsub li a.current').removeClass('current');
			jQuery(this).addClass('current');

			var keyword = '';
			if ( jQuery(this).parent().hasClass('publish' ) ) {
                keyword = 'publish';
			} else if ( jQuery(this).parent().hasClass('pending' ) ) {
                keyword = 'pending';
            } else if ( jQuery(this).parent().hasClass('draft' ) ) {
                keyword = 'draft';
            } else if ( jQuery(this).parent().hasClass('import' ) ) {
                keyword = 'import';
			}
			WETU_IMPORTER.status_search = true;
            jQuery('#lsx-wetu-importer-search-form').find('input.keyword').val(keyword);
			jQuery('#lsx-wetu-importer-search-form').submit();
			jQuery('#lsx-wetu-importer-search-form').find('input.keyword').val('');
		});
	},
	watchSearch: function() {

		jQuery('#lsx-wetu-importer-search-form input.submit').on( 'click', function(event) {
			jQuery('.subsubsub li a.current').removeClass('current');
			jQuery('.subsubsub li.searchform a').addClass('current');
		});

		jQuery('#lsx-wetu-importer-search-form').on( 'submit', function(event) {
			event.preventDefault();
			var $this = this;
			var order = [[ 1, "asc" ]];

			jQuery('.subsubsub li a.current').removeClass('current');
			jQuery('.subsubsub li.searchform a').addClass('current');

			console.log( WETU_IMPORTER.data_table );
			if ( false !== WETU_IMPORTER.data_table ) {
				console.log('preserve_import3');
				var preserve_import = jQuery('#import-list .wp-list-table').html();
				WETU_IMPORTER.data_table.destroy();
				jQuery('#import-list .wp-list-table').html(preserve_import);
			}

			if ( false !== WETU_IMPORTER.status_search ) {
				order = [[ 2, "asc" ]];
			}

			var column_count = jQuery('#posts-filter thead th').length;
			jQuery('#posts-filter tbody').html('<tr><td style="text-align:center;" colspan="' + ( column_count - 1 ) + '">'+jQuery('#lsx-wetu-importer-search-form .ajax-loader').html()+'</td></tr>');

			var type = jQuery('#lsx-wetu-importer-search-form').attr('data-type');
			var keywords = [];

			if ('' != jQuery('#lsx-wetu-importer-search-form input.keyword').val()) {
				keywords.push(jQuery('#lsx-wetu-importer-search-form input.keyword').val());
			}

			if (undefined != jQuery('#lsx-wetu-importer-search-form .advanced-search textarea').val()) {
				var bulk_keywords = jQuery('#lsx-wetu-importer-search-form .advanced-search textarea').val().split('\n');
				var arrayLength = bulk_keywords.length;
				for (var i = 0; i < arrayLength; i++) {
				    keywords.push(bulk_keywords[i]);
				}
			}

			jQuery.post(
				lsx_tour_importer_params.ajax_url,
				{
					'action' 	: 			'lsx_tour_importer',
					'type'		: 			type,
					'keyword' 	: 			keywords,
					'security'  :			lsx_tour_importer_params.ajax_nonce
				},
				function(response) {
					jQuery('#posts-filter tbody').html(response);
				}).done(function(){
					WETU_IMPORTER.data_table = jQuery('#posts-filter .wp-list-table').DataTable({
						searching: false,
						dom: '<"top"ip<"clear">>rt<"bottom"lp<"clear">>',
						order: order,
						columnDefs: [
							{
								"targets": 0,
								"orderable": false,
							},
							{
								"targets": 1,
								"visible": false,
								"searchable": false
							}
						],
						destroy: true
					});
					WETU_IMPORTER.status_search = false;
			});

			return false;
		});
	},
	watchAdvancedSearch: function() {
		jQuery('#lsx-wetu-importer-search-form .advanced-search-toggle').on( 'click', function(event) {
			event.preventDefault();
			if (jQuery('#lsx-wetu-importer-search-form .advanced-search').hasClass('hidden')) {
				jQuery('#lsx-wetu-importer-search-form .advanced-search').fadeIn('fast').css( 'display', 'inline-block' ).removeClass('hidden');
				jQuery('#lsx-wetu-importer-search-form .normal-search').hide('fast');
				jQuery('#lsx-wetu-importer-search-form .normal-search input.keyword').val('');
				jQuery( this ).html('Simple Search');

			} else {
				jQuery('#lsx-wetu-importer-search-form .advanced-search').fadeOut('fast').addClass('hidden');
				jQuery('#lsx-wetu-importer-search-form .advanced-search textarea').val('');
				jQuery('#lsx-wetu-importer-search-form .normal-search').fadeIn('fast');
				jQuery( this ).html('Bulk Search');
			}
		});
	},
	watchClearButton: function() {
		jQuery('#posts-filter input.button.clear').on('click',function(event){
			event.preventDefault();
			jQuery('#posts-filter tbody').html('');
			jQuery('#lsx-wetu-importer-search-form input[type="text"]').val('');
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
				//jQuery(this).parent().parent().fadeIn('fast');
			});
		});
	},

	importRow: function(args,row) {
		var $this = this;
		var $row = row;

		console.log(args);

        $row.find('td.post-title strong').css('color','#555');

	    jQuery.ajax( {
	        url : lsx_tour_importer_params.ajax_url,
	        data : args,
			method : 'POST',
	    } )
        .always( function( data, textStatus, response ) {
            $this.importNext();
        } )
        .done( function( data ) {
            if ('none' == jQuery('.completed-list-wrapper').css('display')) {
                jQuery('.completed-list-wrapper').fadeIn('fast');
            }
            jQuery('.completed-list-wrapper ul').append(data);
            $row.fadeOut('fast',
                function(here){
                    jQuery(this).fadeOut('fast').remove();
				});
				console.log('done');

        } )
        .fail( function( reason ) {
			// Handles errors only
			console.log($row.find('.check-column'));
			$row.find('.post-title').css('color','red');
			$row.find('.check-column input').attr('checked','');
			$row.find('.check-column input').removeClass('importing');
			$row.find('.check-column input').removeClass('queued');
			$row.find('.check-column input').show();
			$row.find('.check-column img').remove();
        } );
	},

	importNext: function() {
		var checkbox = 	jQuery('#import-list tr input.queued:checked:not(.importing):first');

		if (1 == checkbox.length) {
			checkbox.addClass('importing');

			var post_type = jQuery('.post_type').val();
			var array_import = [];
			var type = jQuery('#lsx-wetu-importer-search-form').attr('data-type');

			var team_members = [];

			if ('undefined' != jQuery('#import-list input.team').length) {
				jQuery('#import-list input.team').each(function(){
					if ( true === jQuery(this).prop('checked') ) {
						team_members.push(jQuery(this).val());
					}
				});
			}
			var content = [];
			if ('undefined' != jQuery('#import-list input.content').length) {
				jQuery('#import-list input.content').each(function(){
					if ( true === jQuery(this).prop('checked') ) {
						content.push(jQuery(this).val());
					}
				});
			}
			var safari_brands = [];
			if ('undefined' != jQuery('#import-list input.accommodation-brand').length) {
				jQuery('#import-list input.accommodation-brand').each(function(){
					if ( true === jQuery(this).prop('checked') ) {
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
				'content'	: 			content,
				'security'  :			lsx_tour_importer_params.ajax_nonce,
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
				jQuery(this).parents('tr').find('.check-column').append(jQuery('#lsx-wetu-importer-search-form .ajax-loader-small').html());
				false_click = false;
			});

			if (true != false_click) {
				$this.importNext();
			} else {
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
					'security'  :			lsx_tour_importer_params.ajax_nonce
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
					'security'  :			lsx_tour_importer_params.ajax_nonce
		        },
		        function(response) {
					if ('none' == jQuery('.completed-list-wrapper').css('display')) {
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
        jQuery('.settings-all input').on('click', function( event ){
			jQuery( this ).trigger('change');
        });
    },
    watchTourUpdate: function() {
        jQuery('.wetu-status.tour-wetu-status h3 a').on('click',function(event){
			event.preventDefault();
			jQuery('.wetu-status.tour-wetu-status').find('form').submit();
        });
	},
}
jQuery(document).ready( function() {
	WETU_IMPORTER.init();

	jQuery("#doc-tooltip").click(function(){
		jQuery(".tooltip-inner").addClass("clicked");
	});
});
