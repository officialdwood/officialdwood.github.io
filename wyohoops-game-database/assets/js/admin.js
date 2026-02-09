/**
 * WyoHoops Game Database - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize color pickers
        if ($.fn.wpColorPicker) {
            $('.wyohoops-color-picker').wpColorPicker();
        }
        
        // Media uploader
        $('.wyohoops-upload-button').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var targetId = button.data('target');
            var previewId = targetId.replace('_id', '_preview');
            
            var mediaUploader = wp.media({
                title: 'Select Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#' + targetId).val(attachment.id);
                $('#' + previewId).html('<img src="' + attachment.url + '" style="max-width: 200px;">');
            });
            
            mediaUploader.open();
        });
        
        // Delete team
        $('.wyohoops-delete-team').on('click', function() {
            if (!confirm('Are you sure you want to delete this team?')) {
                return;
            }
            
            var teamId = $(this).data('team-id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: wyohoopsAdmin.ajax_url,
                method: 'POST',
                data: {
                    action: 'wyohoops_delete_team',
                    nonce: wyohoopsAdmin.nonce,
                    team_id: teamId
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(function() {
                            row.remove();
                        });
                    } else {
                        alert('Error deleting team: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the team.');
                }
            });
        });
        
        // Delete game
        $('.wyohoops-delete-game').on('click', function() {
            if (!confirm('Are you sure you want to delete this game?')) {
                return;
            }
            
            var gameId = $(this).data('game-id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: wyohoopsAdmin.ajax_url,
                method: 'POST',
                data: {
                    action: 'wyohoops_delete_game',
                    nonce: wyohoopsAdmin.nonce,
                    game_id: gameId
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(function() {
                            row.remove();
                        });
                    } else {
                        alert('Error deleting game: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the game.');
                }
            });
        });
    });
    
})(jQuery);
