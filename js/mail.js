(function($) {
	var isSubmitForm = false;
	$("#ajax-contact-form .submit").click(function(e) {
		e.preventDefault();
		if (isSubmitForm)
			return false;

		var str = $("#ajax-contact-form").serialize();		
		var href = $("#ajax-contact-form").attr('action');
		isSubmitForm = true;
		$.ajax({
			type: "POST",
			url: href,
			data: str,
			success: function(msg) {
				isSubmitForm = false;
				// Message Sent - Show the 'Thank You' message and hide the form
				if(msg == 'OK') {
					$("#ajax-contact-form").find('success').removeClass('hidden'); 
				} else {
					$("#ajax-contact-form").find('error').removeClass('hidden');
				}
			}
		});
		return false;
	});
})(jQuery); 
