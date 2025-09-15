(function($){
$(function(){
  var frame;
  $('.pt-upload').on('click', function(e){
    e.preventDefault();
    if (frame) { frame.open(); return; }
    frame = wp.media({
      title: 'Select or Upload Profile Photo',
      button: { text: 'Use this photo' },
      library: { type: 'image' },
      multiple: false
    });
    frame.on('select', function(){
      var att = frame.state().get('selection').first().toJSON();
      $('#pt_photo').val(att.url);
      $('#pt_photo_preview').attr('src', att.url);
      $('.pt-remove').show();
    });
    frame.open();
  });
  $('.pt-remove').on('click', function(e){
    e.preventDefault();
    $('#pt_photo').val('');
    $('#pt_photo_preview').attr('src', PROTECH_TEAM_URL + 'assets/img/avatar-placeholder.png');
    $(this).hide();
  });
});
})(jQuery);
