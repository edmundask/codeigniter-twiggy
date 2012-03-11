
$(document).ready(function() {
	$('nav ul li a').click(function(event) {
		//event.preventDefault();
		var el = $(this).attr('href');

		$('html, body').animate({
		    scrollTop: $(el).offset().top
		}, {
    			duration: 1000,
    			specialEasing: {
      			scrollTop: 'easeInOutCirc'
      		}
		});
	});

	$('a#to-top').click(function(event) {

		$('html, body').animate({
		    scrollTop: 0
		}, {
    			duration: 1000,
    			specialEasing: {
      			scrollTop: 'easeInOutCirc'
      		}
		});
	});
});

$(window).scroll(function(){
	console.log($(window).scrollTop());
  if  ($(window).scrollTop() == $(document).height() - $(window).height()){
    console.log('yay');
  }

  if($(window).scrollTop() >= 420) {
  	$('#to-top').fadeIn();
  } else {
  	$('#to-top').fadeOut();
  }
});