(function () {
// This is the core object 
// Requires FileLoader, View
// Dependencies extensions, FrameAnimation, Sever, ViewPort

var domain = 'http://php-gracenote.com/';
var requestTimeout = 20000;
var main = 'Main';
var level = null;
var xp = null;
var name = null;
var prevViewName = null;
var btnOn = null;
var btnOff = null;
var audioList = [
	domain + 'img/contents/src/audio/click.mp3',
	domain + 'img/contents/src/audio/bang2.mp3',
	domain + 'img/contents/src/audio/sword-small.mp3',
];
var audioMap = {
	click: domain + 'img/contents/src/audio/click.mp3',
	bang: domain + 'img/contents/src/audio/bang2.mp3',
	battle: domain + 'img/contents/src/audio/sword-small.mp3',
};
var audioSrc = null;

function Application (parent) {
	window.loader = new FileLoader();
	window.server = new Server(domain);
	window.server.onSend = onSend;
	window.server.onResponse = onResponse;
	window.server.onCallback = onCallback;
	window.server.onError = onError;
	window.server.setTimeout(requestTimeout);
	extend(document.body);
	window.viewPort = new ViewPort(parent, { 
		width: '320px', 
		height: '100%', 
		minWidth: '320px', 
		minHeight: '416px', 
		position: 'absolute', 
		top: 0, 
		left: 0,
		marginLeft: 'auto',
		marginRight: 'auto'
	}); 
	// set up views
	window.viewPort.addView('/js/game/views/Landing.js', 'Landing');
	window.viewPort.addView('/js/game/views/TopMenu.js', 'TopMenu');
	window.viewPort.addView('/js/game/views/Dictionary.js', 'Dictionary');
	window.viewPort.addView('/js/game/views/' + main + '.js', main);
	window.viewPort.addView('/js/game/views/HuntArea.js', 'HuntArea');
	window.viewPort.addView('/js/game/views/Fusion.js', 'Fusion');
	window.viewPort.addView('/js/game/views/Battle.js', 'Battle');
	window.viewPort.addView('/js/game/views/TeamManagement.js', 'TeamManagement');
	window.viewPort.addView('/js/game/views/Creature.js', 'Creature');
	window.viewPort.addView('/js/game/views/CreatureList.js', 'CreatureList');
	window.viewPort.addView('/js/game/views/Lock.js', 'Lock');
	window.viewPort.addView('/js/game/views/LightBox.js', 'LightBox');
	window.viewPort.addView('/js/game/views/CutIn.js', 'CutIn');
	window.viewPort.loadViews(function () {
		// set up screen lock
		window.viewPort.setupScreenLock('Lock');
		window.viewPort.onOpen = function () {
			window.viewPort.lock();
		};
		window.viewPort.onOpenComplete = function () {
			window.viewPort.unlock();
		};
		setupAudio(function () {});
		// start the game
		getSession(false, function () {});
	});
	//window.viewPort.on('open', hideAddressBar);
	// game util functions
	window.game = {};
	window.game.session = null;
	window.game.isActive = isActive;
	window.game.activate = activate;
	window.game.inactivate = inactivate;
	window.game.openLightBox = openLightBox;
	window.game.closeLightBox = closeLightBox;
	window.game.onCloseLightBox = onCloseLightBox;
	window.game.playAudio = playAudio;
	// generic animations
	window.game.fadeIn = new FrameAnimation();
	window.game.fadeIn.addKeyFrame(0, { opacity: 0 });
	window.game.fadeIn.addKeyFrame(4, { opacity: 1 });
	window.game.fadeIn.setup({ easing: 'ease-in-out' });
	// set up button animations
	btnOn = new FrameAnimation();
	btnOn.addKeyFrame(0, { WebkitTransform: 'scale(1, 1)', opacity: 1 });
	btnOn.addKeyFrame(2, { WebkitTransform: 'scale(0.95, 0.95)', opacity: 0.8 });
	btnOn.setup({ easing: 'ease-in-out' });
	btnOff = new FrameAnimation();
	btnOff.addKeyFrame(0, { WebkitTransform: 'scale(0.95, 0.95)', opacity: 0.8 });
	btnOff.addKeyFrame(2, { WebkitTransform: 'scale(1, 1)', opacity: 1 });
	btnOff.setup({ easing: 'ease-in-out' });
	// setup audio src
	//audioSrc = document.createElement('audio');
	// set up auto-resync
	localStorage.setItem('noSync', true);
	window.addEventListener('pageshow', function (event) {
		// it does not sync when you first construct the game
		var noSync = localStorage.getItem('noSync');
		if (!noSync) {
			// sync
			var resync = true;
			getSession(resync, function () {
				// force the lock to unlock
				window.viewPort.lockCounter = 0;	
			});
		}
		localStorage.removeItem('noSync');
	}, false);
}

function playAudio(name) {
	if (audioMap[name]) {
		//audioSrc.setAttribute('src', audioMap[name]);
		//audioSrc.play();
	}
}

function openLightBox(text, borderColor) {
	var params = {}
	params.border = '4 solid ' + borderColor;
	params.text = text;
	window.viewPort.overlay('LightBox', params);
	window.viewPort.lockToTop('LightBox');
	window.viewPort.forceUnlock();
}

function closeLightBox(cb){
	if (typeof cb === 'function') {
		window.viewPort.views.LightBox.lightBoxCallback = cb;
	}
	window.viewPort.closeOverlay('LightBox');
}

function onCloseLightBox(cb){
	window.viewPort.views.LightBox.setProperty('lightBoxCallback', cb);
}

// ask for session if there is any
function getSession(resync, cb) {
	window.server.send('demo.session', {}, function (error, data) {
		if (error) {
			return cb(error);
		}
		// reset time flag for HuntArea
		// TODO: do it better
		localStorage.removeItem('lastUpdate');
		var session = data.session;		
		if (session && session.id) {
			if (!resync) {
				// session found > start the game
				activate(session);
			}
		}
		else {
			// session not found > login
			window.viewPort.open('Landing', { type: 'login' });
		}
		cb();
	});
}

// call this function to check for login status of a user
function isActive(data) {
	if (!data || data.active === false) {
		// user is not active (not logged in)
		return false;
	}
	return true;
}

// call this function to activate the game
function activate(session, optionalCallback) {
	viewPort.views.Dictionary.setup(function () {
		window.game.session = session;
		if (!prevViewName) {
			// reloaded or loaded for the first time
			window.viewPort.open(prevViewName || main);
		}
		window.viewPort.views.TopMenu.update();
		prevViewName = null;
		if (optionalCallback) {
			optionalCallback(); 
		}
	});
}

// call this function to inactivate the game
function inactivate(sendObj) {
	// remember current view name
	if (viewPort.currentView) {
		prevViewName = viewPort.currentView.name;
	} else {
		prevViewName = main;
	}
	// reset time flag for HuntArea
	// TODO: do it better
	localStorage.removeItem('lastUpdate');
	// session gone > reconnect > re-send
	window.viewPort.views.Landing.reconnect(sendObj);
}

function parseReturnData(data, cb) {
	if (data && data.session) {
		window.game.session = data.session;
	}
	if (data && data.my_creatures) {
		if (data && data.boss !== undefined) {
			// update boss
			if (data.boss !== viewPort.views.HuntArea.getBoss()) {
				viewPort.views.HuntArea.update(data.boss);
			}
		}
		// update team and creature list and dictionary
		viewPort.views.Dictionary.update(data.my_creatures);
		viewPort.views.TeamManagement.update(data.my_creatures, cb);
	} else {
		if (data && data.boss !== undefined) {
			// update boss
			viewPort.views.HuntArea.update(data.boss);
		}
		cb();
	}
	viewPort.views.TopMenu.update();
}

function onSend() {
	viewPort.lock();
}

function onResponse(error, data, path, sendObj, cb) {
	if (error) {
		logger.error(error);
		return cb();
	}
	if (!isActive(data)) {
		return inactivate(sendObj);
		// cb(); // we do NOT call the callback when there is no session > instead we re-login and re-execute
	}
	parseReturnData(data, cb);
}

function onCallback() {
	window.setTimeout(function () {
		viewPort.unlock();
	}, 0);
}

function onError(error, response, sendObj) {
	viewPort.unlock();
	if (sendObj) {
		window.game.onCloseLightBox(function reconnect() {
			// retry
			server.send(sendObj.method, sendObj.params, sendObj.cb);
		});
		window.game.openLightBox('<p style="maring: 0; padding: 0; font-size: 18px; line-height: 24px;">Connection lost.<br />Retry</p>', '#999');
	}
}

// set up button object
button.onCreate = function (element) {
	element.style.WebkitTapHighlightColor = 'rgba(0,0,0,0)';
};

button.onStart = function (event) {
	event.preventDefault();
	//event.srcElement.style.opacity = 0.5;
	//event.srcElement.style.WebkitTapHighlightColor = 'rgba(0,0,0,0)'; 
	btnOn.setTarget(event.srcElement);
	btnOff.setTarget(event.srcElement);
	btnOn.stop();
	btnOff.stop();
	btnOn.start();
	playAudio('click');
};
button.onEnd = function (event) {
	event.preventDefault();
	//event.srcElement.style.opacity = 1;
	btnOn.setTarget(event.srcElement);
	btnOff.setTarget(event.srcElement);
	btnOn.stop();
	btnOff.stop();
	btnOff.start();
};
button.onCancel = function (event) {
	event.preventDefault();
	//event.srcElement.style.opacity = 1;
	btnOn.setTarget(event.srcElement);
	btnOff.setTarget(event.srcElement);
	btnOn.stop();
	btnOff.stop();
	btnOff.start();
};
button.onDisable = function (elm) {
	elm.originalBg = elm.style.background;
	elm.originalColor = elm.style.color;
	elm.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#BDBDBD), to(#4A4A4A))';
	elm.style.color = '#ccc';
	elm.style.WebkitTapHighlightColor = 'rgba(0,0,0,0)'; 
};
button.onEnable = function (elm) {
	elm.style.background = elm.originalBg;
	elm.style.color = elm.originalColor;
	elm.style.WebkitTapHighlightColor = ''; 
};

// not used for now
function setupAudio(list, cb) {
	var preloader = new Preloader();
	for (var i = 0, len = list.length; i < len; i++) {
		console.log(list[i]);
		preloader.addAudio(list[i]);
	}
	preloader.onComplete = cb;
	preloader.loadAudio(false); // asynchronous
}

// initialize everything
onPageReady(function () {
	// hide address bar
	window.hideAddressBar();
	var body = document.find('body');
	body.css({
		WebkitUserSelect: 'none',
		width: '100%',
		height: '100%'
	});
	var container = body.create('div');
	container.css({
		width: '320px',
		height: '100%',
		position: 'relative',
		marginLeft: 'auto',
		marginRight: 'auto'
	});
	var viewParent = container.create('div');
	// set up application 
	window.app = new Application(viewParent);
});

}());
