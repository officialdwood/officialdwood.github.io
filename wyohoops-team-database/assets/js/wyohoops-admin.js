(function ($) {
    $(document).ready(function () {
        var frame;
        $('.wyohoops-media-upload').on('click', function (e) {
            e.preventDefault();
            var target = $('#' + $(this).data('target'));
            frame = wp.media({
                title: 'Select Logo',
                button: { text: 'Use this logo' },
                multiple: false
            });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                target.val(attachment.id);
                target.siblings('.wyohoops-logo-preview').html('<img src="' + attachment.url + '" style="max-width:64px;max-height:64px;" />');
            });
            frame.open();
        });

        $('.wyohoops-toggle-player').on('click', function (e) {
            e.preventDefault();
            var target = $('#' + $(this).data('target'));
            target.toggle();
        });

        $(document).on('submit', '.wyohoops-delete-form', function (e) {
            var message = $(this).data('confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                e.preventDefault();
            }
        });
    });
})(jQuery);
