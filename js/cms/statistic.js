(function () {

	var loader = new Loader();
	var statistic = {};
	window.statistic = statistic;

	statistic.getData = function (type, cb) {
		var uri = '/statistic/getData/' + type + '/';
		loader.ajax(uri, null, function (error, path, res) {
			if (error) {
				return alert(error);
			}
			cb(res.data);
		});
	};

	statistic.createBarGraph = function (data) {

	};

}());
