Event.observe(window,'load',detailsInit);

function detailsInit() {
  // set current task
  var title = document.getElementsByTagName('title')[0];
  title = title.textContent || title.text; //IE uses .text
  var arr = /\d+/.exec(title);
  Cookie.setVar('current_task',arr[0]);
  if (!$('details')) {
    // make sure the page is not in edit mode
    Event.observe(document,'keydown',keyboardNavigation);
  }
}
function keyboardNavigation(e) {
  var src = Event.element(e);
  if (/input|select|textarea/.test(src.nodeName.toLowerCase())) {
    // don't do anything if key is pressed in input, select or textarea
    return;
  }
  if ((useAltForKeyboardNavigation && !e.altKey) ||
       e.ctrlKey || e.shiftKey) {
    return;
  }
  switch (e.keyCode) {
    case 85:  // "u" get back to task list
        window.location = $('indexlink').href;
        Event.stop(e);
        break;
    case 80:  // "p" move to previous task
        if ($('prev')) {
          window.location = $('prev').href;
          Event.stop(e);
        }
        break;
    case 78: // "n" move to next task
        if ($('next')) {
          window.location = $('next').href;
          Event.stop(e);
        }
        break;
  }
}