/**
 * Color Gallery 29ga - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initColorGallery();
    });
    
    function initColorGallery() {
        // Handle tile clicks to open modal
        $('.cg29ga-tile').on('click', function() {
            var $tile = $(this);
            var $chip = $tile.find('.cg29ga-chip');
            var colorName = $tile.find('.cg29ga-name').text();
            
            // Get background style (color or image)
            var bgColor = $chip.css('background-color');
            var bgImage = $chip.css('background-image');
            
            // Open modal
            var $modal = $('#cg29ga-modal');
            var $modalChip = $modal.find('.cg29ga-modal-chip');
            var $modalName = $modal.find('.cg29ga-modal-name');
            
            // Set modal content
            if (bgImage && bgImage !== 'none') {
                $modalChip.css({
                    'background-image': bgImage,
                    'background-color': ''
                });
            } else {
                $modalChip.css({
                    'background-color': bgColor,
                    'background-image': ''
                });
            }
            
            $modalName.text(colorName);
            $modal.addClass('active');
            
            // Prevent body scroll when modal is open
            $('body').css('overflow', 'hidden');
        });
        
        // Close modal on X click
        $('.cg29ga-close').on('click', function() {
            closeModal();
        });
        
        // Close modal on outside click
        $('#cg29ga-modal').on('click', function(e) {
            if ($(e.target).is('#cg29ga-modal')) {
                closeModal();
            }
        });
        
        // Close modal on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                if ($('#cg29ga-modal').hasClass('active')) {
                    closeModal();
                }
            }
        });
        
        function closeModal() {
            $('#cg29ga-modal').removeClass('active');
            $('body').css('overflow', '');
        }
    }
    
})(jQuery);
