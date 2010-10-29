if (typeof PIC === "undefined" || !PIC) {
	var PIC = {};
}


PIC.env = PIC.env || {
	// AJAX backends
	ajaxBackend: AMI.env.root_url + 'script/ajax_backend.php',

	// AJAX actions
	actionServiceUpdate: 1,

	// cookie
	appsStatusCookie: 'portal_iteam_apps_status'
};
