/**
 * Color Gallery 29ga - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    var currentColorIndex = 0;
    var $currentGallery = null;
    
    $(document).ready(function() {
        initColorGallery();
    });
    
    function initColorGallery() {
        // Handle tile clicks to open modal
        $(document).on('click', '.cg29ga-tile', function() {
            var $tile = $(this);
            $currentGallery = $tile.closest('.cg29ga-gallery');
            
            // Get all visible tiles in THIS gallery only
            var $visibleTiles = $currentGallery.find('.cg29ga-tile:visible');
            currentColorIndex = $visibleTiles.index($tile);
            
            openModalWithColor($tile);
        });
        
        // Close modal on X click
        $(document).on('click', '.cg29ga-close', function(e) {
            e.stopPropagation();
            closeModal();
        });
        
        // Close modal on outside click
        $(document).on('click', '#cg29ga-modal', function(e) {
            if ($(e.target).is('#cg29ga-modal')) {
                closeModal();
            }
        });
        
        // Navigation arrows
        $(document).on('click', '.cg29ga-nav-arrow.prev', function(e) {
            e.stopPropagation();
            navigateColor(-1);
        });
        
        $(document).on('click', '.cg29ga-nav-arrow.next', function(e) {
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
        if (!$currentGallery) return;
        
        var $visibleTiles = $currentGallery.find('.cg29ga-tile:visible');
        if ($visibleTiles.length === 0) return;
        
        currentColorIndex += direction;
        
        // Wrap around
        if (currentColorIndex < 0) {
            currentColorIndex = $visibleTiles.length - 1;
        } else if (currentColorIndex >= $visibleTiles.length) {
            currentColorIndex = 0;
        }
        
        var $tile = $visibleTiles.eq(currentColorIndex);
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
        $currentGallery = null;
    }
    
    // Initialize "See More" functionality
    function initSeeMore() {
        $(document).on('click', '.cg29ga-see-more-btn', function() {
            var $gallery = $(this).closest('.cg29ga-gallery');
            var $hiddenTiles = $gallery.find('.cg29ga-tile.cg29ga-hidden');
            
            if ($(this).hasClass('expanded')) {
                // Collapse - hide tiles again
                $hiddenTiles.hide().removeClass('cg29ga-hidden');
                $hiddenTiles.addClass('cg29ga-hidden');
                $(this).removeClass('expanded');
                $(this).html('See More <span class="cg29ga-arrow">↓</span>');
            } else {
                // Expand - show all tiles
                $hiddenTiles.show();
                $(this).addClass('expanded');
                $(this).html('See Less <span class="cg29ga-arrow">↑</span>');
            }
        });
    }
    
    $(document).ready(function() {
        initColorGallery();
        initSeeMore();
    });
    
})(jQuery);
