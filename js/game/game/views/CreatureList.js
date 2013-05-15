function CreatureList (viewElement) {
	this.parent = viewElement;
	this.parent.style.background = 'url(/img/contents/src/background/colorful.jpg)';
	this.parent.css({ backgroundSize: '100% 100%' });
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
	this.backBtn = btn;
	
	this.header = this.parent.create('div');
	this.header.style.top = '29px';
	this.header.style.left = '75px';
	this.header.style.position = 'absolute';
	this.header.style.width = '245px';
	this.header.style.height = '50px';
	
	var container = this.parent.create('div');
	container.style.background = 'rgba(0, 0, 0, 0.7)';
	container.style.borderTop = '2px solid rgba(255, 255, 255, 1)';
	container.style.borderBottom = '2px solid rgba(255, 255, 255, 1)';
	container.style.top = '79px';
	container.style.left = '0';
	container.style.position = 'absolute';
	container.style.width = '100%';
	container.style.height = '69%';
	var list = container.create('div');
	this.creatureBox = list.create('div');
	this.creatureBox.list = [];
	this.scroller = new iScroll(container);
	
	this.listMax = 30;
}

CreatureList.prototype.onOpen = function (params, cb) {
	this.creatureBox.css({ display: 'none', opacity: 0 });
	viewPort.overlay('TopMenu');
	cb();
};

/*
* params.ignoreCreatureIds: Array
* params.back: Object { viewName, params }
* params.onSelect: Function
* params.headerHTML: String
*/
CreatureList.prototype.onOpenComplete = function (params) {
	var self = this;
	window.game.fadeIn.setTarget(this.creatureBox);
	window.game.fadeIn.once('start', function () {
		self.creatureBox.css({ display: 'block' });
		button.destroy(self.backBtn);
		button.create(self.backBtn, function () {
			if (params && params.back) {
				viewPort.open(params.back.viewName, params.back.params);
			} else {
				viewPort.open('Main');
			}
		});
		var ignoreList = (params && params.ignoreCreatureIds) ? params.ignoreCreatureIds : [];
		var onSelect = (params && params.onSelect) ? params.onSelect : null;
		self.displayList(ignoreList, onSelect);
		if (params && params.headerHTML) {
			self.header.innerHTML = params.headerHTML;
			self.header.style.display = '';
		}
		else {
			self.header.style.display = 'none';
		}
		setTimeout(function () {self.scroller.refresh();}, 0);
	});
	window.game.fadeIn.start();
};

CreatureList.prototype.displayList = function (ignore, onSelect) {
	var size = 50;
	var self = this;
	var tm = viewPort.views.TeamManagement;
	for (var i = 0, len = this.listMax; i < len; i++) {
		if (!this.creatureBox.list[i]) {
			var item = this.creatureBox.list[i] = this.creatureBox.create('div');
			this.creatureBox.list[i].sprite = this.creatureBox.list[i].create('div');
			this.creatureBox.list[i].sprite.style.width = '100%';
			this.creatureBox.list[i].sprite.style.height = size + 'px';
			this.creatureBox.list[i].sprite.style.margin = '0 10px';
			this.creatureBox.list[i].style.backgroundSize = 'auto 100%';
			this.creatureBox.list[i].style.backgroundRepeat = 'no-repeat';
			this.creatureBox.list[i].style.backgroundPosition = 'center';
			item.style.height = size + 'px';
			//item.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(255, 255, 255, 0.2)), to(rgba(255, 255, 255, 0.5)))';
			item.style.background = 'rgba(255, 255, 255, 0.7)';
			item.style.borderBottom = '1px solid #000';
			this.creatureBox.list[i].text = this.creatureBox.list[i].sprite.create('div');
			this.creatureBox.list[i].text.style.opacity = 0.6;
			this.creatureBox.list[i].text.style.color = '#fff';
			this.creatureBox.list[i].text.style.textShadow = '1px 1px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000';
			this.creatureBox.list[i].text.style.fontWeight = 'bold';
			this.creatureBox.list[i].text.style.fontSize = '12px';
			this.creatureBox.list[i].text.style.paddingTop = '10px';
			this.creatureBox.list[i].text.style.paddingRight = '25px';
			this.creatureBox.list[i].text.style.textAlign = 'right';
			var btn = this.creatureBox.list[i].create('div');
			btn.style.textAlign = 'center';
			btn.style.padding = '4px auto';
			btn.style.color = '#fff';
			btn.style.border = '2px solid #006';
			btn.style.WebkitBorderRadius = '8px';
			btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#9C9CFF), to(#3232CF))';
			btn.style.width = '60px';
			btn.style.height = '30px';
			btn.style.lineHeight = '30px';
			btn.textContent = 'Select';
			btn.style.margin = '0';
			btn.style.top = '-42px';
			btn.style.left = '10px';
			btn.style.position = 'relative';
			this.creatureBox.list[i].select = btn;
		}
		var cell = this.creatureBox.list[i];
		if (tm.list.creature_list[i] && ignore.indexOf(tm.list.creature_list[i].id) === -1) {
			cell.sprite.style.background = 'url(' + tm.list.creature_list[i].sprite + ')';
			cell.sprite.style.backgroundSize = 'auto 200%';
			cell.sprite.style.backgroundRepeat = 'no-repeat';
			cell.sprite.style.backgroundPosition = '70% 10%';
			cell.style.display = '';
			cell.text.innerHTML = 'Level ' + tm.list.creature_list[i].lvl + '<br />' + tm.list.creature_list[i].name;
			button.destroy(cell.select);
			if (onSelect) {
				cell.select.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#9C9CFF), to(#3232CF))';
				cell.select.style.border = '2px solid #006';
				button.create(cell.select, bind(null, function (event, index, creature) {
					onSelect(event, index, creature);
				}, i, tm.list.creature_list[i]));
			}
			else {
				cell.select.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#ccc), to(#ccc))';
				cell.select.style.border = '2px solid #999';
			}
		}
		else {
			cell.style.display = 'none';
		}
	}
};

CreatureList.prototype.onCloseComplete = function () {
	this.header.innerHTML = '';
};
