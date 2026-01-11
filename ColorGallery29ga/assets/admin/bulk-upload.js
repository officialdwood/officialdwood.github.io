/**
 * Color Gallery 29ga - Bulk Upload JavaScript
 */

(function($) {
    'use strict';
    
    var selectedImages = [];
    var mediaFrame;
    
    $(document).ready(function() {
        initBulkUpload();
        initDragAndDrop();
    });
    
    function initBulkUpload() {
        // Select images button
        $('#cg29ga_select_images').on('click', function(e) {
            e.preventDefault();
            
            // Create media frame if it doesn't exist
            if (mediaFrame) {
                mediaFrame.open();
                return;
            }
            
            mediaFrame = wp.media({
                title: 'Select Color Images',
                button: {
                    text: 'Add Selected Images'
                },
                multiple: true,
                library: {
                    type: 'image'
                }
            });
            
            // When images are selected
            mediaFrame.on('select', function() {
                var selection = mediaFrame.state().get('selection');
                selectedImages = [];
                
                selection.map(function(attachment) {
                    attachment = attachment.toJSON();
                    selectedImages.push({
                        id: attachment.id,
                        url: attachment.url,
                        filename: attachment.filename,
                        title: attachment.title
                    });
                });
                
                renderSelectedImages();
            });
            
            mediaFrame.open();
        });
        
        // Save button
        $('#cg29ga_bulk_save').on('click', function(e) {
            e.preventDefault();
            bulkSaveColors();
        });
    }
    
    function renderSelectedImages() {
        var container = $('#cg29ga_selected_images');
        container.empty();
        
        if (selectedImages.length === 0) {
            $('#cg29ga_bulk_save').prop('disabled', true);
            return;
        }
        
        container.append('<h2>Selected Images (' + selectedImages.length + ')</h2>');
        container.append('<p class="description">Enter a name for each color. This name will appear below the color chip in your gallery.</p>');
        
        var grid = $('<div class="cg29ga-image-grid"></div>');
        
        $.each(selectedImages, function(index, image) {
            var item = $('<div class="cg29ga-image-item" data-index="' + index + '"></div>');
            var img = $('<img src="' + image.url + '" alt="' + image.filename + '" />');
            var nameInput = $('<input type="text" class="cg29ga-color-name" placeholder="Enter color name" value="' + sanitizeTitle(image.title || image.filename) + '" />');
            var removeBtn = $('<button type="button" class="button cg29ga-remove-image">Remove</button>');
            
            item.append(img);
            item.append(nameInput);
            item.append(removeBtn);
            grid.append(item);
        });
        
        container.append(grid);
        $('#cg29ga_bulk_save').prop('disabled', false);
        
        // Remove button handler
        $('.cg29ga-remove-image').on('click', function() {
            var index = $(this).closest('.cg29ga-image-item').data('index');
            selectedImages.splice(index, 1);
            renderSelectedImages();
        });
    }
    
    function sanitizeTitle(title) {
        // Remove file extension
        title = title.replace(/\.[^/.]+$/, '');
        // Replace underscores and dashes with spaces
        title = title.replace(/[_-]/g, ' ');
        // Capitalize first letter of each word
        title = title.replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        return title;
    }
    
    function bulkSaveColors() {
        var galleryId = $('#cg29ga_bulk_gallery').val();
        
        if (!galleryId) {
            showMessage('error', 'Please select a gallery first.');
            return;
        }
        
        // Get color names from inputs
        var colorsToCreate = [];
        $('.cg29ga-image-item').each(function() {
            var index = $(this).data('index');
            var name = $(this).find('.cg29ga-color-name').val().trim();
            
            if (name) {
                colorsToCreate.push({
                    imageId: selectedImages[index].id,
                    name: name
                });
            }
        });
        
        if (colorsToCreate.length === 0) {
            showMessage('error', 'Please enter names for at least one color.');
            return;
        }
        
        // Disable button and show spinner
        $('#cg29ga_bulk_save').prop('disabled', true);
        $('.spinner').addClass('is-active');
        $('#cg29ga_bulk_messages').empty();
        
        // Create colors one by one
        var completed = 0;
        var errors = [];
        
        function createNextColor() {
            if (completed >= colorsToCreate.length) {
                // All done
                $('.spinner').removeClass('is-active');
                
                if (errors.length === 0) {
                    showMessage('success', 'Successfully created ' + completed + ' color(s)!');
                    // Clear selection
                    selectedImages = [];
                    renderSelectedImages();
                    $('#cg29ga_bulk_gallery').val('');
                } else {
                    showMessage('warning', 'Created ' + (completed - errors.length) + ' color(s). ' + errors.length + ' failed.');
                }
                
                $('#cg29ga_bulk_save').prop('disabled', false);
                return;
            }
            
            var color = colorsToCreate[completed];
            
            $.ajax({
                url: cg29gaBulk.ajax_url,
                type: 'POST',
                data: {
                    action: 'cg29ga_bulk_upload',
                    nonce: cg29gaBulk.nonce,
                    gallery_id: galleryId,
                    image_id: color.imageId,
                    color_name: color.name
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('info', response.data.message);
                    } else {
                        errors.push(color.name);
                        showMessage('error', 'Failed: ' + color.name);
                    }
                },
                error: function() {
                    errors.push(color.name);
                    showMessage('error', 'Failed: ' + color.name);
                },
                complete: function() {
                    completed++;
                    createNextColor();
                }
            });
        }
        
        createNextColor();
    }
    
    function showMessage(type, message) {
        var messageClass = 'notice notice-' + type;
        var messageHtml = '<div class="' + messageClass + '"><p>' + message + '</p></div>';
        $('#cg29ga_bulk_messages').append(messageHtml);
    }
    
    // Drag and Drop functionality
    function initDragAndDrop() {
        var dropZone = $('#cg29ga_drop_zone');
        
        if (dropZone.length === 0) return;
        
        // Prevent default drag behaviors
        $(document).on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
        
        // Highlight drop zone when dragging over it
        dropZone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        dropZone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });
        
        // Handle dropped files
        dropZone.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            
            if (files.length === 0) return;
            
            // Show uploading message
            dropZone.addClass('uploading');
            dropZone.find('.drop-text').text('Uploading ' + files.length + ' file(s)...');
            
            // Upload files to WordPress media library
            uploadFiles(files);
        });
    }
    
    function uploadFiles(files) {
        var formData = new FormData();
        var uploadedCount = 0;
        var totalFiles = files.length;
        var newImages = [];
        
        // Upload each file
        Array.from(files).forEach(function(file, index) {
            // Only process image files
            if (!file.type.match('image.*')) {
                uploadedCount++;
                if (uploadedCount === totalFiles) {
                    finishUpload(newImages);
                }
                return;
            }
            
            var fileFormData = new FormData();
            fileFormData.append('action', 'cg29ga_upload_file');
            fileFormData.append('nonce', cg29gaBulk.nonce);
            fileFormData.append('file', file);
            
            $.ajax({
                url: cg29gaBulk.ajax_url,
                type: 'POST',
                data: fileFormData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success && response.data) {
                        newImages.push({
                            id: response.data.id,
                            url: response.data.url,
                            filename: response.data.filename,
                            title: response.data.title
                        });
                    }
                },
                error: function() {
                    showMessage('error', 'Failed to upload: ' + file.name);
                },
                complete: function() {
                    uploadedCount++;
                    if (uploadedCount === totalFiles) {
                        finishUpload(newImages);
                    }
                }
            });
        });
    }
    
    function finishUpload(newImages) {
        var dropZone = $('#cg29ga_drop_zone');
        dropZone.removeClass('uploading');
        dropZone.find('.drop-text').html('Drag & Drop Images Here<br><span style="font-size: 14px; font-weight: normal;">or click "Choose Images" button above</span>');
        
        if (newImages.length > 0) {
            // Add new images to existing selection
            selectedImages = selectedImages.concat(newImages);
            renderSelectedImages();
            showMessage('success', 'Successfully uploaded ' + newImages.length + ' image(s)!');
        }
    }
    
})(jQuery);
