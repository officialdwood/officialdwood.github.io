jQuery(document).ready(function($) {
    var mediaUploader;
    
    // Handle upload button clicks
    $('.csa-upload-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInputId = button.data('target');
        var previewId = button.data('preview');
        
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Extend the wp.media object
        mediaUploader = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        
        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#' + targetInputId).val(attachment.url);
            
            // Update preview
            var preview = $('#' + previewId);
            preview.find('img').attr('src', attachment.url);
            preview.show();
        });
        
        // Open the uploader dialog
        mediaUploader.open();
    });
    
    // Handle remove button clicks
    $('.csa-remove-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInputId = button.data('target');
        var previewId = button.data('preview');
        
        // Clear the input field
        $('#' + targetInputId).val('');
        
        // Hide preview
        var preview = $('#' + previewId);
        preview.hide();
        preview.find('img').attr('src', '');
    });
});
