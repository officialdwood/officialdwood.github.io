/**
 * Color Gallery 29ga - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    var currentColorIndex = 0;
    var $allTiles = [];
    
    $(document).ready(function() {
        initColorGallery();
    });
    
    function initColorGallery() {
        // Cache all visible tiles in the gallery (excluding hidden ones from pagination)
        $allTiles = $('.cg29ga-tile:visible').toArray();
        
        // Handle tile clicks to open modal
        $('.cg29ga-tile').on('click', function() {
            var $tile = $(this);
            // Recalculate index based on visible tiles
            $allTiles = $('.cg29ga-tile:visible').toArray();
            currentColorIndex = $allTiles.indexOf(this);
            openModalWithColor($tile);
        });
        
        // Close modal on X click
        $('.cg29ga-close').on('click', function(e) {
            e.stopPropagation();
            closeModal();
        });
        
        // Close modal on outside click
        $('#cg29ga-modal').on('click', function(e) {
            if ($(e.target).is('#cg29ga-modal')) {
                closeModal();
            }
        });
        
        // Navigation arrows
        $('.cg29ga-nav-arrow.prev').on('click', function(e) {
            e.stopPropagation();
            navigateColor(-1);
        });
        
        $('.cg29ga-nav-arrow.next').on('click', function(e) {
            e.stopPropagation();
            navigateColor(1);
        });
        
        // Keyboard navigation
        $(document).on('keydown', function(e) {
            if ($('#cg29ga-modal').hasClass('active')) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    closeModal();
                } else if (e.key === 'ArrowLeft' || e.keyCode === 37) {
                    navigateColor(-1);
                } else if (e.key === 'ArrowRight' || e.keyCode === 39) {
                    navigateColor(1);
                }
            }
        });
    }
    
    function openModalWithColor($tile) {
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
    }
    
    function navigateColor(direction) {
        if ($allTiles.length === 0) return;
        
        currentColorIndex += direction;
        
        // Wrap around
        if (currentColorIndex < 0) {
            currentColorIndex = $allTiles.length - 1;
        } else if (currentColorIndex >= $allTiles.length) {
            currentColorIndex = 0;
        }
        
        var $tile = $($allTiles[currentColorIndex]);
        var $chip = $tile.find('.cg29ga-chip');
        var colorName = $tile.find('.cg29ga-name').text();
        
        // Get background style (color or image)
        var bgColor = $chip.css('background-color');
        var bgImage = $chip.css('background-image');
        
        var $modal = $('#cg29ga-modal');
        var $modalChip = $modal.find('.cg29ga-modal-chip');
        var $modalName = $modal.find('.cg29ga-modal-name');
        
        // Update modal content
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
    }
    
    function closeModal() {
        $('#cg29ga-modal').removeClass('active');
        $('body').css('overflow', '');
    }
    
    // Initialize "See More" functionality
    function initSeeMore() {
        $('.cg29ga-see-more-btn').each(function() {
            $(this).on('click', function() {
                var $gallery = $(this).closest('.cg29ga-gallery');
                var $hiddenTiles = $gallery.find('.cg29ga-tile.cg29ga-hidden');
                
                if ($(this).hasClass('expanded')) {
                    // Collapse - hide tiles again
                    $hiddenTiles.hide();
                    $(this).removeClass('expanded');
                    $(this).html('See More <span class="cg29ga-arrow">↓</span>');
                } else {
                    // Expand - show all tiles
                    $hiddenTiles.show();
                    $(this).addClass('expanded');
                    $(this).html('See Less <span class="cg29ga-arrow">↑</span>');
                    
                    // Update the tiles array to include newly visible tiles
                    $allTiles = $('.cg29ga-tile:visible').toArray();
                }
            });
        });
    }
    
    $(document).ready(function() {
        initColorGallery();
        initSeeMore();
    });
    
})(jQuery);
