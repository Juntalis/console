/**
 * Log viewer Javascript
 */
$(document).ready( function(){
	// Hide the directory tree
	$('.directory .year').hide();
	$('.directory .files').hide();

	// Open up the active year/month
	var active = $('.directory .files li.active');

	if( active ) {
		$(active).parent('ul').show();
		$(active).parents('.year').show();
	}

} );

$(function(){

	// Slide in years
	$('.directory h2').click( function(){
		// Show/hide the year
		var year = $(this).next('.year');

		( $(year).is(':visible') ) ?
			$(year).slideUp('normal') :
			$(year).slideDown('normal') ;
			$
	} );

	// Fade in files
	$('.directory h3').click( function(){
		// Get the list
		var ul = $(this).siblings('ul');

		// Slide up or down
		if( $(ul).is(':visible') ){
			$(ul).slideUp('normal');
		}
		else {
			$(ul).slideDown('normal');
		}
	} );

	// Hover click
	$('.entry').click( function(){
		// Entry
		$(this).toggleClass('call-out');
	} );

	// Added by Charles Grunwald (Juntalis)
	/* 	It was going to slow with my first implementation of the filter, so
		I did it this way to improve the speed.
	 */
	var _entries = {
		'all'		: $("div.entry"),
		'debug' 	: $("div.debug"),
		'not_debug'	: $("div.info").add("div.error"),
		'info'		: $("div.info"),
		'not_info'	: $("div.debug").add("div.error"),
		'error' 	: $("div.error"),
		'not_error'	: $("div.debug").add("div.info")
	};
	//var _debugEntries = ;
	var _infoEntries = $("div.debug");
	var _errorEntries = $("div.debug");
	$('#current > a').each(function(){
		$(this).click(function(){
			if ($(this).attr('href')=="#current") {
				return false;
			}

			var _filter = $(this).attr('id');
			if(_filter != 'all') {
				_entries['not_' + _filter].css('display','none');
			}
			_entries[_filter].css('display','block');
			var _newhref = $("#current > a[href=#current]").attr('id');
			$("#current > a[href=#current]").attr('href', '#' + _newhref);
			$(this).attr('href', '#current');
			return false;
		})
	});

});