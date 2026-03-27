// Steel Store - Admin JavaScript
(function($) {
    'use strict';

    $(document).ready(function() {
        // Media uploader for product images
        let mediaUploader;

        $('.steel-upload-image-button').on('click', function(e) {
            e.preventDefault();

            // If the uploader object has already been created, reopen the dialog
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Extend the wp.media object
            mediaUploader = wp.media({
                title: 'Choose Product Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // When an image is selected, run a callback
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                $('#steel_product_image_id').val(attachment.id);
                $('.steel-product-image-preview').html('<img src="' + attachment.url + '" style="max-width: 100%; height: auto;">');
                $('.steel-upload-image-button').text('Change Image');
                
                // Show remove button if not already visible
                if ($('.steel-remove-image-button').length === 0) {
                    $('.steel-upload-image-button').after('<button type="button" class="button steel-remove-image-button" style="margin-left: 5px;">Remove</button>');
                    bindRemoveButton();
                }
            });

            // Open the uploader dialog
            mediaUploader.open();
        });

        // Remove image
        function bindRemoveButton() {
            $('.steel-remove-image-button').on('click', function(e) {
                e.preventDefault();
                
                $('#steel_product_image_id').val('');
                $('.steel-product-image-preview').html('<p style="color: #666;">No image selected</p>');
                $('.steel-upload-image-button').text('Upload Image');
                $(this).remove();
            });
        }

        // Bind remove button on page load if image exists
        bindRemoveButton();
    });

})(jQuery);
