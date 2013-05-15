function TeamManagement (viewElement) {
	this.parent = viewElement;
	this.parent.style.background = 'url(/img/contents/src/background/colorful.jpg)';
	this.parent.css({ backgroundSize: '100% 100%' });
	this.list = {};
	this.teamMap = {};
	this.teamBox = this.parent.create('div');
	this.teamBox.style.padding = '5px';
	this.teamBox.style.margin = '0 auto';
	this.teamBox.style.width = '210px';
	this.teamBox.style.position = 'absolute';
	this.teamBox.style.top = '29px';
	this.teamBox.style.left = '49px';
	this.teamBox.list = [];
	this.selectedTeamCell = null;
	var container = this.parent.create('div');
	container.style.background = 'rgba(0, 0, 0, 0.7)';
	container.style.borderTop = '2px solid rgba(255, 255, 255, 1)';
	container.style.borderBottom = '2px solid rgba(255, 255, 255, 1)';
	container.style.top = '93px';
	container.style.left = '0';
	container.style.position = 'absolute';
	container.style.width = '100%';
	container.style.height = '69%';
	var list = container.create('div');
	this.creatureBox = list.create('div');
	this.creatureBox.list = [];
	this.scroller = new iScroll(container);
	
	this.listMax = 20;
	this.teamList = []; // this is the list of team members to be sent to the server
	this.back = null;
	
	// back/save
	var self = this;
	var btn = this.parent.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px auto';
	btn.style.color = '#fff';
	btn.style.border = '2px solid #006';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#9C9CFF), to(#3232CF))';
	btn.style.width = '40px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = 'OK';
	btn.style.margin = '0';
	btn.style.top = '44px';
	btn.style.left = '5px';
	btn.style.position = 'absolute';
	button.create(btn, function () {
		var teamCount = 0;
		var team = self.teamList.map(function (item) {
			if (item && item.creature) {
				teamCount += 1;
				return item.creature.id;
			} else {
				return null;
			}
		});
		// can not have an empty team
		if (teamCount === 0) {
			return;
		}
		// team can not be empty
		if (self.teamList.length === 0) {
			return;
		}
		var origTeam = self.list.team.map(function (item) {
			if (item && item.creature) {
				return item.creature.id;
			}
			else {
				return null;
			}
		});
		var update = false;
		// check for changes
		if (JSON.stringify(team) !== JSON.stringify(origTeam)) {
			update = true;
		}
		var go = 'Main';
		if (self.back) {
			go = self.back;
		}
		if (update) {
			server.send('demo.manage_team', {type: 'reposition', team: team}, function (error, data) {
				if (error) {
					logger.warn(error);
				}
				self.update(data.my_creatures, function () {
					window.viewPort.open(go);
				});
			});
		} else {
			window.viewPort.open(go);
		}
	});
	
	// creature num 
	this.creatureNum = this.parent.create('div');
	this.creatureNum.style.fontWeight = 'bold';
	this.creatureNum.style.width = '53px';
	this.creatureNum.style.height = '20px';
	this.creatureNum.style.position = 'absolute';
	this.creatureNum.style.top = '69px';
	this.creatureNum.style.right = '2px';
	this.creatureNum.style.textAlign = 'center';
}

TeamManagement.prototype.onOpen = function (params, cb) {
	if (this.selectedTeamCell) {
		this.selectedTeamCell = null;
		this.resetTeam();
	}
	if (params && params.back) {
		this.back = params.back;
	}
	this.creatureBox.css({ display: 'none' });
	cb();
};

TeamManagement.prototype.onOpenComplete = function (params) {
	viewPort.overlay('TopMenu');
	var self = this;
	window.setTimeout(function () {
		self.displayView(); 
	}, 0);
};

TeamManagement.prototype.displayView = function () {
	var self = this;
	window.game.fadeIn.setTarget(this.creatureBox);
	window.game.fadeIn.once('start', function () {
		self.resetList();
		self.creatureBox.css({ display: 'block' });
		window.setTimeout(function () {
			self.scroller.refresh();
		}, 0);
	});
	window.game.fadeIn.start();
};

TeamManagement.prototype.update = function (data, cb) {
	viewPort.lock();
	if (data && data.creature_list) {
		this.list.creature_list = data.creature_list;
	}
	if (data && data.team) {
		this.list.team = data.team;
	}
	var preloader = [];
	for (var i = 0, len = this.list.creature_list.length; i < len; i++) {
		preloader.push(this.list.creature_list[i].sprite);
	}
	preloader.push('/img/contents/src/ui/blue-arrow.png');
	this.teamMap = {};
	this.teamList = []; 
	for (var index = 0, indexLen = this.list.team.length; index < indexLen; index++) {
		this.teamMap[this.list.team[index].creature_id] = this.list.team[index];
		this.teamList.push(this.list.team[index]);
	}
	var self = this;
	loader.asyncImg(preloader, function () {
		self.displayTeam();
		self.displayList();
		window.setTimeout(function () {
			self.scroller.refresh();
			viewPort.unlock();
			cb();
		}, 0);	
	});
};

TeamManagement.prototype.displayTeam = function () {
	// create list of team
	var size = 40;
	var margin = 5;
	for (var i = 0; i < 4; i++) {
		var item = this.teamList[i];
		if (!this.teamBox.list[i]) {
			this.teamBox.list[i] = this.teamBox.create('div');
			this.teamBox.list[i].style.cssFloat = 'left';
			this.teamBox.list[i].style.height = '52px';
			this.teamBox.list[i].style.border = '1px solid rgba(0, 0, 0, 0.5)';
			this.teamBox.list[i].index = i;
			var sprite = this.teamBox.list[i].sprite = this.teamBox.list[i].create('div');
			sprite.style.width = size + 'px';
			sprite.style.height = size + 'px';
			sprite.style.backgroundSize = '100%';
			sprite.style.padding = '5px';
			// display level
			this.teamBox.list[i].lvl = sprite.create('div');
			this.teamBox.list[i].lvl.style.fontSize = '12px';
			this.teamBox.list[i].lvl.style.color = '#fff';
			this.teamBox.list[i].lvl.style.textAlign = 'center';
			this.teamBox.list[i].lvl.style.fontWeight = 'bold';
			this.teamBox.list[i].lvl.style.lineHeight = '65px';
			this.teamBox.list[i].lvl.style.textShadow = '1px 1px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000';
			// display slot number
			this.teamBox.list[i].slot = this.teamBox.list[i].create('div');
			this.teamBox.list[i].slot.style.position = 'relative';
			this.teamBox.list[i].slot.style.top = '-6px';
			this.teamBox.list[i].slot.style.left = '40px';
			this.teamBox.list[i].slot.style.width = '10px';
			this.teamBox.list[i].slot.style.height = '8px';
			this.teamBox.list[i].slot.style.background = '-webkit-linear-gradient(top, #FFFFFF 0%, #D2D8EF 100%)';
			this.teamBox.list[i].slot.style.webkitBorderRadius = '4px 0 0 0';
			this.teamBox.list[i].slot.style.textAlign = 'center';
			this.teamBox.list[i].slot.style.color = '#333';
			this.teamBox.list[i].slot.style.fontWeight = 'bold';
			this.teamBox.list[i].slot.style.fontSize = '8px';
			this.teamBox.list[i].slot.style.lineHeight = '8px';
			this.teamBox.list[i].slot.textContent = i + 1;
		} 
		this.teamBox.list[i].style.webkitBoxShadow = '';
		this.teamBox.list[i].style.border = '1px solid rgba(0, 0, 0, 0.5)';
		// add to team/remove from team button
		var self = this;
		button.destroy(this.teamBox.list[i]);
		button.create(this.teamBox.list[i], bind(null, function (event, index, teamCreature, selectedCell) {
			if (self.selectedTeamCell) {
				// reset previously selected cell
				self.selectedTeamCell.style.webkitBoxShadow = '';
				self.selectedTeamCell.style.border = '1px solid rgba(0, 0, 0, 0.5)';
				self.selectedTeamCell = null;
			}
			self.selectedTeamCell = selectedCell;
			self.selectedTeamCell.style.webkitBoxShadow = 'inset 0 0 3px #0f0';
			self.selectedTeamCell.style.border = '1px solid #0f0';
			// redraw
			self.displayList();
			
		}, i, item, this.teamBox.list[i]));
		if (item) {
			this.teamBox.list[i].sprite.style.background = 'url(' + item.creature.sprite + ') no-repeat';
			this.teamBox.list[i].lvl.textContent = 'Lvl ' + item.creature.lvl;
			this.teamBox.list[i].style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(0, 0, 0, 0.2)), to(rgba(0, 0, 0, 0.7)))';
		} else {
			this.teamBox.list[i].sprite.style.background = '';
			this.teamBox.list[i].lvl.textContent = 'Empty';
			this.teamBox.list[i].style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, 0.2)))';
		}
	}
};

TeamManagement.prototype.displayList = function () {
	this.creatureNum.textContent = this.list.creature_list.length + ' / ' + this.listMax;
	if (this.list.creature_list.length === this.listMax) {
		this.creatureNum.style.textShadow = '1px 1px 0 #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff';
		this.creatureNum.style.color = '#f00';
	} else {
		this.creatureNum.style.textShadow = '1px 1px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000';
		this.creatureNum.style.color = '#fff';
	}
	var size = 50;
	var self = this;
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
			this.creatureBox.list[i].text.style.paddingRight = '60px';
			this.creatureBox.list[i].text.style.textAlign = 'right';
			this.creatureBox.list[i].pos = this.creatureBox.list[i].create('div');
			var btn = this.creatureBox.list[i].teamBtn = this.creatureBox.list[i].pos.create('div');
			btn.style.top = '-42px';
			btn.style.left = '10px';
			btn.style.position = 'relative';
			btn.style.color = '#fff';
			btn.style.border = '2px solid #999';
			btn.style.WebkitBorderRadius = '8px';
			btn.style.background = '#ccc';
			btn.style.width = '60px';
			btn.style.height = '30px';
			btn.style.lineHeight = '30px';
			btn.style.fontSize = '12px';
			btn.style.textAlign = 'center';
			btn.textContent = 'Add';
			var num = this.creatureBox.list[i].num = this.creatureBox.list[i].pos.create('span');
			num.style.textAlign = 'center';
			num.style.padding = '4px auto';
			num.style.color = '#ccc';
			num.style.border = '2px solid #999';
			num.style.WebkitBorderRadius = '13px';
			num.style.background = '#ccc';
			num.style.padding = '0 7px';
			num.style.height = '20px';
			num.style.lineHeight = '20px';
			num.style.margin = '5px 1px';
			num.style.position = 'relative';
			num.style.top = '-74px';
			num.style.left = '85px';
			num.textContent = '0';
			var detail = this.creatureBox.list[i].pos.detail = this.creatureBox.list[i].pos.create('span');
			detail.style.backgroundImage = 'url(/img/contents/src/ui/blue-arrow.png)';
			detail.style.backgroundPosition = 'center';
			detail.style.backgroundRepeat = 'no-repeat';
			detail.style.backgroundSize = '100%';
			detail.style.width = '30px';
			detail.style.height = '30px';
			detail.style.lineHeight = '30px';
			detail.style.padding = '15px';
			detail.style.margin = '20px 18px';
			detail.style.position = 'relative';
			detail.style.top = '-74px';
			detail.style.left = '235px';
		}
		var cell = this.creatureBox.list[i];
		button.destroy(this.creatureBox.list[i].teamBtn);
		if (this.list.creature_list[i]) {
			cell.sprite.style.background = 'url(' + this.list.creature_list[i].sprite + ')';
			cell.sprite.style.backgroundSize = 'auto 200%';
			cell.sprite.style.backgroundRepeat = 'no-repeat';
			cell.sprite.style.backgroundPosition = '50% 10%';
			cell.style.display = '';
			cell.text.innerHTML = 'Level ' + this.list.creature_list[i].lvl + '<br />' + this.list.creature_list[i].name;
			if (this.teamMap[ this.list.creature_list[i].id ]) {
				cell.text.style.textShadow = '1px 1px 0 #090, -1px -1px 0 #090, 1px -1px 0 #090, -1px 1px 0 #090';
				cell.teamBtn.style.border = '2px solid #600';
				cell.teamBtn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FF9C9C), to(#CF3232))';
				cell.teamBtn.textContent = 'Remove';
				button.create(cell.teamBtn, bind(null, function (event, selfBtn, mapIndex, sortIndex) {
					if (self.teamList[sortIndex - 1]) {
						// remove from team
						delete self.teamList[sortIndex - 1];
						// destroy button
						button.destroy(selfBtn);
						// update map
						delete self.teamMap[mapIndex];
						// reset
						self.selectedTeamCell = null;
						// redraw team
						self.displayTeam();
						self.displayList();
					}
					
				}, this.creatureBox.list[i].teamBtn, this.list.creature_list[i].id, this.teamMap[ this.list.creature_list[i].id ].sortindex));
				var order = this.teamMap[ this.list.creature_list[i].id ].sortindex;
				this.creatureBox.list[i].num.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#9C9CFF), to(#3232CF))';
				this.creatureBox.list[i].num.style.border = '2px solid #006';
				this.creatureBox.list[i].num.style.color = '#fff';
				this.creatureBox.list[i].num.textContent = order;
			} 
			else {
				this.creatureBox.list[i].text.style.textShadow = '1px 1px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000';
				this.creatureBox.list[i].teamBtn.style.border = '2px solid #999';
				this.creatureBox.list[i].teamBtn.style.background = '#ccc';
				this.creatureBox.list[i].teamBtn.textContent = 'Add';
				this.creatureBox.list[i].num.style.color = '#ccc';
				this.creatureBox.list[i].num.style.background = '#ccc';
				this.creatureBox.list[i].num.style.border = '2px solid #999';
				this.creatureBox.list[i].num.textContent = '0';
				if (this.selectedTeamCell) {
					var self = this;
					this.creatureBox.list[i].teamBtn.style.border = '2px solid #006';
					this.creatureBox.list[i].teamBtn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#9C9CFF), to(#3232CF))';
					button.create(this.creatureBox.list[i].teamBtn, bind(null, function (event, creature) {
						// reset previous
						if (self.teamList[self.selectedTeamCell.index]) {
							delete self.teamMap[ self.teamList[self.selectedTeamCell.index].creature.id ];
						}
						// add to team
						self.teamList[self.selectedTeamCell.index] = {creature: creature};	
						self.teamMap[creature.id] = {sortindex: self.selectedTeamCell.index + 1};
						// reset 
						self.selectedTeamCell = null;
						// redraw
						self.displayTeam();
						self.displayList();
					}, this.list.creature_list[i]));
				}
			}
			var detail = this.creatureBox.list[i].pos.detail;
			button.destroy(detail);
			button.create(detail, bind(null, function (event, creatureData) {
				var team = self.teamList.map(function (item) {
					return item.creature.id;
				});
				var origTeam = self.list.team.map(function (item) {
					return item.creature.id;
				});
				var update = false;
				// check for changes
				if (JSON.stringify(team) !== JSON.stringify(origTeam)) {
					update = true;
				}
				if (update) {
					server.send('demo.manage_team', {type: 'reposition', team: team}, function (error, data) {
						if (error) {
							logger.warn(error);
						}
						self.updateData = true;
						viewPort.open('Creature', { back: 'TeamManagement', creatureData: creatureData });
					});
				} else {
					viewPort.open('Creature', { back: 'TeamManagement', creatureData: creatureData });
				}	
			}, this.list.creature_list[i]));
		}
		else {
			cell.style.display = 'none';	
		}
	}
};


TeamManagement.prototype.resetTeam = function () {
	for (var i = 0, len = this.teamBox.list.length; i < len; i++) {
		this.teamBox.list[i].style.webkitBoxShadow = '';
		this.teamBox.list[i].style.border = '1px solid rgba(0, 0, 0, 0.5)';
	}
};

TeamManagement.prototype.resetList = function () {
	for (var i = 0, len = this.creatureBox.list.length; i < len; i++) {
		if (!this.list.creature_list[i] || !this.teamMap[ this.list.creature_list[i].id ]) {
			this.creatureBox.list[i].text.style.textShadow = '1px 1px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000';
			this.creatureBox.list[i].teamBtn.style.border = '2px solid #999';
			this.creatureBox.list[i].teamBtn.style.background = '#ccc';
			this.creatureBox.list[i].teamBtn.textContent = 'Add';
			this.creatureBox.list[i].num.style.color = '#ccc';
			this.creatureBox.list[i].num.style.background = '#ccc';
			this.creatureBox.list[i].num.style.border = '2px solid #999';
			this.creatureBox.list[i].num.textContent = '0';
			button.destroy(this.creatureBox.list[i].teamBtn);
		}
	}
};

