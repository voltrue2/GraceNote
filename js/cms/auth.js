// Loader.src.js is required
(function () {

var loader = new Loader();

window.blocker.setup(loader);

var auth = {};

window.auth = auth;

auth.focus = function () {
	document.getElementById('nameInput').focus();
};

auth.focus();

}());
