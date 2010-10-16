PIC.log = function () {
	return {
		debug: function () {
			 if (PIC.env.debug === true && window.console && window.console.log) {
        		window.console.log('[ПИК] ' + Array.prototype.join.call(arguments,''));
			}
		}
	};
}();
