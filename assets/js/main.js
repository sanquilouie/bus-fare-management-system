(function ($) {

	"use strict";

	var fullHeight = function () {

		$('.js-fullheight').css('height', $(window).height());
		$(window).resize(function () {
			$('.js-fullheight').css('height', $(window).height());
		});

	};
	fullHeight();

	$('#sidebarCollapse').on('click', function () {
		$('#sidebar').toggleClass('active');
	});

})(jQuery);

$(document).ready(function () {
	$('.nav-item').hover(
		function () {
			$(this).find('.collapse').stop(true, true).slideDown(200);
		},
		function () {
			$(this).find('.collapse').stop(true, true).slideUp(200);
		}
	);
});

