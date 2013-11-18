function Creature (viewElement) {
	this.parent = viewElement;
	this.parent.style.background = 'url(/img/contents/src/background/colorful.jpg)';
	this.parent.css({ backgroundSize: '100% 100%' });
	// creature category
	this.cat = this.parent.create('div');
	this.cat.css({
		width: '200px',
		height: '30px',
		marginTop: '5px',
		marginLeft: 'auto',
		marginRight: 'auto',
		color: '#fff',
		fontSize: '14px',
		fontWeight: 'bold',
		textShadow: '1px 1px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000',
		textAlign: 'center',
		lineHeight: '30px'
	});
	// back
	var self = this;
	var back = 'TeamManagement';
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
		window.viewPort.open(self.back);
		self.back = 'TeamManagement';
		
	});
	// release
	var self = this;
	var btn = this.parent.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px auto';
	btn.style.color = '#fff';
	btn.style.border = '2px solid #333';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#999), to(#666))';
	btn.style.width = '70px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = 'Release';
	btn.style.margin = '0';
	btn.style.top = '39px';
	btn.style.left = '240px';
	btn.style.position = 'absolute';
	var msg = '<div style="maring: 0; padding: 0; font-size: 15px; line-height: 19px;">Do you want to release "$name"<br /> for <strong style="color: #09f;">$price</strong> Credit?</div>';
	var yes = '<span id="release-yes" style="position: relative; top: 20px; color: #c00; font-size: 20px; line-height: 19px;">Yes</span>';
	var no = '<span id="release-no" style="position: relative; top: 20px; color: #0c0; font-size: 20px; line-height: 19px;">No</span>';
	var space = '<span style="position: relative; top: 20px; color: #0c0; font-size: 20px; line-height: 19px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
	button.create(btn, function () {
		window.game.openLightBox(msg.replace('$name', self.creature.name).replace('$price', Math.max(1, Math.floor(self.creature.lvl / 2))) + no + space + yes, '#999');
		var yesB = window.document.find('#release-yes');
		button.destroy(yesB);
		button.create(yesB, function () {
			button.destroy(yesB);
			window.game.closeLightBox();
			server.send('demo.remove_user_creature', { creature_id: self.creature.id }, function (error, res) {
				if (error) {
					console.warn(error);
				}
				window.viewPort.open(self.back);
				self.back = 'TeamManagement';
			});
		});
		var noB = window.document.find('#release-no');
		button.destroy(noB);
		button.create(noB, window.game.closeLightBox);
	});
	// ui image list
	this.imageList = {
		fire: '/img/contents/src/ui/fire.png',
		elec: '/img/contents/src/ui/elec.png',
		ice: '/img/contents/src/ui/ice.png',
		wind: '/img/contents/src/ui/wind.png',
		light: '/img/contents/src/ui/light.png',
		dark: '/img/contents/src/ui/dark.png',
		almighty: '/img/contents/src/ui/almighty.png',
		phys: '/img/contents/src/ui/phys.png',
		passive: '/img/contents/src/ui/exclamation.png'
	};
	
	this.creature = null;
	this.attackTypes = null;
	this.stage = {};
	this.params = null;
	this.ignoreList = [];
	this.stage.release = btn;
}

Creature.prototype.onOpen = function (params, cb) {	
	if (params && params.back) {
		this.back = params.back;
	} else {
		this.back = 'TeamManagement';
	}
	this.params = params;
	this.creature = params.creatureData;
	this.ignoreList = viewPort.views.TeamManagement.getProperty('teamList').map(function (creature) {
		return creature.creature_id;
	});
	this.ignoreList.push(this.creature.id);
	var preloader = [];
	var loader = new Loader();
	preloader.push(this.creature.sprite);
	for (var i in this.imageList) {
		preloader.push(this.imageList[i]);
	}
	loader.asyncImg(preloader, function () {
		viewPort.closeOverlay('TopMenu');
		cb();
	});
};

Creature.prototype.onOpenComplete = function (params) {
	this.display();
	if (params && params.popup) {
		params.popup();
		params.popup = null;
	}
};

Creature.prototype.display = function () {
	var regColor = '1px 1px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000';
	var upColor = '1px 1px 0 #090, -1px -1px 0 #090, 1px -1px 0 #090, -1px 1px 0 #090';
	var downColor = '1px 1px 0 #900, -1px -1px 0 #900, 1px -1px 0 #900, -1px 1px 0 #900';
	var statColor = '1px 1px 0 #060, -1px -1px 0 #060, 1px -1px 0 #060, -1px 1px 0 #060';
	var levelColor = '1px 1px 0 #660, -1px -1px 0 #660, 1px -1px 0 #660, -1px 1px 0 #660';
	// release button
	if (this.params && this.params.noRelease) {
		this.stage.release.style.display = 'none';
	}
	else {
		this.stage.release.style.display = '';
	}
	// monster category
	this.cat.textContent = getCategoryDisplay(this.creature.active);
	this.cat.css({ background: getCategoryBg(this.creature.active) });
	// monster name
	if (!this.stage.name) {
		var name = this.parent.create('div');
		name.style.fontSize = '18px';
		name.style.fontWeight = 'bold';
		name.style.color = '#fff';
		name.style.position = 'absolute';
		name.style.top = '49px';
		name.style.left = '80px';
		name.style.width = '150px';
		name.style.textAlign = 'center';
		this.stage.name = name;
	}
	this.stage.name.textContent = this.creature.name;
	this.stage.name.style.textShadow = regColor
	// monster sprite
	if (!this.stage.sprite) {
		var sprite = this.parent.create('img');
		sprite.style.position = 'absolute';
		sprite.style.top = '79px';
		sprite.style.left = '80px';
		sprite.style.width = '150px';
		this.stage.sprite = sprite;	
	}
	this.stage.sprite.setAttribute('src', this.creature.sprite);
	// monster attack types
	if (!this.attackTypes) {
		var at = this.parent.create('div');
		at.style.position = 'absolute';
		at.style.top = '79px';
		at.style.left = '240px';
		at.style.width = '70px';
		at.style.height = '140px';
		this.attackTypes = at;
		var label = at.create('div');
		label.css({
			textAlign: 'center',
			color: '#fff',
			fontSize: '8px',
			fontWeight: 'bold',
			textShadow: regColor
		});
		label.textContent = 'Attack Proficiency';
	}
	setupAttackTypes(this.attackTypes, this.creature.attack_types, this.imageList);
	// monster level
	if (!this.stage.level) {
		var level = this.parent.create('div');
		level.style.color = '#fd0';
		level.style.textShadow = levelColor;
		level.style.fontWeight = 'bold';
		level.style.position = 'absolute';
		level.style.top = '99px';
		level.style.left = '15px';
		level.textContent = 'Level ';
		level.data = level.create('span');
		level.data.style.color = '#fff';
		this.stage.level = level;
	}
	this.stage.level.data.textContent = this.creature.lvl;
	this.stage.level.data.style.textShadow = regColor;
	// monster hp
	if (!this.stage.hp) {
		var hp = this.parent.create('div');
		hp.style.fontSize = '12px';
		hp.style.color = '#24D69B';
		hp.style.textShadow = statColor;
		hp.style.fontWeight = 'bold';
		hp.style.position = 'absolute';
		hp.style.top = '129px';
		hp.style.left = '15px';
		hp.textContent = 'HP:  ';
		hp.data = hp.create('span');
		hp.data.style.color = '#fff';
		this.stage.hp = hp;
	}
	this.stage.hp.data.textContent = this.creature.hp;
	this.stage.hp.data.style.textShadow = regColor;
	// monster attack
	if (!this.stage.attack) {
		var attack = this.parent.create('div');
		attack.style.fontSize = '12px';
		attack.style.color = '#24D69B';
		attack.style.textShadow = statColor;
		attack.style.fontWeight = 'bold';
		attack.style.position = 'absolute';
		attack.style.top = '142px';
		attack.style.left = '15px';
		attack.textContent = 'ATK: ';
		attack.data = attack.create('span');
		attack.data.style.color = '#fff';
		this.stage.attack = attack;
	}
	this.stage.attack.data.textContent = this.creature.atk;
	this.stage.attack.data.style.textShadow = regColor;
	// monster magic attack
	if (!this.stage.mattack) {
		var mattack = this.parent.create('div');
		mattack.style.fontSize = '12px';
		mattack.style.color = '#24D69B';
		mattack.style.textShadow = statColor;
		mattack.style.fontWeight = 'bold';
		mattack.style.position = 'absolute';
		mattack.style.top = '154px';
		mattack.style.left = '15px';
		mattack.textContent = 'M.ATK: ';
		mattack.data = mattack.create('span');
		mattack.data.style.color = '#fff';
		this.stage.mattack = mattack;
	}
	this.stage.mattack.data.textContent = this.creature.matk;
	this.stage.mattack.data.style.textShadow = regColor;
	// monster defense
	if (!this.stage.def) {
		var def = this.parent.create('div');
		def.style.fontSize = '12px';
		def.style.color = '#24D69B';
		def.style.textShadow = statColor;
		def.style.fontWeight = 'bold';
		def.style.position = 'absolute';
		def.style.top = '166px';
		def.style.left = '15px';
		def.textContent = 'DEF: ';
		def.data = def.create('span');
		def.data.style.color = '#fff';
		this.stage.def = def;
	}
	this.stage.def.data.textContent = this.creature.def;
	this.stage.def.data.style.textShadow = regColor;
	// monster magic defense
	if (!this.stage.mdef) {
		var mdef = this.parent.create('div');
		mdef.style.fontSize = '12px';
		mdef.style.color = '#24D69B';
		mdef.style.textShadow = statColor;
		mdef.style.fontWeight = 'bold';
		mdef.style.position = 'absolute';
		mdef.style.top = '179px';
		mdef.style.left = '15px';
		mdef.textContent = 'M.DEF: ';
		mdef.data = mdef.create('span');
		mdef.data.style.color = '#fff';
		this.stage.mdef = mdef;
	}
	this.stage.mdef.data.textContent = this.creature.mdef;
	this.stage.mdef.data.style.textShadow = regColor;
	// monster speed
	if (!this.stage.spd) {
		var spd = this.parent.create('div');
		spd.style.fontSize = '12px';
		spd.style.color = '#24D69B';
		spd.style.textShadow = statColor;
		spd.style.fontWeight = 'bold';
		spd.style.position = 'absolute';
		spd.style.top = '192px';
		spd.style.left = '15px';
		spd.textContent = 'SPEED: ';
		spd.data = spd.create('span');
		spd.data.style.color = '#fff';
		this.stage.spd = spd;
	}
	this.stage.spd.data.textContent = this.creature.spd;
	this.stage.spd.data.style.textShadow = regColor;
	// attributes
	var list = ['fire', 'ice', 'elec', 'wind', 'light', 'dark', 'phys', 'almighty'];
	if (!this.stage.att) {
		var att = this.parent.create('div');
		att.style.height = '30px';
		att.style.width = '200px';
		att.style.position = 'absolute';
		att.style.top = '229px';
		att.style.left = '10px';
		att.list = {};
		for (var i = 0, len = list.length; i < len; i++) {
			var icon = att.list[i] = att.create('div');
			icon.type = list[i];
			icon.style.margin = '2px';
			icon.style.width = '20px';
			icon.style.height = '20px';
			icon.style.lineHeight = '20px';
			icon.style.background = 'url(' + this.imageList[ list[i] ] + ') no-repeat';
			icon.style.backgroundSize = '100%';
			icon.data = icon.create('span');
			icon.data.style.lineHeight = '23px';
			icon.data.style.fontSize = '12px';
			icon.data.style.color = '#fff';
			icon.data.style.fontWeight = 'bold';
			icon.data.style.paddingLeft = '23px';
		}
		this.stage.att = att;
	}
	var attributes = this.creature.attributes;
	if (!attributes.length) {
		attributes = [attributes];
	} 
	var alen = attributes.length;
	for (var i = 0, len = list.length; i < len; i++) {
		var item = this.stage.att.list[i];
		var value = 'Normal';
		var shadow = regColor;
		for (var k = 0; k < alen; k++) {
			if (item.type === attributes[k].element) {
				var num = Number(attributes[k].attribute);
				if (num < 1) {
					if (num < 0.5) {
						value = 'Block';
					} 
					else {
						value = 'Resist';
					}
					shadow = upColor;
				}
				else if (num > 1 && num <= 2) {
					value = 'Weak';
					shadow = downColor;
				}
				else if (num > 2) {
					value = 'Valnerable';
					shadow = downColor;
				}
				break;
			}			
		}
		item.data.textContent = value;
		item.data.style.textShadow = shadow;
	}
	// skills
	var maxSkills = 8;
	if (!this.stage.skills) {
		this.stage.skills = this.parent.create('div');
		this.stage.skills.style.position = 'absolute';
		this.stage.skills.style.top = '220px';
		this.stage.skills.style.left = '98px';
		this.stage.skills.list = [];
		for (var i = 0, len = maxSkills; i < len; i++) {
			var skill = this.stage.skills.list[i] = this.stage.skills.create('div');
			skill.css({
				background: 'rgba(0, 0, 0, 0.2)',
				borderBottom: '1px solid #ccc',
				width: '140px',
				height: '23px',
			});
			skill.data = skill.create('div');
			skill.data.css({
				backgroundSize: '20px 20px',
				lineHeight: '23px',
				fontSize: '12px',
				color: '#fff',
				fontWeight: 'bold',
				paddingLeft: '23px',
				textShadow: regColor
			});
		}
	}
	var skills = this.creature.skills;
	if (!skills.length) {
		skills = [skills];
	}
	for (var i = 0, len = maxSkills; i < len; i++) {
		if (skills[i]) {
			this.stage.skills.list[i].data.style.display = '';
			if (skills[i] && skills[i].skill && skills[i].skill.type === 'command') {
				this.stage.skills.list[i].data.style.background = 'url(' + this.imageList[ skills[i].skill.element ] + ') no-repeat';
				this.stage.skills.list[i].data.textContent = skills[i].skill.name + ' : ';
				this.stage.skills.list[i].data.textContent += (Number(skills[i].skill.hits) > 1) ? skills[i].skill.power + ' to ' + (Number(skills[i].skill.hits) * skills[i].skill.power) : Number(skills[i].skill.hits) * skills[i].skill.power;
			} else if (skills[i] && skills[i].skill) {
				this.stage.skills.list[i].data.style.background = 'url(' + this.imageList.passive + ') no-repeat';
				this.stage.skills.list[i].data.textContent = skills[i].skill.name;
			}
			// if there is any description
			button.destroy(this.stage.skills.list[i].data);
			if (skills[i] && skills[i].skill && skills[i].skill.description) {
				var self = this;
				button.create(this.stage.skills.list[i].data, bind(null, function (event, skillData, skill) {
					var name = skill.name;
					var desc = skill.description;
					var descMsg = '<p style="padding: 0; margin: 0; font-size: 15px; line-height: 20px;"><strong style="color: #09f; font-size: 19px;">' + name + '</strong><br />' + desc + '</p>';
					var sb = (self.params && self.params.showBuy) ? self.params.showBuy : false;
					if (!sb && skills.length >= maxSkills) {
						var descMsg = '<p style="padding: 0; margin: 0; font-size: 12px; line-height: 12px;"><strong style="color: #09f; font-size: 12px;">' + name + '</strong><br />' + desc + '</p>';
						var msg = '<div style="maring: 0; padding: 0; margin-top: 10px; font-size: 10px; line-height: 12px;">Remove "' + name + '" for <strong style="color: #09f;">100</strong> Credit?</div>';
						var yes = '<span id="release-yes" style="position: relative; top: 10px; color: #c00; font-size: 20px; line-height: 19px;">Yes</span>';
						var no = '<span id="release-no" style="position: relative; top: 10px; color: #0c0; font-size: 20px; line-height: 19px;">No</span>';
						var space = '<span style="position: relative; top: 20px; color: #0c0; font-size: 20px; line-height: 19px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
						descMsg += msg + no + space + yes;
						viewPort.overlay('TopMenu');
						viewPort.views.LightBox.lightBoxCallback = function () {
							viewPort.closeOverlay('TopMenu');
						};
						window.game.openLightBox(descMsg, '#009');
						var yesB = window.document.find('#release-yes');
						button.destroy(yesB);
						button.create(yesB, function () {
							button.destroy(yesB);
							window.game.closeLightBox(function () {
								server.send('demo.remove_skill', { skillId: skillData.id }, function (error, res) {
									if (error) {
										return console.warn(error);
									}
									if (res.error) {
										var html = '<span style="maring: 0; padding: 0; font-size: 20px; line-height: 40px;">Failed to remove the skill.</span>';
										var color = '#666';
										if (res.error === 'lastSkill') {
											html = '<span style="maring: 0; padding: 0; font-size: 20px; line-height: 40px;">Cannot remove more skill.</span>';
										} else if (res.error === 'notEnoughCredit') {
											html = '<span style="maring: 0; padding: 0; font-size: 20px; line-height: 40px;">Not enough Credit.</span>';
										}
										window.setTimeout(function () {
											window.game.openLightBox(html, color);
										}, 0);
									} else {								
										window.viewPort.open('Creature', { creatureData: res.creature, back: self.back });
									}
								});
							});
						});
					} else {
						window.game.openLightBox(descMsg, '#009');
					}
				}, skills[i], skills[i].skill));
			}
			// adjust font size
			if (this.stage.skills.list[i].data.textContent.length >= 16) {
				this.stage.skills.list[i].data.style.fontSize = '9px';
			}
			else {
				this.stage.skills.list[i].data.style.fontSize = '12px';
			}
		} else {
			this.stage.skills.list[i].data.style.display = 'none';
		}
	}
	// buy
	if (!this.stage.buy) {
		var btn = this.parent.create('div');
		btn.style.textAlign = 'center';
		btn.style.padding = '4px auto';
		btn.style.color = '#fff';
		btn.style.border = '2px solid #965009';
		btn.style.WebkitBorderRadius = '8px';
		btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FFEF0D), to(#946A08))';
		btn.style.width = '70px';
		btn.style.height = '30px';
		btn.style.lineHeight = '30px';
		btn.textContent = 'Buy';
		btn.style.margin = '0';
		btn.style.top = '239px';
		btn.style.left = '240px';
		btn.style.position = 'absolute';
		this.stage.buy = btn;
	}	
	if (this.params && this.params.showBuy) {
		this.stage.buy.style.display = '';
		var self = this;
		var btn = this.stage.buy;
		var msg = '<div style="maring: 0; padding: 0; font-size: 15px; line-height: 19px;">Buy "$name" for <br /><strong style="color: #09f;">$price</strong> Credit?</div>';
		var yes = '<span id="release-yes" style="position: relative; top: 20px; color: #c00; font-size: 20px; line-height: 19px;">Yes</span>';
		var no = '<span id="release-no" style="position: relative; top: 20px; color: #0c0; font-size: 20px; line-height: 19px;">No</span>';
		var space = '<span style="position: relative; top: 20px; color: #0c0; font-size: 20px; line-height: 19px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
		button.destroy(btn);
		button.create(btn, function () {
			viewPort.overlay('TopMenu');
			viewPort.views.LightBox.lightBoxCallback = function () {
				viewPort.closeOverlay('TopMenu');
			};
			window.game.openLightBox(msg.replace('$name', self.creature.name).replace('$price', self.creature.price) + no + space + yes, '#999');
			var yesB = window.document.find('#release-yes');
			button.destroy(yesB);
			button.create(yesB, function () {
				button.destroy(yesB);
				window.game.closeLightBox(function () {
					// call server to buy
					buyCreature(self.creature);
				});
			});
			var noB = window.document.find('#release-no');
			button.destroy(noB);
			button.create(noB, function () {
				viewPort.closeOverlay('TopMenu');
				window.game.closeLightBox();
			});
		});
	} else {
		this.stage.buy.style.display = 'none';
	}
	// train
	var self = this;
	if (!this.stage.train) {
		var btn = this.parent.create('div');
		btn.style.textAlign = 'center';
		btn.style.padding = '4px auto';
		btn.style.color = '#fff';
		btn.style.border = '2px solid #13834D';
		btn.style.WebkitBorderRadius = '8px';
		btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#22B16B), to(#115E48))';
		btn.style.width = '70px';
		btn.style.height = '30px';
		btn.style.lineHeight = '30px';
		btn.textContent = 'Train';
		btn.style.margin = '0';
		btn.style.top = '239px';
		btn.style.left = '240px';
		btn.style.position = 'absolute';
		this.stage.train = btn;
	}
	if (this.params && this.params.noTrain) {
		this.stage.train.style.display = 'none';
	}
	else {
		var btn = this.stage.train;
		var msg = '<div style="maring: 0; padding: 0; font-size: 15px; line-height: 19px;">Train "$name" for <br /><strong style="color: #09f;">$price</strong> Credit?</div>';
		var yes = '<span id="release-yes" style="position: relative; top: 20px; color: #c00; font-size: 20px; line-height: 19px;">Yes</span>';
		var no = '<span id="release-no" style="position: relative; top: 20px; color: #0c0; font-size: 20px; line-height: 19px;">No</span>';
		var space = '<span style="position: relative; top: 20px; color: #0c0; font-size: 20px; line-height: 19px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
		button.destroy(btn);
		button.create(btn, function () {
			viewPort.overlay('TopMenu');
			viewPort.views.LightBox.lightBoxCallback = function () {
				viewPort.closeOverlay('TopMenu');
			};
			window.game.openLightBox(msg.replace('$name', self.creature.name).replace('$price', self.creature.trainingCost) + no + space + yes, '#999');
			var yesB = window.document.find('#release-yes');
			button.destroy(yesB);
			button.create(yesB, function () {
				button.destroy(yesB);
				window.game.closeLightBox(function () {
					// call server to buy
					self.train(self.creature);
				});
			});
			var noB = window.document.find('#release-no');
			button.destroy(noB);
			button.create(noB, function () {
				viewPort.closeOverlay('TopMenu');
				window.game.closeLightBox();
			});
			
		});
		this.stage.train.style.display = '';
	}
	// inherit
	var self = this;
	if (!this.stage.boost) {
		var btn = this.parent.create('div');
		btn.style.textAlign = 'center';
		btn.style.padding = '4px auto';
		btn.style.color = '#fff';
		btn.style.border = '2px solid #6A14FC';
		btn.style.WebkitBorderRadius = '8px';
		btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#500FBF), to(#0F0233))';
		btn.style.width = '70px';
		btn.style.height = '30px';
		btn.style.lineHeight = '30px';
		btn.textContent = 'Inherit';
		btn.style.margin = '0';
		btn.style.top = '289px';
		btn.style.left = '240px';
		btn.style.position = 'absolute';
		this.stage.boost = btn;
	}
	if (this.params && this.params.noBoost) {
		this.stage.boost.style.display = 'none';
	}
	else {
		var btn = this.stage.boost;
		button.destroy(btn);
		button.create(btn, function () {
			var header = '<div style="width: 245px; height: 50px; background: url(' + self.creature.sprite + ') no-repeat; background-size: auto 200%; background-position: 0 10%; font-size: 12px; text-align: right; line-height: 26px; color: #fff; text-shadow: ' + regColor + '; font-weight: bold;">Level&nbsp;' + self.creature.lvl + '&nbsp;<br />Skill Inheritance&nbsp;(' + self.creature.name + ')&nbsp;</div>';
			viewPort.open('CreatureList', { back: { viewName: 'Creature', params: self.params }, ignoreCreatureIds: self.ignoreList, headerHTML: header, onSelect: self.inherit });
		});
		this.stage.boost.style.display = '';
	}
	// fusion
	if (!this.stage.fusion) {
		var btn = this.parent.create('div');
		btn.style.textAlign = 'center';
		btn.style.padding = '4px auto';
		btn.style.color = '#fff';
		btn.style.border = '2px solid #FF8800';
		btn.style.WebkitBorderRadius = '8px';
		btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FFBB00), to(#822A04), color-stop(.6,#991602))';
		btn.style.width = '70px';
		btn.style.height = '30px';
		btn.style.lineHeight = '30px';
		btn.textContent = 'Fusion';
		btn.style.margin = '0';
		btn.style.top = '339px';
		btn.style.left = '240px';
		btn.style.position = 'absolute';
		this.stage.fusion = btn;
	}	
	if (this.params && this.params.noFusion) {
		this.stage.fusion.style.display = 'none';
	}
	else {
		var btn = this.stage.fusion;
		button.destroy(btn);
		button.create(btn, function () {
			var header = '<div style="width: 245px; height: 50px; background: url(' + self.creature.sprite + ') no-repeat; background-size: auto 200%; background-position: 0 10%; font-size: 12px; text-align: right; line-height: 26px; color: #fff; text-shadow: ' + regColor + '; font-weight: bold;">Level&nbsp;' + self.creature.lvl + '&nbsp;<br />Fusion&nbsp;(' + self.creature.name + ')&nbsp;</div>';
			viewPort.open('CreatureList', { back: { viewName: 'Creature', params: self.params }, ignoreCreatureIds: self.ignoreList, headerHTML: header, onSelect: self.fusion });
		});
		this.stage.fusion.style.display = '';
	}
};

Creature.prototype.train = function (creature) {
	var self = this;
	server.send('demo.train', { creatureId: creature.id }, function (error, res) {
		if (error) {
			return console.error(error);
		}
		var msg = null;
		var color = '#666';
		if (res.invalidCreature) {
			msg = 'You can not train this creature.';
		} else if (res.notEnoughCredit) {
			msg = 'Not enough credit.';
		} else if (res.levelTooLow) {
			msg = 'Your level is too low for training.';
		}
		var html = '<p style="font-size: 15px; line-height: 19px;">' + msg + '</p>';
		if (msg) {
			// error
			return window.game.openLightBox(html, color);
		}
		// show popup with updated stats
		var updated = {};
		var targetCreature = self.creature;
		// check for level up
		if (res.creature.lvl > targetCreature.lvl) {
			updated.lvl = res.creature.lvl - targetCreature.lvl;
		}
		if (res.creature.atk > targetCreature.atk) {
			updated.atk = res.creature.atk - targetCreature.atk;
		}
		if (res.creature.matk > targetCreature.matk) {
			updated.matk = res.creature.matk - targetCreature.matk;
		}
		if (res.creature.def > targetCreature.def) {
			updated.def = res.creature.def - targetCreature.def;
		}
		if (res.creature.mdef > targetCreature.mdef) {
			updated.mdef = res.creature.mdef - targetCreature.mdef;
		}
		if (res.creature.spd > targetCreature.spd) {
			updated.spd = res.creature.spd - targetCreature.spd;
		}
		var showPopUp = false;
		var statColor = '1px 1px 0 #060, -1px -1px 0 #060, 1px -1px 0 #060, -1px 1px 0 #060';
		var str = '<div style="margin: 0; padding: 0; font-size: 18px; line-height: 24px;">';
		// level up
		var stats = { lvl: true, atk: true, matk: true, def: true, mdef: true, spd: true };
		str += '<div style="margin-bottom: 10px;">Level Up</div><div style="width: 50px; margin-left: auto; margin-right: auto;">';
		for (var stat in stats) {
			var value = '<span style="color: #ccc;">0</span>';
			if (updated[stat]) {
				value = '<span style="color: #09f;">' + updated[stat] + '</span>';
			}
			if (stat === 'lvl') {
				stat = 'level';
			}
			str += '<div style="text-align: left; font-size: 10px; line-height: 12px;"><span style="color: #24D69B; textShadow: ' + statColor + ';">' + stat.replace('m', 'm.').toUpperCase() + '</span>&nbsp;&nbsp;+' + value + '</div>';
		}
		str += '</div>';
		var showPopup = function () {
			window.setTimeout(function () {
				window.game.openLightBox(str, '#6A14FC');
			}, 0);
		};
		viewPort.open('Creature', { creatureData: res.creature, popup: showPopup, back: self.back });
	});
};

Creature.prototype.inherit = function (event, index, creature) {
	var self = viewPort.views.Creature;
	server.send('demo.inherit_skill', { base_id: self.creature.id, sacrifice_id: creature.id }, function (error, res) {
		if (error) {
			return console.error(error);
		}
		var showPopUp = true;
		var statColor = '1px 1px 0 #060, -1px -1px 0 #060, 1px -1px 0 #060, -1px 1px 0 #060';
		var str = '<div style="margin: 0; padding: 0; font-size: 18px; line-height: 24px;">';
		if (res.error) {
			// TODO show error message in a popup
			var msg = 'Failed to inherit.';
			if (res.error === 'noSkillToInherit') {
				msg = 'No skill to inherit...';
			}	
			window.game.openLightBox('<div style="font-size: 20px; line-height: 100px;">' + msg + '</div>', '#666');
			return;
		}
		str += '<div>New Skill Inherited</div>';
		str += '<div style="background-size: auto 100%; font-size: 15px; line-height: 18px; height; 18px; color: #24D69B; margin-top: 20px; textShadow: ' + statColor + ';">' + res.newSkill + '</div>';		
		str += '</div>';
		var showPopup = function () {
			if (showPopUp) {
				window.setTimeout(function () {
					window.game.openLightBox(str, '#6A14FC');
				}, 0);
			}
		};
		var data = { 
			type: 'boost',
			creatureA: viewPort.views.Creature.getProperty('creature').sprite, 
			creatureB: creature.sprite, 
			creatureResult: self.creature.sprite,
			returnView: { name: 'Creature', params: {back: 'TeamManagement', creatureData: res.creature, popup: showPopup} }
		};
		viewPort.open('Fusion', data);
		
	});
};

Creature.prototype.fusion = function (event, index, creature) {
	var self = viewPort.views.Creature;
	server.send('demo.creature_fusion', { a_id: self.creature.id, b_id: creature.id }, function (error, res) {
		if (error) {
			return console.error(error);	
		}
		if (res && res.success === false) {
			if (res.lowLevel) {
				return window.game.openLightBox('<p style="maring: 0; padding: 0; font-size: 18px; line-height: 60px;">Your level is too low!</p>', '#999');	
			}
			// fusion failed
			return window.game.openLightBox('<p style="maring: 0; padding: 0; font-size: 18px; line-height: 60px;">Cannot perform fusion!</p>', '#999');
		}
		var data = { 
			type: 'fusion', 
			creatureA: self.creature.sprite, 
			creatureB: creature.sprite, 
			creatureResult: res.creature.sprite,
			returnView: { name: 'Creature', params: {back: 'TeamManagement', creatureData: res.creature} }
		};
		viewPort.open('Fusion', data);
	});
};

function buyCreature(creature) {
	window.setTimeout(function () {
		if (Number(creature.price) > game.session.data.credit) {
			// not enough credit
			game.openLightBox('<span style="font-size: 20px;">Not enough Credit!</span>', '#666');
		} else {
			// try to buy
			server.send('demo.buy_creature', { identifier: creature.identifier }, function (error, res) {
				if (error) {
					return console.error(error);
				} 
				var html = '<span style="maring: 0; padding: 0; font-size: 15px; line-height: 40px;">You bought <br /><strong style="color: #66f">' + creature.name + '</strong></span>';
				var color = '#06f';
				if (res.tooMany) {
					html = '<span style="maring: 0; padding: 0; font-size: 15px; line-height: 40px;">You have too many monsters</span>';
					color = '#666';
				} else if (res.notEnoughCredit) {
					html = '<span style="font-size: 20px;">Not enough Credit!</span>';
					color = '#666';
				}
				// done
				game.openLightBox(html, color);
				// update
				//window.viewPort.views.TopMenu.update();
			});
		}
	}, 0);
	viewPort.closeOverlay('TopMenu');
}

function getCategoryDisplay(cat) {
	if (cat === 1) {
		return '';
	} else if (cat === 0) {
		return 'Rare Hunt';
	} else if (cat === 3) {
		return 'Special Fusion';
	} else if (cat === 4) {
		return 'Seasonal Hunt';
	} else if (cat === 5) {
		return 'Boss Hunt';
	} else {
		return '';
	}
}

function getCategoryBg(cat) {
	if (cat === 1) {
		return '';
	} else if (cat === 0) {
		return '-webkit-gradient(linear, 100% 100%, 0% 100%, from(rgba(0, 150, 100, 0)), to(rgba(0, 150, 100, 0)), color-stop(0.5, rgba(0, 150, 100, 0.5)))';
	} else if (cat === 3) {
		return '-webkit-gradient(linear, 100% 100%, 0% 100%, from(rgba(0, 15, 200, 0)), to(rgba(0, 15, 200, 0)), color-stop(0.5, rgba(0, 15, 200, 0.5)))';
	} else if (cat === 4) {
		return '-webkit-gradient(linear, 100% 100%, 0% 100%, from(rgba(200, 15, 0, 0)), to(rgba(200, 15, 0, 0)), color-stop(0.5, rgba(200, 15, 0, 0.7)))';
	}
	else if (cat === 5) {
		return '-webkit-gradient(linear, 100% 100%, 0% 100%, from(rgba(255, 0, 0, 0)), to(rgba(255, 0, 0, 0)), color-stop(0.5, rgba(255, 0, 0, 0.7)))';
	}
}

function setupAttackTypes(at, attackTypes, imageList) {
	var size = 15;
	var height = 9;
	var offset = 4;
	var full = 22;
	for (var type in attackTypes) {
		var value = attackTypes[type];
		if (!at[type]) {
			var div = at.create('div');
			div.css({
				width: '70px',
				height: size + 'px',
				background: 'url(' + imageList[type] + ') no-repeat',
				backgroundSize: size + 'px ' + size + 'px'
			});
			var container = div.create('div');
			container.css({
				width: '50px',
				height: height + 'px',
				lineHeight: height + 'px',
				position: 'relative',
				left: size + 'px',
				top: offset + 'px'
			});
			var core = container.create('div');
			core.css({
				background: '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#3369FF), to(#03084A))',
				height: height + 'px',
				width: full + 'px',
				WebkitTransformOrigin: 0
			});
			container.core = core;
			var boost = container.create('div');
			boost.css({
				height: height + 'px',
				width: full + 'px',
				WebkitTransformOrigin: 0,
				position: 'relative',
				top: '-' + height + 'px',
				opacity: 1
			});
			container.boost = boost;
			at[type] = container;
		}
		var core = at[type].core;
		core.css({
			WebkitTransform: 'scale(' + Math.min(1, value) + ', 1)',
		});
		var boost = at[type].boost;
		var diff = value - 1;
		var pos = full;
		var color = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#54FF47), to(#061205))';
		var opt = 1;
		if (diff < 0) {
			color = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FF4B45), to(#120000))';
			diff = -1 * diff;
			pos = full - (full * diff);
			opt = 0.3;
		}
		boost.css({
			background: color,
			left: pos + 'px',
			WebkitTransform: 'scale(' + diff + ', 1)',
			opacity: opt
		});
	}
}


















