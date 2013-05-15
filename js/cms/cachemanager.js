(function () {
	
	var loader = new Loader();

	window.deleteCache = function (index) {
		var res = confirm(window.text.deleteConfirmMsg.replace('$1', window.list[index]));
		if (res) {
			var uri = '/cachemanager/delete/';
			var send = {
				key: window.list[index]
			};
			loader.ajax(uri, send, function (error, req, res) {
				if (error) {
					return alert(window.text.failedMsg);
				}
				var item = Dom.getById(index);
				item.remove();
				window.list.splice(index, 1);
				var display = Dom.getById('numDisplay');
				display.text('(' + (window.from || 0) + ' - ' + (from + window.list.length) + ')');
			});
		}
	};

	window.getPreview = function (index) {
		window.lightbox.show(700, 500, function (bar, box, close) {
			var container = box.createChild('div', { width: '700px', height: '480px', overflow: 'scroll' });
			var uri = '/cachemanager/getValue/';
			var send = {
				key: window.list[index]
			};
			loader.ajax(uri, send, function (error, req, res) {
				if (error) {
					return alert(window.text.failedMsg);
				}
				var key = '<div style="text-align:center; margin: 5px; font-size: 15px;">' + window.list[index] + '</div>';
				container.html(key + window.beautify(res.value));
			});
		});
	}

	var searchBtn = Dom.getById('searchBtn');
	var searchField = Dom.getById('searchField');
	if (searchBtn && searchField) {
		searchBtn.on('tapend', function () {
			execSearch();
		});
		searchField.allowEvents(['change']);
		searchField.on('change', function () {
			execSearch();
		});
		
		function execSearch() {
			if (!searchField.get('value')) {
				return;
			}
			var uri = '/cachemanager/index/0/' + searchField.get('value') + '/';
			window.location.href = uri;

		}
	}

}());
