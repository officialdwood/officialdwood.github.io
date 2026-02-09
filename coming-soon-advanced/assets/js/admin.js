jQuery(document).ready(function($) {
    // Initialize WordPress color pickers
    $('.csa-color-picker').wpColorPicker();
    
    // Handle upload button clicks - create separate uploader for each button
    $('.csa-upload-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInputId = button.data('target');
        var previewId = button.data('preview');
        
        // Create a new media uploader instance for this specific button
        var mediaUploader = wp.media({
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
    
    // Handle video upload button clicks
    $('.csa-upload-video-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInputId = button.data('target');
        var previewId = button.data('preview');
        
        // Create a new media uploader instance for video
        var mediaUploader = wp.media({
            title: 'Choose Video',
            button: {
                text: 'Choose Video'
            },
            library: {
                type: 'video'
            },
            multiple: false
        });
        
        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#' + targetInputId).val(attachment.url);
            
            // Update preview
            var preview = $('#' + previewId);
            preview.find('video').attr('src', attachment.url);
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
        preview.find('video').attr('src', '');
    });
});
