/*
 * $Id$
 */
addEvent(window, "load", initTabs);
function addEvent(elm, evType, fn, useCapture)
// addEvent and removeEvent
// cross-browser event handling for IE5+,  NS6 and Mozilla
// By Scott Andrew
{
  if (elm.addEventListener){
    elm.addEventListener(evType, fn, useCapture);
    return true;
  } else if (elm.attachEvent){
    var r = elm.attachEvent("on"+evType, fn);
    return r;
  } else {
    alert("Handler could not be removed");
  }
}

var _TAB_DIVS;

// tabs handling {{{
// show tab with given id
function showTabById(tabid, noEval) { // {{{
  var divs = document.getElementsByTagName('div');
  var tab = document.getElementById(tabid);
  var submenu = document.getElementById('submenu');
  var i;
  var el = document.getElementById(tabid + "taba");

  if (!noEval && el) {
    eval(el.getAttribute('onmousedown'));
  }
  for (i=0; i<divs.length; i++) {
    if (divs[i].className && (divs[i].className.indexOf('tab') > -1)) {
      divs[i].style.display = 'none';
    }
  }

  if (tab) {
    tab.style.display = 'block';

    if (submenu) {
      var links = submenu.getElementsByTagName('a');
      for (i=0; i<links.length; i++) {
        if (links[i].href.match('^.*#'+tabid+'$')) {
          links[i].className = 'active';
        } else { links[i].className = ''; }
      }
    }
  }
  //if (window.scrollTo(0,0)) {
  //    window.scrollTo(0,0);
  //}
} // }}}

// create JavaScript calls to switch tabs
function makeTabLinks() { // {{{
  var submenu = document.getElementById('submenu');
  var links, i, target;

  if (submenu) {
    links = submenu.getElementsByTagName('a');
    for (i=0; i<links.length; i++) {
      var href = links[i].getAttribute('href');
      target = href.substring(href.indexOf('#')+1);
      links[i]['onclick'] = new Function("showTabById('"+target+"'); return false;");
    }
  }
} // }}}

// show tab with given number
function showTabByNumber(number) { // {{{
  var targets = new Array();    // tab names
  var divs = document.getElementsByTagName('div');
  var i;

  for (i=0; i<divs.length; i++) {
    if (divs[i].className == 'tab') {
      targets[targets.length] = divs[i].id;             //array[array.length]= same as .push, but IE-compatible.
    }
  }
  if (number >= targets.length) {
    number = targets.length-1;
  }
  showTabById(targets[number]);
} // }}}

// get list of all DIVs that contain tabs
function getTabDivs() {/*{{{*/
    if (_TAB_DIVS == null) {
        _TAB_DIVS = new Array();
        var divs = document.getElementsByTagName('div');
        var i;
        for (i=0; i<divs.length; i++) {
            if (divs[i].className && (divs[i].className.indexOf('tab') > -1)) {
                _TAB_DIVS[_TAB_DIVS.length] = divs[i];  //array[array.length]= same as .push, but IE-compatible.
            }
        }
    }
    return _TAB_DIVS;
}/*}}}*/

// tabs init
// show first tab or tab with given name (string after #)
function initTabs() {/*{{{*/
  var target = location.href.substring(location.href.indexOf('#')+1);
  if (target.match(/comment/)) {
    target = "comments";
  }
  
  makeTabLinks();

  if (target && document.getElementById(target) && document.getElementById(target).className == 'tab') {
    showTabById(target);
  } else {
    showTabByNumber(0);
  }
  var history = document.getElementById('history');
  if (history) {
    addEvent(history,'click',uglyHistoryCommentFix);
  }

}/*}}}*/
function uglyHistoryCommentFix(e) {
  var target = e.target || window.event.srcElement;
  if ('A' == target.nodeName) {
    var re= /#comments/;
    if (re.test(target.href)) {
      showTabById('comments');
    }
  }
  var history = document.getElementById('history');
  if (history) {
    addEvent(history,'click',uglyHistoryCommentFix);
  }
}
// }}}

// }}}

// vim:enc=utf-8:fenc=utf-8:fdm=marker
