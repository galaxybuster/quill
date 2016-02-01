$(function() {
	$('#animation-spinner').hide(0);

	$('.page').hide();
	$('#madman').hide(0).delay(70).fadeIn(200);
	$('.nav').hide(0).delay(270).show(0);
	$('.page-active').show();
	//$(".page-active p").hide();
	// $(".page-active p").delay(340).each(function(i) {
	// 	var d = 70;// + Math.random()*100;
	// 	$(this).delay(d*i).show(0).children('a, span, label').shuffleLetters({fps:50});
	// });


	// offset = $('body').outerHeight() / 2 - (93/2); 
	// $('body').css('padding-top', offset);

	// Nav
	$('.navlink').click(function() {
		$('.page').hide();
		$('.page-active').removeClass('page-active');
		$('.nav-active').toggleClass('nav-active');
		$(this).addClass('nav-active');

		$('#' + $('.nav-active').data('page')).show().addClass('page-active');
		
		$(".page-active p").hide();
		// Page transitions
		$(".page-active p").each(function(i) {
			var d = 70;// + Math.random()*100;
			$(this).delay(d*i).show(0).children('a, span, label').shuffleLetters({fps:50});
		});
	});


	// Contact
	$('#b-send').click(function() {
		// show working animation
		$(this).hide();
		$('#result').hide();
		$('#animation-spinner').fadeIn(250);

		$.ajax({
			url:"contact.php",
			type:"POST",
			data:{
				name:$('#f-name').val(),
				reply:$('#f-reply').val(),
				msg:$('#f-msg').val(),
				verify:$('#hidden-verify').val()
			}
		}).done(function(msg) {
			msg = JSON.parse(msg);

			if (msg.success) {
				// animation for success
				console.log("Success");
				$('#animation-spinner').fadeOut(250, function() {
					$('#result').text('Message sent').fadeIn(250).delay(3000).fadeOut(250);
				});
				
			} else {
				// animation for failure
				console.log("Error");
				$('#animation-spinner').fadeOut(250, function() {
					$('#result').text(msg.err).fadeIn(250).delay(1500).fadeOut(250, function() {
						$('#b-send').fadeIn(250);
					});
				});
			}
		},'json');
	});
});