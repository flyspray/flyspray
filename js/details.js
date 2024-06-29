Event.observe(window,'load',detailsInit);

function detailsInit() {

  // set current task
  var title = document.getElementsByTagName('title')[0];
  title = title.textContent || title.text; //IE uses .text
  var arr = /(#)(\d+)/.exec(title);
  if( arr != null){
    sessionStorage.setItem('current_task', arr[2]);

    // make sure the page is not in edit mode, 'details' is id of description textarea
    if (!document.getElementById('details')) {
      Event.observe(document,'keydown',keyboardNavigation);

      if (document.getElementById('actions')) {
          Event.observe('actions', 'click', function (e) { showhidestuff('actionsform') } );
      }

      if (document.getElementById('closetask')) {
          Event.observe('closetask', 'click', function (e) { showhidestuff('closeform') } );
      }

      if (document.getElementById('reqclosetask')) {
          Event.observe('reqclosetask', 'click', function (e) { showhidestuff('requestreopen') } );
      }

      if (document.getElementById('reqclosetask')) {
          Event.observe('reqclose', 'click', function (e) { showhidestuff('closeform') } );
      }

      if (document.getElementById('reqclosedisabled')) {
        Event.observe('reqclosedisabled', 'click', function (e) { showhidestuff('reqclosedinfo') } );
      }
    } else {
      // when in edit mode
      if (document.getElementById('toggle_add_comment')) {
        $('toggle_add_comment').observe('click', function (e) {
          var eac = $('edit_add_comment');
          var ct = $('comment_text');

          if (eac.visible()) {
            $(eac).hide();
            $(ct).disabled = true;
            var cv = $(ct).value.trim();

            if (cv != '') {
              $(this).childElements()[0].style.color = '#900';
            } else {
              $(this).childElements()[0].style.color = null;
            }
          } else {
            $(eac).show();
            $(ct).disabled = false;
            $(ct).focus();
          }
        });
      }
    }
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
