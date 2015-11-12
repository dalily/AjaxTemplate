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

function createButtonGroup(buttonsArr) {
    buttonsHtml = "";
    for (var i = 0; i < buttonsArr.length; i++) {
        buttonParam = buttonsArr[i];
        buttonsHtml += createButton(buttonParam.value, buttonParam.attr);
    }
    return buttonsHtml;
}

function createButton(value, buttonParam) {
    if (("icon" in buttonParam)) {
        value = '<i class="fa fa-' + buttonParam['icon'] + '"></i> ' + value;
        delete buttonParam["icon"];
    }
    return '<button ' + _joinAttrubutes(buttonParam, ' ') + '>' + value + '</button> ';
}

function _joinAttrubutes(obj, sup) {
    var out = [];
    for (var i in obj) {
        out.push(i + '="' + obj[i] + '"');
    }
    return out.join(sup);
}