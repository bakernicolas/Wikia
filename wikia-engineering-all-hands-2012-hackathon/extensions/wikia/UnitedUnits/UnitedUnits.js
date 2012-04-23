var UnitedUnits = {
	init: function() {
		$( 'span.unit' ).each( function( key, value) {
			var span = $( value );
			var data = jQuery.parseJSON( span.attr( 'data' ) );
			if ( 'pl' == wgUserLanguage ) {
				span.html( data.kilometre );
				span.attr( 'title', data.mile );
			} else {
				span.html( data.mile );
				span.attr( 'title', data.kilometre );
			}
		});
	}
}

$(function() {
	UnitedUnits.init();
});
