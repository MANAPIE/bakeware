var header=100;
	
$(function(){
	$('#body .navigation_button,#body .navigation_shadow').click(function(){
		$('#header').toggleClass('active');
		$('#body').toggleClass('active');
	});
	
	makeResposible();
	$(window).resize(function(){makeResposible();});
});

function makeResposible(){
}