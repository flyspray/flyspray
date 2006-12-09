Event.observe(window,'load',tasklistInit);
Event.observe(window,'load',searchInit);

function tasklistInit() {
  Caret.init();
}
function searchInit() {
  if (navigator.appVersion.match(/\bMSIE 6\.0\b/) && $('searchthisproject') && $('reset')) {
    Event.observe('searchthisproject','click',function() {$('reset').remove();});
  }
}
var Caret = {
  init: function () {
    var task = Cookie.getVar('current_task') || 'top';
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
          window.location = Caret.currentRow.getElementsByTagName('a')[0].href;
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
      Cookie.setVar('current_task','top');
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
      Cookie.setVar('current_task','bottom');
      window.location = $('previous').href;
      return;
    }
    
  }
};


