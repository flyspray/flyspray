Event.observe(window,'load',tasklistInit);
Event.observe(window,'load',searchInit);

function tasklistInit() {
  Caret.init();
}

function searchInit() {
  if (navigator.appVersion.match(/\bMSIE 6\.0\b/) && $('searchthisproject') && $('reset')) {
    Event.observe('searchthisproject','click',function() {$('reset').remove();});
  }

  let search =document.getElementById('search');
  if (search !== null) {
    search.addEventListener('submit', trimBeforeSearchSubmit);
  }
}

// convenience for users with enabled javascript in browser
// Removes default/unset search parameters from the search GET request for shorter URL
function trimBeforeSearchSubmit(event){
	if (document.getElementById('searchtext') && document.getElementById('searchtext').value=='') {
		document.getElementById('searchtext').disabled='disabled';
	}

	// id and name differ, name is 'search_name'
	if (document.getElementById('save_search') && document.getElementById('save_search').value=='') {
		document.getElementById('save_search').disabled='disabled';
	}

	// multi-selects
	if (document.getElementById('type') && document.getElementById('type').selectedOptions.length==1 && document.getElementById('type').selectedOptions[0].value==''){
		document.getElementById('type').disabled='disabled';
	}

	if (document.getElementById('sev') && document.getElementById('sev').selectedOptions.length==1 && document.getElementById('sev').selectedOptions[0].value==''){
		document.getElementById('sev').disabled='disabled';
	}
	if (document.getElementById('pri') && document.getElementById('pri').selectedOptions.length==1 && document.getElementById('pri').selectedOptions[0].value==''){
		document.getElementById('pri').disabled='disabled';
	}

	if (document.getElementById('due') && document.getElementById('due').selectedOptions.length==1 && document.getElementById('due').selectedOptions[0].value==''){
		document.getElementById('due').disabled='disabled';
	}

	if (document.getElementById('reported') && document.getElementById('reported').selectedOptions.length==1 && document.getElementById('reported').selectedOptions[0].value==''){
		document.getElementById('reported').disabled='disabled';
	}

	if (document.getElementById('cat') && document.getElementById('cat').selectedOptions.length==1 && document.getElementById('cat').selectedOptions[0].value==''){
		document.getElementById('cat').disabled='disabled';
	}

	if (document.getElementById('percent') && document.getElementById('percent').selectedOptions.length==1 && document.getElementById('percent').selectedOptions[0].value==''){
		document.getElementById('percent').disabled='disabled';
	}

	// status select is a bit different than the other multiselects as 'open' is preselected and the default, so we can shorten the url when 'open' selected
	if (document.getElementById('status') && document.getElementById('status').selectedOptions.length==1 && document.getElementById('status').selectedOptions[0].value=='open'){
		document.getElementById('status').disabled='disabled';
	}

	// username: opened by
	if (document.getElementById('opened') && document.getElementById('opened').value=='') {
		document.getElementById('opened').disabled='disabled';
	}

	// username: one of the assignees
	if (document.getElementById('dev') && document.getElementById('dev').value=='') {
		document.getElementById('dev').disabled='disabled';
	}

	// username: closed by
	if (document.getElementById('closed') && document.getElementById('closed').value=='') {
		document.getElementById('closed').disabled='disabled';
	}

	// dates
	if (document.getElementById('duedatefrom') && document.getElementById('duedatefrom').value=='') {
		document.getElementById('duedatefrom').disabled='disabled';
	}
	if (document.getElementById('duedateto') && document.getElementById('duedateto').value=='') {
		document.getElementById('duedateto').disabled='disabled';
	}

	if (document.getElementById('changedfrom') && document.getElementById('changedfrom').value=='') {
		document.getElementById('changedfrom').disabled='disabled';
	}
	if (document.getElementById('changedto') && document.getElementById('changedto').value=='') {
		document.getElementById('changedto').disabled='disabled';
	}

	if (document.getElementById('openedfrom') && document.getElementById('openedfrom').value=='') {
		document.getElementById('openedfrom').disabled='disabled';
	}
	if (document.getElementById('openedto') && document.getElementById('openedto').value=='') {
		document.getElementById('openedto').disabled='disabled';
	}

	if (document.getElementById('closedfrom') && document.getElementById('closedfrom').value=='') {
		document.getElementById('closedfrom').disabled='disabled';
	}
	if (document.getElementById('closedto') && document.getElementById('closedto').value=='') {
		document.getElementById('closedto').disabled='disabled';
	}

}

var Caret = {
  init: function () {
    var task = sessionStorage.getItem('current_task') || 'top';
    if (task == 'bottom' || task == 'top') {
      var tab = $('tasklist_table');
      var rows = tab ? tab.getElementsByTagName('tbody')[0].getElementsByTagName('tr') : [];
      Caret.currentRow = (task == 'top' || rows.length == 0) ? rows[0] : rows[rows.length-1];
    }
    else {
      Caret.currentRow = $('task'+task);
    }
    if (Caret.currentRow) {
      Element.addClassName(Caret.currentRow,'current_row');
      Event.observe(document,'keydown',Caret.keypress);
    }
  },
  keypress: function (e) {
    var src = Event.element(e);
    if (/input|select|textarea/.test(src.nodeName.toLowerCase())) {
      // don't do anything if key is pressed in input, select or textarea
      return;
    }
    if ((useAltForKeyboardNavigation && !e.altKey) ||
        (!useAltForKeyboardNavigation && e.altKey) ||
         e.ctrlKey || e.shiftKey) {
      return;
    }
    switch (e.keyCode) {
      case 74:       // user pressed "j" move down
          Element.removeClassName(Caret.currentRow,'current_row');
          Caret.nextRow();
          Element.addClassName(Caret.currentRow,'current_row');
          Event.stop(e);
          break;
      case 75:      // user pressed "k" move up
          Element.removeClassName(Caret.currentRow,'current_row');
          Caret.previousRow();
          Element.addClassName(Caret.currentRow,'current_row');
          Event.stop(e);
          break;
      case 79:     // user pressed "o" open task
          window.location = Caret.currentRow.getElementsByTagName('a')[0].href; // FIXME ambiguous in future: if first a is not a link to the task, e.g. a column with link to task opener 
          Event.stop(e);
          break;
    }
  },
  nextRow: function () {
    var row = Caret.currentRow;
    while ((row = row.nextSibling)) {
      if ('tr' == row.nodeName.toLowerCase()) {
        Caret.currentRow = row;
        return;
      }
    }
    // we've reached the bottom of the list
    if ($('next')) {
      //Cookie.setVar('current_task','top'); // doesn't work well on multitab multiproject usage
      sessionStorage.setItem('current_task','top');
      window.location = $('next').href;
      return;
    }
  },
  previousRow: function () {
    var row = Caret.currentRow;
    while ((row = row.previousSibling)) {
      if ('tr' == row.nodeName.toLowerCase()) {
        Caret.currentRow = row;
        return;
      }
    }
    // we've reached the top of the list
    if ($('previous')) {
      //Cookie.setVar('current_task','bottom'); // doesn't work well on multitab multiproject usage
      sessionStorage.setItem('current_task','bottom');
      window.location = $('previous').href;
      return;
    }
    
  }
};
