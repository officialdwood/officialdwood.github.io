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
        // Cache all tiles in the gallery
        $allTiles = $('.cg29ga-tile').toArray();
        
        // Handle tile clicks to open modal
        $('.cg29ga-tile').on('click', function() {
            var $tile = $(this);
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
        
        // Get the tile's size for 250% scaling
        var tileWidth = $chip.width();
        var scaledSize = tileWidth * 2.5;
        
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
        
        // Set size to 250% of original tile
        $modalChip.css({
            'width': scaledSize + 'px',
            'height': scaledSize + 'px',
            'padding-bottom': '0'
        });
        
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
        
        // Get the tile's size for 250% scaling
        var tileWidth = $chip.width();
        var scaledSize = tileWidth * 2.5;
        
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
        
        // Set size to 250% of original tile
        $modalChip.css({
            'width': scaledSize + 'px',
            'height': scaledSize + 'px',
            'padding-bottom': '0'
        });
        
        $modalName.text(colorName);
    }
    
    function closeModal() {
        $('#cg29ga-modal').removeClass('active');
        $('body').css('overflow', '');
    }
    
})(jQuery);
