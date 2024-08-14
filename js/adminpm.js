Event.observe(window,'load',adminInit);

function adminInit() {
	var toolbox = $('toolbox');
	var toolbox_class = $w(toolbox.className);
	var toolbox_area = '';

	for (c of toolbox_class) {
		//console.log('loop : ' + c);
		if (c.match(/^toolbox_(\w+)$/)) {
			//console.log('matched: ' + c);
			toolbox_area = c.replace(/^toolbox_/, '');

			//console.log('area: ' + toolbox_area);

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

	$('toggledbfields').observe('click', function (e) {
		var me = this;
		var icon = me.firstDescendant();

		 // fields visible
		var on = !$('dbtables').hasClassName('hidden-fields');

		if (on) {
			me.childElements()[1].textContent = me.dataset.offText;
			icon.classList.replace('fa-' + me.dataset.onIcon, 'fa-' + me.dataset.offIcon);

			$('dbtables').addClassName('hidden-fields');
		} else {
			me.childElements()[1].textContent = me.dataset.onText;
			icon.classList.replace('fa-' + me.dataset.offIcon, 'fa-' + me.dataset.onIcon);

			$('dbtables').removeClassName('hidden-fields');
		}
	});

	$('toggledbconninfo').observe('click', function (e) {
		var me = this;
		var icon = me.firstDescendant();

		 // fields visible
		var on = !$('dbinfo').hasClassName('hidden-info');

		if (on) {
			me.childElements()[1].textContent = me.dataset.offText;
			icon.classList.replace('fa-' + me.dataset.onIcon, 'fa-' + me.dataset.offIcon);

			$('dbinfo').addClassName('hidden-info');
		} else {
			me.childElements()[1].textContent = me.dataset.onText;
			icon.classList.replace('fa-' + me.dataset.offIcon, 'fa-' + me.dataset.onIcon);

			$('dbinfo').removeClassName('hidden-info');

		}
	});
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
		var disp_introdep = $$('.disp_introdep');

		disp_introdep.each(function(i) {
			if (me.checked) {
				if (i.hasClassName('hide-intro')) {
					i.toggleClassName('hide-intro', false);
				}
			}
			else {
				if (!i.hasClassName('hide-intro')) {
					i.toggleClassName('hide-intro', true);
				}
			}
		});
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
