(function () {

	var htmlTagsForReplacement = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;'
	};

	function beautify(obj) {
		var space = '&nbsp;&nbsp;&nbsp;';
		var lb = '<br />';
		return parse(obj, '', space, lb);
	}

	window.beautify = beautify;

	function parse(obj, indent, space, lb) {
		var str = '';
		if (typeof obj === 'object') {
			var isArray = Array.isArray(obj);
			var op = ':' + space;
			for (var key in obj) {
				var val, braceOpen, braceClose;
				var item = obj[key];
				if (isNaN(key)) {
					key = color(key, '#09f', '"');
				}
				if (typeof item === 'object' && item) {
					val = lb + parse(item, indent + space, space, lb);
					if (Array.isArray(item)) {
						braceOpen = '[';
						braceClose = ']';
					} else {
						braceOpen = '{';
						braceClose = '}';
					}
				} else {
					val = format(item);
					braceOpen = '';
					braceClose = '';
				}
				val = braceOpen + val + indent + braceClose;
				if (isArray) {
					str += indent + val + lb;
				} else {
					str += indent + key + ' <strong>' + op + '</strong> ' + val + lb;
				}
			}
		} else {
			str = format(obj);
		}
		return str;
	}

	function format(value) {
		switch (typeof value) {
			case 'string':
				value = color(escapeHtml(value), '#090', '"');
				break;
			case 'number':
				value = color(value, '#00f');
				break;
			case 'boolean': 
				value = color(value, '#f00');
				break;
		}
		return value;
	}

	function color(value, colorCode, quote) {
		if (!quote) {
			quote = '';
		}
		return '<span style="color: ' + colorCode + ';">' + quote + value + quote + '</span>';
	}

	function escapeHtml(str) {
		return str.replace(/[&<>]/g, replaceTag);
	}

	function replaceTag(tag) {
		return htmlTagsForReplacement[tag] || tag;
	}
}());
