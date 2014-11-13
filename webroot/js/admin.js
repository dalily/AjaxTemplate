/**
 * Admin
 *
 * for admin pages
 */
var Admin = typeof Admin == 'undefined' ? {} : Admin;

/**
 * Forms
 *
 * @return void
 */
Admin.form = function() {
	// Tooltips activation
	$('body[rel=tooltip],*[data-title]:not([data-content]),input[title],textarea[title]').tooltip();
	if (typeof $.prototype.tipsy == 'function') {
		$('a.tooltip').tipsy({gravity: 's', html: false}); // Legacy tooltip
	}
}


/**
 * Document ready
 *
 * @return void
 */
$(document).ready(function() {
	//Admin.form();
	
	$(document).on('onLoadDatagrid', function(e){
		//$('*[data-title]:not([data-content])').tooltip();
	});

});
