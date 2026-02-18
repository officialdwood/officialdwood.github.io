(function ($) {
    $(document).on('click', '.wyohoops-tab', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        var wrapper = $(this).closest('.wyohoops-wrapper');
        wrapper.find('.wyohoops-tab').removeClass('is-active');
        $(this).addClass('is-active');
        wrapper.find('.wyohoops-team-grid').hide();
        wrapper.find('.wyohoops-team-grid[data-gender="' + target + '"]').fadeIn(150);
    });
})(jQuery);
