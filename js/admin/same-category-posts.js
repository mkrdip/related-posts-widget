/**
 * Same Category Posts Widget
 * https://github.com/DanielFloeter/same-category-posts
 *
 * Adds a widget that shows posts from the same category to the current post. 
 *
 * Released under the GPLv2 license or later -  http://www.gnu.org/licenses/gpl-2.0.html
 */

    // namespace
    var scpwp_namespace = {

        open_panels : {},  // holds an array of open panels per wiget id

        // generic click handler on the panel title
        clickHandler: function(element) {
            // open the div "below" the h4 title
            jQuery(element).toggleClass('open')
                        .next().stop().slideToggle();
            // mark the change of state in the open panels array
            var panel = jQuery(element).data('panel');
            var id = jQuery(element).parent().parent().parent().parent().parent().attr('id');
            var o = {};
            if (this.open_panels.hasOwnProperty(id))
                o = this.open_panels[id];
            if (o.hasOwnProperty(panel))
                delete o[panel];
            else 
                o[panel] = true;
            this.open_panels[id] = o;
        },
		
        // Deactivate other terms on same taxonomy change
        toggleSelectTaxPanel: function(item) {
            var value = jQuery(item).prop("checked"),
				tax = jQuery(item).data("taxname-and-post-type").split('-'),
				taxname = tax[0],
				postType = tax[1];
            jQuery('[data-post-type='+postType+']').hide();
			jQuery('.scpwp-exclude-taxterms-'+taxname+'-panel').show();
        },
        
        // Show hide number of categories options on separate categories change
        toggleSeparateCategoriesPanel: function(item) {
            var value = jQuery(item).prop("checked");			
            if(value == true) {
                jQuery('.scpwp-separate-categories-panel').show();
            }
            else {
                jQuery('.scpwp-separate-categories-panel').hide();
            }
        },

        // Show hide length and more-text options on show post excerpt change
        toggleShowPostExcerptPanel: function(item) {
            var value = jQuery(item).prop("checked");		
            if(value == true) {
                jQuery('.scpwp-show-post-excerpt-panel').show();
            }
            else {
                jQuery('.scpwp-show-post-excerpt-panel').hide();
            }
        },
        
        // Show hide width, height and crop options on show thumbnail change
        toggleShowPostThumbnailPanel: function(item) {
            var value = jQuery(item).prop("checked");		
            if(value == true) {
                jQuery('.scpwp-show-post-thumbnail-panel').show();
            }
            else {
                jQuery('.scpwp-show-post-thumbnail-panel').hide();
            }
        },
		
        // Show hide width, height and crop options on show thumbnail change
        toggleDatePanel: function(item) {
            var value = jQuery(item).find("input").prop("checked");		
            if(value == true) {
                jQuery('.scpwp-data-panel-date').show();
            }
            else {
                jQuery('.scpwp-data-panel-date').hide();
            }
        },
		
        // Show hide width, height and crop options on show thumbnail change
        toggleUseWPDateFormatPanel: function(item) {
            var value = jQuery(item).find("input").prop("checked");		
            if(value == true) {
                jQuery('.scpwp-data-panel-date-format').hide();
            }
            else {
                jQuery('.scpwp-data-panel-date-format').show();
            }
        },
    }

	jQuery(document).ready( function () {

		jQuery('.same-category-widget-cont h4').click(function () { // for widgets page
			// toggle panel open/close
			scpwp_namespace.clickHandler(this);
		});

		// needed to reassign click handlers after widget refresh
		jQuery(document).on('widget-added widget-updated', function(root,element){ // for customize and after save on widgets page
			jQuery('.same-category-widget-cont h4').off('click').on('click', function () {
				// toggle panel open/close
				scpwp_namespace.clickHandler(this);
			});

			// refresh panels to state before the refresh
			var id = jQuery(element).attr('id');
			if (scpwp_namespace.open_panels.hasOwnProperty(id)) {
				var o = scpwp_namespace.open_panels[id];
				for (var panel in o) {
					jQuery(element).find('[data-panel='+panel+']').toggleClass('open')
						.next().stop().show();
				}
			}
		});
	});
