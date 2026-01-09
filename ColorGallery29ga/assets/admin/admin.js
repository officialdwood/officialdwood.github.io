/**
 * Color Gallery 29ga - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Live preview for color value input
        $('#cg29ga_color_value').on('input', function() {
            $('.cg29ga-color-preview').css('background-color', $(this).val());
        });
    });
    
})(jQuery);
