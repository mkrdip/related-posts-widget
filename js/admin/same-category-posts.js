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
		
    }
