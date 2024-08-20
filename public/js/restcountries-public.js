(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	
	$(document).on('change', '.restcountries-select', function(e) {

		var sel_country = $(this).find(':selected');
		$('.restcountries-select-name').html( sel_country.data('name') );
		$('.restcountries-select-flag').html( "<img src='" + sel_country.data('flag') + "' width='100' />" );
		$('.restcountries-select-language').html( sel_country.data('language') );
		$('.restcountries-select-currency').html( sel_country.data('currency') );
		$('.restcountries-select-borders').html( sel_country.data('borders') );
		$('.restcountries-select-button').show();
	});

	$(document).on('click', '.restcountries-save', function(e) {

		$.ajax({
			type: 'POST',
			url: $(this).data('ajaxurl'),
			dataType: 'JSON',
			data: {
				action: 'save_restcountries',
				name:    $('.restcountries-select-name').html(),

			},
		}).success(function(response) {

			if (response['success'] === true) {

				$('.restcountries-list').append( $('.restcountries-select-name').html() + ' (borders: ' + $('.restcountries-select-borders').html() + ')'  );

				alert(response['message']);

			}
		}).error(function(response) {

			alert('ERROR');
		});

	});

})( jQuery );
