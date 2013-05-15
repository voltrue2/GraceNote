/***
* Dependency: Animate Sequence
**/
function Progressbar (parent) {
	
	var self = this;
	var core = null;
	var progressing = false;
	var reverse = false; // false: progress -> subprogress, true: subprogress -> progress
	var queues = [];
	var cb = [];
	var duration = 1000; // default
	var width = 100; // default
	var height = 10; // default
	var radius = px(3); // default
	var wbk = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(255, 255, 255, 1)), to(rgba(199, 199, 199, 1)), color-stop(0.7, rgba(71, 71, 71, 1)))'; // default
	var mbk = '-moz-linear-gradient(19% 75% 90deg, rgba(199, 199, 199, 1), rgba(255, 255, 255, 1))'; // default
	var wo = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(255, 255, 255, 0.5)), to(rgba(199, 199, 199, 0.5)), color-stop(0.7, rgba(25, 25, 25, 0.5)))'; // default
	var mo = '-moz-linear-gradient(19% 75% 90deg, rgba(199, 199, 199, 0.5), rgba(255, 255, 255, 0.5))'; // default
	var core_default = {
		width: px(width),
		height: px(height),
		WebkitBorderRadius: radius,
		MozBorderRadius: radius,
		OBorderRadius: radius,
		background: wbk,
		backgroundColor: wbk,
		border: '1px solid #fff',
		margin: 0,
		padding: 0
	};
	var overlay_default = {
		width: px(width),
		height: px(height),
		WebkitBorderRadius: radius,
		MozBorderRadius: radius,
		OBorderRadius: radius,
		background: wo,
		backgroundColor: mo,
		position: 'relative',
		top: per(-200),
		margin: 0,
		padding: 0
	};
	var subprogress_default = {
		width: px(width),
		height: px(height),
		WebkitBorderRadius: radius,
		MozBorderRadius: radius,
		OBorderRadius: radius,
		background: '#003399',
		margin: 0,
		padding: 0,
		transformOrigin: '0%',
		WebkitTransformOrigin: '0%'
	};
	var progress_default = {
		width: px(width),
		height: px(height),
		WebkitBorderRadius: radius,
		MozBorderRadius: radius,
		OBorderRadius: radius,
		background: '#0066FF',
		position: 'relative',
		top: per(-100),
		margin: 0,
		padding: 0,
		transformOrigin: '0%',
		WebkitTransformOrigin: '0%'
	};
	
	/* Public Method */
	this.size = function (w, h) {
		core_default.width = px(w);
		core_default.height = px(h);
		overlay_default.width = px(w);
		overlay_default.height = px(h);
		progress_default.width = px(w);
		progress_default.height = px(h);
		subprogress_default.width = px(w);
		subprogress_default.height = px(h);
	};
	
	this.get = function () {
		return core;
	};
	
	this.create = function () {
		if (core){
			// avoid duplicates
			console.error('Progress.create: Progress bar already exists');
			return;
		}
		if (parent && parent.appendChild){
			// core container of the progressbar
			core = document.createElement('div');
			core.className = 'progressbar-core';
			core.css = function (styles){
				css(core, styles);
			};
			core.css(core_default);
			parent.appendChild(core);
			// subprogress bar
			core.subprogress = document.createElement('div');
			core.subprogress.current = 0;
			core.subprogress.className = 'progressbar-subprogress';
			core.subprogress.css = function (styles){
				css(core.subprogress, styles);
			};
			core.subprogress.css(subprogress_default);
			core.appendChild(core.subprogress);
			// progress bar
			core.progress = document.createElement('div');
			core.progress.className = 'progressbar-progress';
			core.progress.current = 0;
			core.progress.css = function (styles){
				css(core.progress, styles);
			};
			core.progress.css(progress_default);
			core.appendChild(core.progress);
			// overly
			core.overlay = document.createElement('div');
			core.overlay.className = 'progressbar-overlay';
			core.overlay.css = function (styles){
				css(core.overlay, styles);
			};
			core.overlay.css(overlay_default);
			core.appendChild(core.overlay);
			// set up
			self.progress(0, 0, false);
		}
		else {
			console.error('Progressbar.create: Invalid DOM element given');
		}
	};
	
	// milliseconds
	this.duration = function (d) {
		duration = d;
	};
	
	// revese the animation sequence
	this.reverse = function () {
		reverse = true;
	};
	
	this.unreverse = function () {
		reverse = false;
	};
	
	this.reset = function () {
		if (core.progress.animate){
			core.progress.animate.clear();
			core.progress.animate.stop();
			core.progress.animate = null;
		}
		if (core.subprogress.animate){
			core.subprogress.animate.clear();
			core.subprogress.animate.stop();
			core.subprogress.animate = null;
		}
		if (core.seq){
			core.seq.clearall();
			core.seq.stop();
			core.seq = null;
		}
		queues = [];
		cb = [];
		progressing = false;
		core.progress.current = 0;
		core.subprogress.current = 0;
		self.progress(0, 0, false);
	};
	
	this.onprogress = function (callback) {
		if (!cb['progress']){
			cb['progress'] = [];
		}
		cb['progress'][cb['progress'].length] = callback;
	};
	
	this.onsubprogress = function (callback) {
		if (!cb['subprogress']){
			cb['subprogress'] = [];
		}
		cb['subprogress'][cb['subprogress'].length] = callback;
	};
	
	this.onfinish = function (callback) {
		if (!cb['finish']){
			cb['finish'] = [];
		}
		cb['finish'][cb['finish'].length] = callback;
	};
	
	/***
	* p: 0 to 100
	* sub: 0 to 100
	* animate: true/false
	**/
	this.progress = function (p, sub, animate) {
		if (!progressing){
			progressing = true;
		}
		else {
			set_queue(p, sub, animate);
			return;
		}
		// check for negative and exceeding max
		if (p < 0){
			p = 0;
		}
		else if (p > 100){
			p = 100;
		}
		if (sub < 0){
			sub = 0;
		}
		else if (sub > 100){
			sub = 100;
		}
		// format 
		var sp = p / 100;
		var ssub = sub / 100;
		if (animate){
			// set up progress animate
			if (core.progress.animate){
				core.progress.animate.clear();
				core.progress.animate.stop();
				core.progress.animate = null;
			}
			core.progress.animate = new Animate(core.progress);
			var start = 'scale(' + (core.progress.current / 100) + ', 1)';
			var end = 'scale(' + sp + ', 1)';
			core.progress.animate.frame(0, {
				WebkitTransform: start,
				MozTransform: start,
				transform: start
			});
			core.progress.animate.frame(duration, {
				WebkitTransform: end,
				MozTransform: end,
				transform: end
			});
			core.progress.animate.onfinish(function (){
				core.progress.current = p;
				execb('progress', core.progress.current, core.subprogress.current);
			});
			// set up subprogress animation
			if (core.subprogress.animate){
				core.subprogress.animate.clear();
				core.subprogress.animate.stop();
				core.subprogress.animate = null;
			}
			core.subprogress.animate = new Animate(core.subprogress);
			var start = 'scale(' + sp + ', 1)';
			if (reverse){
				start = 'scale(' + (core.subprogress.current / 100) + ', 1)';
			}
			var end = 'scale(' + ssub + ', 1)';
			core.subprogress.animate.frame(0, {
				WebkitTransform: start,
				MozTransform: start,
				transform: start
			});
			core.subprogress.animate.frame(duration, {
				WebkitTransform: end,
				MozTransform: end,
				transform: end
			});
			core.subprogress.animate.onfinish(function (){
				core.subprogress.current = sub;
				execb('subprogress', core.progress.current, core.subprogress.current);
			});
			// execute animation
			if (core.seq){
				core.seq.clearall();
				core.seq.stop();
				core.seq = null;
			}
			core.seq = new Sequence();
			if (!reverse){
				core.seq.set([core.progress.animate, core.subprogress.animate]);
			}
			else {
				core.seq.set([core.subprogress.animate, core.progress.animate]);
			}
			core.seq.onfinish(function () {
				execb('finish', core.progress.current, core.subprogress.current);
				progressing = false;
				call_queued();
			});
			core.seq.start();
		}
		else {
			// progress
			core.progress.css({
				WebkitTransform: 'scale(' + sp + ', 1)',
				MozTransform: 'scale(' + sp + ', 1)',
				transform: 'scale(' + sp + ', 1)'
			});
			core.progress.current = p;
			// subprogress
			core.subprogress.css({
				WebkitTransform: 'scale(' + ssub + ', 1)',
				MozTransform: 'scale(' + ssub + ', 1)',
				transform: 'scale(' + ssub + ', 1)'
			});
			core.subprogress.current = sub;
			// reset the flag
			progressing = false;
			// check and call queued progress
			call_queued();
		}
	};
	
	/* Private Methods */
	function call_queued () {
		if (queues.length > 0){
			var queue = queues.shift();
			self.progress(queue.p, queue.sub, queue.animate);
		}
	}
	
	function set_queue (p, sub, animate) {
		var index = queues.length;
		queues[index] = {p: p, sub: sub, animate: animate};
	}
	
	function execb (type, p, sub) {
		if (cb[type] && cb[type].length){
			var len = cb[type].length;
			for (var i = 0; i < len; i++){
				cb[type][i](p, sub);
			}
		}
	}
	
	function css (target, styles) {
		if (target && target.style){
			for (var name in styles){
				target.style[name] = styles[name];
			}
		}
		else {
			console.error('Progressbar.css: Invalid DOM element given');
		}
	};
	
	function px (value) {
		return value + 'px'; 
	}
	
	function per (value) {
		return value + '%';
	}
}