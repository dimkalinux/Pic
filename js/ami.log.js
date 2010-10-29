AMI.log = function () {
	return {
		debug: function () {
			 if (AMI.env.debug === true && window.console && window.console.log) {
        		window.console.log('[' + AMI.env.cite_name + ']' + Array.prototype.join.call(arguments,''));
			}
		}
	};
}();
