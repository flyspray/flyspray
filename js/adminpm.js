Event.observe(window,'load',adminInit);

function adminInit() {
	var toolbox = $('toolbox');

	console.log(toolbox.classNames());
	console.log($w(toolbox.className));

	var toolbox_class = $w(toolbox.className);
	var toolbox_area = '';

	for (c of toolbox_class) {
		console.log('loop : ' + c);
		if (c.match(/^toolbox_(\w+)$/)) {
			console.log('matched: ' + c);
			toolbox_area = c.replace(/^toolbox_/, '');

			console.log('area: ' + toolbox_area);

			break;
		}
	}

	var bd = $$('body')[0];
	var bd_class = $w(bd.className);

	var admin_type = '';

	for (c of bd_class) {
		if (c == 'pm' || c == 'admin') {
			admin_type = c;
			break;
		}
	}

	if (toolbox_area != '' && admin_type != '') {
		switch (toolbox_area) {
			case 'cat':
				adminInitCat(admin_type);
				break;
			case 'checks':
				if (admin_type == 'admin') {
					adminInitChecks(admin_type);
				}
				break;
			case 'editallusers':
				if (admin_type == 'admin') {
					adminInitEditAllUsers(admin_type);
				}
				break;
			case 'editgroup':
				adminInitEditGroup(admin_type);
				break;
			case 'groups':
				adminInitGroups(admin_type);
				break;
			case 'newgroup':
				adminInitNewGroup(admin_type);
				break;
			case 'newproject':
				if (admin_type == 'admin') {
					adminInitNewProject(admin_type);
				}
				break;
			case 'newuser':
				if (admin_type == 'admin') {
					adminInitNewUser(admin_type);
				}
				break;
			case 'newuserbulk':
				if (admin_type == 'admin') {
					adminInitNewUserBulk(admin_type);
				}
				break;
			case 'os':
				adminInitOS(admin_type);
				break;
			case 'pendingreq':
				if (admin_type == 'pm') {
					adminInitPendingReq(admin_type);
				}
				break;
			case 'prefs':
				adminInitPrefs(admin_type);
				break;
			case 'resolution':
				adminInitResolution(admin_type);
				break;
			case 'status':
				adminInitStatus(admin_type);
				break;
			case 'tag':
				adminInitTag(admin_type);
				break;
			case 'tasktype':
				adminInitTaskType(admin_type);
				break;
			case 'translations':
				if (admin_type == 'admin') {
					adminInitTranslations(admin_type);
				}
				break;
			case 'userrequest':
				if (admin_type == 'admin') {
					adminInitUserRequest(admin_type);
				}
				break;
			case 'users':
				if (admin_type == 'admin') {
					adminInitUsers(admin_type);
				}
				break;
			case 'version':
				adminInitVersion(admin_type);
				break;
		}
	}
}

function adminInitCat(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitChecks(admin_type) {
	if (admin_type != 'admin') { return; }
}

function adminInitEditAllUsers(admin_type) {
	if (admin_type != 'admin') { return; }
}

function adminInitEditGroup(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitGroups(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitNewGroup(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitNewProject(admin_type) {
	if (admin_type != 'admin') { return; }
}

function adminInitNewUser(admin_type) {
	if (admin_type != 'admin') { return; }
}

function adminInitNewUserBulk(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitOS(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitPendingReq(admin_type) {
	if (admin_type != 'pm') { return; }
}

function adminInitPrefs(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }

	/*
		General tab
	*/
	// Set up toggle for intro message section
	disp_intro = $('disp_intro');
	disp_intro.observe('change', function(e) {
		var me = e.element();
		var disp_introdep = $$('.disp_introdep')[0];

		if (me.checked) {
			if (disp_introdep.hasClassName('hide-intro')) {
				disp_introdep.toggleClassName('hide-intro', false);
				disp_introdep.parentNode.toggleClassName('hide-intro', false);
			}
		}
		else {
			if (!disp_introdep.hasClassName('hide-intro')) {
				disp_introdep.toggleClassName('hide-intro', true);
				disp_introdep.parentNode.toggleClassName('hide-intro', true);
			}
		}
	});

	/*
		Look and Feel tab
	*/


	/*
		Notifications tab
	*/


	/*
		Feeds tab
	*/


	/*
		Effort Tracking tab
	*/

	// Explicity fire any event handlers to ensure initial document state is correct
}

function adminInitResolution(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitStatus(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitTag(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitTaskType(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}

function adminInitTranslations(admin_type) {
	if (admin_type != 'admin') { return; }
}

function adminInitUserRequest(admin_type) {
	if (admin_type != 'admin') { return; }
}

function adminInitUsers(admin_type) {
	if (admin_type != 'admin') { return; }
}

function adminInitVersion(admin_type) {
	if (admin_type != 'admin' && admin_type != 'pm') { return; }
}
