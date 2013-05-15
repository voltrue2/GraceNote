function Dictionary (viewElement) {
	this.parent = viewElement;
	this.parent.style.background = 'url(/img/contents/src/background/colorful.jpg)';
	this.parent.style.backgroundSize = '100% 100%';
	this.dictionary = null;
	this.dictionaryMap = null;
	this.seen = null;
	this.currentPage = 0;
	this.num = 9;
	this.currentDic = [];
	this.qmark = '/img/contents/src/ui/question-mark.png';
	// back
	var self = this;
	var btn = this.parent.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px auto';
	btn.style.color = '#fff';
	btn.style.border = '2px solid #600';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FF9C9C), to(#CF3232))';
	btn.style.width = '60px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = 'Back';
	btn.style.margin = '0';
	btn.style.top = '39px';
	btn.style.left = '5px';
	btn.style.position = 'absolute';
	button.create(btn, function () {
		window.viewPort.open('Main');
	});
	// previous
	var self = this;
	var btn = this.parent.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px auto';
	btn.style.color = '#fff';
	btn.style.border = '2px solid #006';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#9C9CFF), to(#3232CF))';
	btn.style.width = '30px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = '<';
	btn.style.margin = '0';
	btn.style.top = '50%';
	btn.style.left = '5px';
	btn.style.marginTop = '-15px';
	btn.style.position = 'absolute';
	btn.style.zIndex = 100;
	button.create(btn, function () {
		self.goBack();
	});
	// next
	var self = this;
	var btn = this.parent.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px auto';
	btn.style.color = '#fff';
	btn.style.border = '2px solid #006';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#9C9CFF), to(#3232CF))';
	btn.style.width = '30px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = '>';
	btn.style.margin = '0';
	btn.style.top = '50%';
	btn.style.left = '281px';
	btn.style.marginTop = '-15px';
	btn.style.position = 'absolute';
	btn.style.zIndex = 100;
	button.create(btn, function () {
		self.goNext();
	});
	// page
	this.page = this.parent.create('div');
	this.page.css({
		position: 'absolute',
		top: '45px',
		left: '50%',
		color: '#fff',
		fontSize: '20px',
		textShadow: '1px 1px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000',
		lightHeight: '30px',
		textAlign: 'center',
		width: '80px',
		height: '30px',
		marginLeft: '-40px'
	});
	// container
	var container = this.parent.create('div');
	container.css({
		width: '320px',
		height: '300px',
		position: 'absolute',
		top: '100px',
		left: '0',
		WebkitTapHighlightColor: 'rgba(0,0,0,0)'
	});
	this.container = container;
	this.rows = [];
	for (var i = 0; i < this.num / 3; i++){
		var elm = container.create('div');
		elm.css({
			margin: '0 35px',
			textAlign: 'center',
			clear: 'both'
		});
		this.rows.push(elm);
	} 
	// swipe paging
	var gesture = new Gesture(container);
	gesture.swipe('right', function (dir) {
			self.goBack();
	});
	gesture.swipe('left', function (dir) {
			self.goNext();
	});
	// fadeIn
	this.leftIn = new FrameAnimation(container);
	this.leftIn.addKeyFrame(0, { opacity: 0, WebkitTransform: 'translate3d(-30px, 0, 0)'});
	this.leftIn.addKeyFrame(3, { opacity: 1, WebkitTransform: 'translate3d(0, 0, 0)'});
	this.rightIn = new FrameAnimation(container);
	this.rightIn.addKeyFrame(0, { opacity: 0, WebkitTransform: 'translate3d(30px, 0, 0)'});
	this.rightIn.addKeyFrame(3, { opacity: 1, WebkitTransform: 'translate3d(0, 0, 0)'});
}

Dictionary.prototype.onOpen = function (params, cb) {	
	if (params && params.currentPage !== undefined) {
		this.currentPage = params.currentPage;
	}
	var loader = new Loader();
	var preloader = [];
	var len = this.currentPage + this.num;
	this.currentDic = [];
	for (var i = this.currentPage; i < len; i++) {
		if (this.dictionary[i]) {
			preloader.push(this.dictionary[i].sprite);
			this.currentDic.push(this.dictionary[i]);
		}
	}
	preloader.push(this.qmark);
	var self = this;
	loader.asyncImg(preloader, function () {
		self.display();
		cb();
	});
	this.container.css({ opacity: 0 });
	viewPort.overlay('TopMenu');
};

Dictionary.prototype.onOpenComplete = function (params) {
	if (params && params.direction) {
		if (params.direction === 'left') {
			this.leftIn.start();
		} else if (params.direction === 'right') {
			this.rightIn.start();
		} else {
			this.container.css({ opacity: 1 });
		}
	} else {
		this.container.css({ opacity: 1 });
	}
};

Dictionary.prototype.goBack = function () {
	var prevPage = this.currentPage - this.num;
	if (prevPage < 0) {
		var remaining = (this.dictionary.length % this.num);
		if (remaining) {
			prevPage = this.dictionary.length - remaining;	
		} else {
			prevPage = this.dictionary.length - this.num;
		}
	}
	window.viewPort.open('Dictionary', { currentPage: prevPage, direction: 'left' });
};

Dictionary.prototype.goNext = function () {
	var nextPage = this.currentPage + this.num;
	if (nextPage >= this.dictionary.length) {
		nextPage = 0;
	}
	window.viewPort.open('Dictionary', { currentPage: nextPage, direction: 'right' });
};

Dictionary.prototype.setup = function (cb) {
	var self = this;
	server.send('demo.get_dictionary', null, function (error, res) {
		if (error) {
			return cb(error);
		}
		self.dictionary = res.dictionary;
		self.seen = res.seen;
		self.dictionaryMap = {};
		for (var i = 0, len = self.dictionary.length; i < len; i++) {
			var item = self.dictionary[i];
			self.dictionaryMap[item.identifier] = item;
		}
		cb();
	});
};

Dictionary.prototype.getCreature = function (ident) {
	if (this.dictionaryMap && this.dictionaryMap[ident]) {
		return this.dictionaryMap[ident];
	} else {
		return null;
	}
};

Dictionary.prototype.update = function (myCreatures) {
	this.seen = myCreatures.dictionary;
};

Dictionary.prototype.display = function () {
	var page = Math.floor(this.currentPage / this.num) + 1;
	var totalPage = Math.ceil(this.dictionary.length / this.num);
	var digit = totalPage.toString().length;
	this.page.textContent = pad(page, digit) + '/' + pad(totalPage, digit);
	var pivot = 0;
	var index = 0;
	for (var i = 0; i < this.num; i++) {
		this.rows[index];
		// if its not there > create it
		if (!this.rows[index].list) {
			this.rows[index].list = {};
		}
		if (!this.rows[index].list[i]) {
			this.rows[index].list[i] = this.rows[index].create('div');
			this.rows[index].list[i].css({
				width: '50px',
				height: '80px',
				lineHeight: '80px',
				margin: '5px 15px',
				background: '#000',
				cssFloat: 'left'
			});
		}
		button.disable(this.rows[index].list[i]);
		this.rows[index].list[i].style.display = 'block';
		this.rows[index].list[i].style.opacity = 0.8;
		if (this.currentDic[i] && this.seen[this.currentDic[i].identifier]) {
			// seen creature
			this.rows[index].list[i].css({
				opacity: 1,
				background: 'url('+ this.currentDic[i].sprite + ') no-repeat',
				backgroundSize: '100%',
				backgroundPosition: '50%',
				WebkitMaskImage: ''
			});
			button.create(this.rows[index].list[i], bind(null, function (event, creatureData, btn) {
				window.viewPort.open('Creature', { creatureData: creatureData, noTrain: true, noBoost: true, noFusion: true, noRelease: true, showBuy: true, back: 'Dictionary' });
			}, this.currentDic[i], this.rows[index].list[i]));
		} else {
			if (!this.currentDic[i]) {
				// no creature for this row and column
				this.rows[index].list[i].css({
					display: 'none'
				});	
			} else {
				// never seen creature
				if (this.currentDic[i].lvl > window.game.session.data.lvl) {
					this.rows[index].list[i].css({ 
						WebkitMaskImage: '',
						background: 'url(' + this.qmark + ') no-repeat',
						backgroundSize: '100% auto',
						backgroundPosition: '50%',
						width: '50px',
						height: '80px',
						lineHeight: '80px'
						
					});
				} else {
					this.rows[index].list[i].css({
						background: '#000',
						WebkitMaskImage: 'url('+ this.currentDic[i].sprite + ')',
						WebkitMaskRepeat: 'no-repeat',
						WebkitMaskSize: '100%'
					});
				}		
			}
		}
		pivot += 1;
		if (pivot === 3) {
			pivot = 0;
			index += 1;
		}
	}
};








