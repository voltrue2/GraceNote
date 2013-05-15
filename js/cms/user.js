(function () {

var loader = new Loader();

var user = {};

window.user = user;

user.openMenu = function () {
	window.lightbox.show(300, 60, function (bar, box) {
		box.setClassName('menu');
		// edit cms user
		var edit = box.createChild('div');
		edit.setClassName('edit-button menu-item');
		edit.on('tapend', function () {
			createUserEditor(window.cmsUser, 'updateUserData', true);
		});
		// manage cms users
		if (window.cmsUser.permission == 1) {
			var user = box.createChild('div');
			user.setClassName('user-button menu-item');
			user.on('tapend', function () {
				createUserEditor({ user: '', fileRestriction: '', permission: 2 }, 'updateUserData/new');
			});
		}
		// logout
		var signout = box.createChild('div');
		signout.setClassName('quit-button menu-item');
		signout.on('tapend', function () {
			window.location.href = '/auth/signout/';
		});
	});	
};

function createUserEditor(userData, saveMethod, languageSelector) {
	if (window.cmsUser && window.languages) {
		window.lightbox.show(400, 430, function (bar, box, close) {
			var container = box.createChild('div');
			// language selector
			if (languageSelector) {
				var lang = container.createChild('div', { paddingLeft: 0 });
				lang.setClassName('area');
				var langList = lang.createChild('select');
				for (var i = 0, len = window.languages.length; i < len; i++) {
					var langData = window.languages[i];
					var option = langList.createChild('option');
					option.set('value', langData.id);
					option.text(langData.name);
					if (window.currentLang === langData.id) {
						option.set('selected', true);
					}
				}
				langList.allowEvents(['change']);
				langList.on('change', function () {
					var url = window.location.href + '?lang=' + this.get('value');
					window.location.href = url;
				});
			} else {
				// user list
				var userList = container.createChild('div', { paddingLeft: 0 });
				userList.setClassName('area');
				var list = userList.createChild('select');
				var option = list.createChild('option');
				option.text(window.text.select, 'Please Select');
				var uri = '/user/getUserList/';
				loader.ajax(uri, null, function (error, path, listData) {
					if (error) {
						return alert(error);
					}
					list.data = listData.list;
					for (var i = 0, len = listData.list.length; i < len; i++) {
						var option = list.createChild('option');
						option.set('value', listData.list[i].id);
						option.text(listData.list[i].name);
					}
				});
				userList.list = list;
			}
			// user data
			var userField = container.createChild('div', { paddingLeft: 0 });
			userField.setClassName('area');
			// name
			var name = userField.createChild('div');
			name.setClassName('title');
			name.text(window.text.name || 'Name');
			var nameInput = userField.createChild('input');
			nameInput.set('type', 'text');
			nameInput.set('value', userData.user);
			// password
			var pass = userField.createChild('div');
			pass.setClassName('title');
			pass.text(window.text.password || 'Password');
			var pass1 = userField.createChild('input');
			pass1.set('type', 'password');
			var pass2 = userField.createChild('input');
			pass2.set('type', 'password');
			if (window.cmsUser.permission == 1) {
				// file restriction
				var fr = userField.createChild('div');
				fr.setClassName('title');
				fr.text(window.text.fileRestriction || 'File Restriction');
				var frInput = userField.createChild('input', { width: '300px' });
				frInput.set('type', 'text');
				frInput.set('value', userData.fileRestriction);
				// permission
				var perm = userField.createChild('div');
				perm.setClassName('title');
				perm.text(window.text.permission || 'Permission Level');
				var permInput = userField.createChild('input');
				permInput.set('type', 'text');
				permInput.set('value', userData.permission);
				permInput.numberInput();
			}
			// select a user
			if (userList) {
				var delBtn = null;
				var originMethod = saveMethod;
				userList.list.allowEvents(['change']);
				userList.list.on('change', function () {
					var data = this.data;
					var item = null;
					var selected = this.get('value');
					for (var i = 0, len = data.length; i < len; i++) {
						if (data[i].id === selected) {
							item = data[i];
							break;
						}
					}
					if (item) {
						nameInput.set('value', item.name);
						if (frInput) {
							frInput.set('value', item.file_restriction);
						}
						if (permInput) {
							permInput.set('value', item.permission);
						}
						saveMethod = 'updateUserData/update/' + item.id;
						// delete button
						if (delBtn) {
							delBtn.remove();
							delBtn = null;
						}
						delBtn = userList.createChild('div');
						delBtn.setClassName('delete-button');
						delBtn.on('tapend', function () {
							var res = confirm(window.text.deleteMsg.replace('$1', item.name));
							if (res) {
								var uri = '/user/deleteUser/' + item.id + '/';
								loader.ajax(uri, null, function (error, path, res) {
									if (error) {
										return alert(error);
									}
									close();
								});
							}
						});
					} else {
						saveMethod = originMethod;
						nameInput.set('value', '');
						if (frInput) {
							frInput.set('value', '');
						}
						if (permInput) {
							permInput.set('value', 2);
						}
						// remove delete button
						if (delBtn) {
							delBtn.remove();
							delBtn = null;
						}
					}
				});
			}
			// save
			var save = container.createChild('div', { margin: '10px auto' });
			save.setClassName('text-button');
			save.text(window.text.save || 'Save Changes');
			save.on('tapend', function () {
				var uri = '/user/' + saveMethod + '/';
				var send = {
					user: nameInput.get('value'),
					fileRestriction: frInput ? frInput.get('value') : null,
					permission: permInput ? permInput.get('value') : null
				};
				// check password
				if (pass1.get('value') === pass2.get('value')) {
					if (pass1.get('value')) {
						send.password = pass1.get('value');
					}
				} else {
					return alert(window.text.passNotMatch || 'Password do not match');
				}
				loader.ajax(uri, { userData: send }, function (error) {
					if (error) {
						return alert(error);
					}
					window.location.href = '/auth/signout/';
				});
			});		
		});
	}
}

}());
