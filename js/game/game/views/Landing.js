function Landing(viewElement) {
	var self = this;
	var outline = '1px 1px 0 rgba(255, 255, 255, 0.5), -1px -1px 0 rgba(255, 255, 255, 0.5), 1px -1px 0 rgba(255, 255, 255, 0.5), -1px 1px 0 rgba(255, 255, 255, 0.5)';
	this.loginKey = 'BT:login';
	this.parent = viewElement;
	this.parent.style.background = '#fff';
	// title
	var title = this.parent.create('div');
	title.style.fontSize = '35px';
	title.style.textAlign = 'center';
	title.style.position = 'absolute';
	title.style.top = '100px';
	title.fontFamily = 'Georgia';
	title.fontWeight = '900';
	title.style.color = '#444';
	title.style.width = '100%';
	title.textContent = 'Happy Hunting';
	title.style.textShadow = outline;
	// sign up view
	this.signupView = this.parent.create('div');
	var container = this.signupView.create('div');
	container.className = 'container';
	container.style.position = 'absolute';
	container.style.top = '160px';
	container.style.width = '310px';
	container.style.margin = '0 auto';
	var form = container.create('form');
	var fields = [ {name: 'Name', type: 'text'}, { name: 'Password', type: 'password'}, { name: 'Confirm Password', type: 'password'} ];
	var inputs = {};
	for (var i = 0; i < fields.length; i++) {
		var d = form.create('div');
		d.style.textAlign = 'right';
		d.style.marginBottom = '10px';
		d.style.textShadow = outline;
		d.textContent = fields[i].name + ': ';
		var input = d.create('input');
		input.className = fields[i].name.toLowerCase().replace(' ', '');
		input.setAttribute('type', fields[i].type);
		inputs[input.className] = input;
	}
	var submit = form.create('div');
	submit.style.border = '1px solid #333';
	submit.style.padding = '5px 20px';
	submit.style.width = '60px';
	submit.style.margin = '30px auto';
	submit.style.textAlign = 'center';
	submit.style.WebkitBorderRadius = '5px';
	submit.style.color = '#fff';
	submit.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#999), to(#666))';
	submit.textContent = 'Sign Up';
	button.create(submit, delegate(this, this.submit, inputs, inputs.name, inputs.password, inputs.confirmpassword));
	var toSignin = container.create('div');
	toSignin.style.color = '#009';
	toSignin.style.textAlign = 'center';
	toSignin.style.textShadow = outline;
	toSignin.textContent = 'Log In';
	button.create(toSignin, function () {
		self.signin();
	});
	// login view
	this.loginView = this.parent.create('div');
	var container = this.loginView.create('div');
	this.loginView.container = container;
	container.className = 'container';
	container.style.position = 'absolute';
	container.style.top = '160px';
	container.style.width = '310px';
	container.style.margin = '0 auto';
	var form = container.create('form');
	var fields = [ { name: 'Name', type: 'text' }, { name: 'Password', type: 'password' } ];
	var inputs = {};
	for (var i = 0; i < fields.length; i++) {
		var d = form.create('div');
		d.style.textAlign = 'right';
		d.style.marginBottom = '10px';
		d.style.textShadow = outline;
		d.textContent = fields[i].name + ': ';
		var input = d.create('input');
		input.className = fields[i].name.toLowerCase();
		input.setAttribute('type', fields[i].type);
		inputs[input.className] = input;
	}
	var loginBtn = form.create('div');
	loginBtn.style.border = '1px solid #333';
	loginBtn.style.padding = '5px 20px';
	loginBtn.style.width = '60px';
	loginBtn.style.margin = '30px auto';
	loginBtn.style.textAlign = 'center';
	loginBtn.style.color = '#fff';
	loginBtn.style.WebkitBorderRadius = '5px';
	loginBtn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#999), to(#666))';
	loginBtn.textContent = 'Log In';
	button.create(loginBtn, delegate(this, this.login, inputs, inputs.name, inputs.password, null));
	var toSignup = container.create('div');
	toSignup.style.color = '#009';
	toSignup.style.textAlign = 'center';
	toSignup.style.textShadow = outline;
	toSignup.textContent = 'Sign Up';
	button.create(toSignup, function () {
		self.signup();
	});
	
	// resend
	this.resend = null;
}

Landing.prototype.onOpen = function (params, cb) {
	var bg = '/img/contents/src/background/landing.jpg';
	var self = this;
	var preloader = new Preloader();
	preloader.addImage(bg);
	preloader.onComplete = function (type, dataMap) {
		self.parent.style.background = 'url(' + bg + ')';
		cb();
	};
	preloader.loadImage(false);
	viewPort.closeOverlay('TopMenu');
};

Landing.prototype.onOpenComplete = function (params) {
	this.connect(params);
};

Landing.prototype.connect = function (params) {
	if (params && params.type == 'signup') {
		this.signup();
	}
	else {
		if (params && params.resend) {
			this.resend = params.resend;
		}
		this.signin();
	}
};

Landing.prototype.signin = function () {
	this.signupView.style.display = 'none';
	this.loginView.style.display = 'none';
	// look for login data in localStorage
	var loginKey = localStorage.getItem(this.loginKey);
	if (loginKey) {
		this.login(null, { value: null }, { value: null }, loginKey);
	} 		
	else {
		this.signupView.style.display = 'none';
		this.loginView.style.display = 'block';
	}
};

Landing.prototype.reconnect = function (resend) {
	if (resend) {
		this.resend = resend;
	}
	var self = this;
	var loginKey = localStorage.getItem(this.loginKey);
	if (loginKey) {
		server.send('demo.login', { name: null, pass: null, loginKey: loginKey }, function (error, result) {
			if (error) {
				window.game.openLightBox('<p style="maring: 0; padding: 0; font-size: 15px; line-height: 30px;">Could not login. please try again.</p>', '#666');
				localStorage.removeItem(self.loginKey);
				return viewPort.open('Landing');
			}
			if (result && result.active) {
				// login successful
				self.activateGame(result);
			} else {
				// login failed
				viewPort.open('Landing');
			}
		});
	} else {
		viewPort.open('Landing');
	}
};

Landing.prototype.signup = function () {
	this.signupView.style.display = 'block';
	this.loginView.style.display = 'none';
};

Landing.prototype.login = function (fields, name, pass, key) {
	for (var i in fields) {
		fields[i].blur();
	}
	var self = this;
	server.send('demo.login', { name: name.value, pass: pass.value, loginKey: key }, function (error, result) {
		if (error) {
			window.game.openLightBox('<p style="maring: 0; padding: 0; font-size: 15px; line-height: 30px;">Could not login. Please try again.</p>', '#666');
			localStorage.removeItem(self.loginKey);
			return logger.error(error);
		}
		if (result && result.active) {
			// login successful
			self.activateGame(result);
		}
		else {
			self.signupView.style.display = 'none';
			self.loginView.style.display = 'block';
			localStorage.removeItem(self.loginKey);
		}
	});
};

Landing.prototype.submit = function (fields, name, pass, passC) {
	if (name.value && pass.value && passC.value && pass.value == passC.value) {
		for (var i in fields) {
			fields[i].blur();
		}
		var self = this;
		server.send('demo.signup', { name: name.value, pass: pass.value, passC: passC.value}, function (error, result) {
			if (error) {
				return logger.error(error);
			}
			if (result && result.active) {
				// sign up successful
				self.activateGame(result);
				// reset
				name.value = '';
				pass.value = '';
				passC.value = '';
			}
		}); 
	}
};

Landing.prototype.activateGame = function (session) {
	if (session && session.hash) {
		// store login data
		localStorage.setItem(this.loginKey, session.hash);
		// activate game
		var self = this;
		window.game.activate(session, function () {
			// resend
			if (self.resend && self.resend.method !== 'demo.signup' && self.resend.method !== 'demo.login') {
				server.send(self.resend.method, self.resend.params, self.resend.cb);
				self.resend = null;
			} else {
				viewPort.open('Main');
			}
		});
	}

};
