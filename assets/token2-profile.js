(function($){
	var container = $('#token2_user');

	container.html( '<tr><th><label>' + window.token2.th_text + '</label></th><td><a class="button thickbox" href="' + window.token2.ajax + '&KeepThis=true&TB_iframe=true&height=380&width=450">' + window.token2.button_text + '</a></td></tr>' );

	$( '.button', container ).on( 'click', function( ev ) {
		ev.preventDefault();
	} );
})(jQuery);