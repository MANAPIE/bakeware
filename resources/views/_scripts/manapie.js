$(function(){
	makeResposible_manapie();
	$(window).resize(function(){makeResposible_manapie();});
});

function makeResposible_manapie(){
	$('.input_wrap.wide').each(function(){
		$(this).find('input,textarea').css('margin-top',$(this).find('span').height()+15);
	});
	$('.selects.wide').each(function(){
		$(this).css('padding-top',$(this).find('span').height()+25);
	});
}