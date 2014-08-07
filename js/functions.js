// Set up the task list onclick handler
addEvent(window,'load',setUpTasklistTable);
function Disable(formid)
{
   document.formid.buSubmit.disabled = true;
   document.formid.submit();
}

function showstuff(boxid, type){
   if (!type) type = 'block';
   $(boxid).style.display= type;
   $(boxid).style.visibility='visible';
}

function hidestuff(boxid){
   $(boxid).style.display='none';
}

function hidestuff_e(e, boxid){
   e = e || window.event;
   if (Event.element(e).getAttribute('id') !== 'lastsearchlink' ||
	(Event.element(e).getAttribute('id') === 'lastsearchlink' && $('lastsearchlink').className == 'inactive')) {
	if (!Position.within($(boxid), Event.pointerX(e), Event.pointerY(e))) {
	   //Event.stop(e);
	   if (boxid === 'mysearches') {
		activelink('lastsearchlink');
	   }
	   $(boxid).style.visibility='hidden';
	   $(boxid).style.display='none';
	   document.onmouseup = null;
   	}
   }
}

function showhidestuff(boxid) {
   if (boxid === 'mysearches') {
	activelink('lastsearchlink');
   }
   switch ($(boxid).style.visibility) {
      case '':
	$(boxid).style.visibility='visible';
	break;
      case 'hidden':
	$(boxid).style.visibility='visible';
	break;
      case 'visible':
	$(boxid).style.visibility='hidden';
	break;
   }
   switch ($(boxid).style.display) {
      case '':
	$(boxid).style.display='block';
	document.onmouseup = function(e) { hidestuff_e(e, boxid); };
	break;
      case 'none':
	$(boxid).style.display='block';
	document.onmouseup = function(e) { hidestuff_e(e, boxid); };
	break;
      case 'block':
	$(boxid).style.display='none';
	document.onmouseup = null;
	break;
      case 'inline':
	$(boxid).style.display='none';
	document.onmouseup = null;
	break;
   }
}
function setUpTasklistTable() {
  if (!$('tasklist_table')) {
    // No tasklist on the page
    return;
  }
  var table = $('tasklist_table');
  addEvent(table,'click',tasklistTableClick);
}
function tasklistTableClick(e) {
  var src = eventGetSrc(e);
  if (src.nodeName != 'TD') {
    return;
  }
  if (src.hasChildNodes()) {
    var checkBoxes = src.getElementsByTagName('input');
    if (checkBoxes.length > 0) {
      // User clicked the cell where the task select checkbox is
      if (checkBoxes[0].checked) {
        checkBoxes[0].checked = false;
      } else {
        checkBoxes[0].checked = true;
      }
      return;
    }
  }
  var row = src.parentNode;
  var aElements = row.getElementsByTagName('A');
  if (aElements.length > 0) {
    window.location = aElements[0].href;
  } else {
    // If both the task id and the task summary columns are non-visible
    // just use the good old way to get to the task
    window.location = '?do=details&task_id=' + row.id.substr(4);
  }
}

function eventGetSrc(e) {
  if (e.target) {
    return e.target;
  } else if (window.event) {
    return window.event.srcElement;
  } else {
    return;
  }
}

function ToggleSelected(id) {
  var inputs = $(id).getElementsByTagName('input');
  for (var i = 0; i < inputs.length; i++) {
    if(inputs[i].type == 'checkbox'){
      inputs[i].checked = !(inputs[i].checked);
    }
  }
}

function addUploadFields(id) {
  if (!id) {
    id = 'uploadfilebox';
  }
  var el = $(id);
  var span = el.getElementsByTagName('span')[0];
  if ('none' == span.style.display) {
    // Show the file upload box
    span.style.display = 'inline';
    // Switch the buttns
    $(id + '_attachafile').style.display = 'none';
    $(id + '_attachanotherfile').style.display = 'inline';

  } else {
    // Copy the first file upload box and clear it's value
    var newBox = span.cloneNode(true);
    newBox.getElementsByTagName('input')[0].value = '';
    el.appendChild(newBox);
  }
}

function addLinkField(id) {
     if(!id) {
	 id = 'addlinkbox';
     }
     var el = $(id);
     var span = el.getElementsByTagName('span')[0];
     if('none' == span.style.display) {

         span.style.display = 'inline';

         $(id + '_addalink').style.display = 'none';
	 $(id + '_addanotherlink').style.display = 'inline';
      } else {

        var newBox = span.cloneNode(true);
        newBox.getElementsByTagName('input')[0].value = '';
        el.appendChild(newBox);
      }
}

function adduserselect(url, user, selectid, error)
{
    var myAjax = new Ajax.Request(url, {method: 'post', parameters: 'id=' + user, onComplete:function(originalRequest)
	{
        if(originalRequest.responseText) {
            var user_info = originalRequest.responseText.split('|');
            // Check if user does not yet exist
            for (i = 0; i < $('r' + selectid).options.length; i++) {
                if ($('r' + selectid).options[i].value == user_info[1]) {
                    return;
                }
            }

            opt = new Option(user_info[0], user_info[1]);OB
            try {
                $('r' + selectid).options[$('r' + selectid).options.length]=opt;
                updateDualSelectValue(selectid);
            } catch(ex) {
                return;
            }
        } else {
            alert(error);
        }
	}});
}
function checkok(url, message, form) {

    var myAjax = new Ajax.Request(url, {method: 'get', onComplete:function(originalRequest)
	{
        if(originalRequest.responseText == 'ok' || confirm(message)) {
            $(form).submit();
        }
	}});
    return false;
}
function removeUploadField(element, id) {
  if (!id) {
    id = 'uploadfilebox';
  }
  var el = $(id);
  var span = el.getElementsByTagName('span');
  if (1 == span.length) {
    // Clear and hide the box
    span[0].style.display='none';
    span[0].getElementsByTagName('input')[0].value = '';
    // Switch the buttons
    $(id + '_attachafile').style.display = 'inline';
    $(id + '_attachanotherfile').style.display = 'none';
  } else {
    el.removeChild(element.parentNode);
  }
}

function removeLinkField(element, id) {
    if(!id) {
        id = 'addlinkbox';
    }
    var el = $(id);
    var span = el.getElementsByTagName('span');
    if (1 == span.length) {
       span[0].style.display='none';
       span[0].getElementsByTagName('input')[0].value = '';

       $(id + '_addalink').style.display = 'inline';
       $(id + '_addanotherlink').style.display = 'none';
    } else {
       el.removeChild(element.parentNode);
    }
}

function updateDualSelectValue(id)
{
    var rt  = $('r'+id);
    var val = $('v'+id);
    val.value = '';

    var i;
    for (i=0; i < rt.options.length; i++) {
        val.value += (i > 0 ? ' ' : '') + rt.options[i].value;
    }
}
function dualSelect(from, to, id) {
    if (typeof(from) == 'string') {
	from = $(from+id);
    }
    if (typeof(to) == 'string') {
        var to_el = $(to+id);
	// if (!to_el) alert("no element with id '" + (to+id) + "'");
	to = to_el;
    }

    var i;
    var len = from.options.length;
    for(i=0;i<len;++i) {
	if (!from.options[i].selected) continue;
	if (to && to.options)
	    to.appendChild(from.options[i]);
	else
	    from.removeChild(from.options[i]);
	// make the option that is slid down selected (if any)
	if (len > 1)
	    from.options[i == len - 1 ? len - 2 : i].selected = true;
	break;
    }

    updateDualSelectValue(id);
}

function selectMove(id, step) {
    var sel = $('r'+id);

    var i = 0;

    while (i < sel.options.length) {
        if (sel.options[i].selected) {
            if (i+step < 0 || i+step >= sel.options.length) {
                return;
            }
	    if (i + step == sel.options.length - 1)
		sel.appendChild(sel.options[i]);
	    else if (step < 0)
		sel.insertBefore(sel.options[i], sel.options[i+step]);
	    else
		sel.insertBefore(sel.options[i], sel.options[i+step+1]);
            updateDualSelectValue(id);
            return;
        }
        i++;
    }
}
var Cookie = {
  getVar: function(name) {
    var cookie = document.cookie;
    if (cookie.length > 0) {
      cookie += ';';
    }
    re = new RegExp(name + '\=(.*?);' );
    if (cookie.match(re)) {
      return RegExp.$1;
    } else {
      return '';
    }
  },
  setVar: function(name,value,expire,path) {
    document.cookie = name + '=' + value;
  },
  removeVar: function(name) {
    var date = new Date(12);
    document.cookie = name + '=;expires=' + date.toUTCString();
  }
};
function setUpSearchBox() {
  if ($('advancedsearch')) {
    var state = Cookie.getVar('advancedsearch');
    if ('1' == state) {
      var showState = $('advancedsearchstate');
      showState.replaceChild(document.createTextNode('+'),showState.firstChild);
      $('sc2').style.display = 'block';
    }
  }
}
function toggleSearchBox(themeurl) {
  var state = Cookie.getVar('advancedsearch');
  if ('1' == state) {
      $('advancedsearchstateimg').src = themeurl + 'edit_add.png';
      hidestuff('sc2');
      Cookie.setVar('advancedsearch','0');
  } else {
      $('advancedsearchstateimg').src = themeurl + 'edit_remove.png';
      showstuff('sc2');
      Cookie.setVar('advancedsearch','1');
  }
}
function deletesearch(id, url) {
    var img = $('rs' + id).getElementsByTagName('img')[0].src = url + 'themes/CleanFS/ajax_load.gif';
    url = url + 'js/callbacks/deletesearches.php';
    var myAjax = new Ajax.Request(url, {method: 'get', parameters: 'id=' + id,
                     onSuccess:function()
                     {
                        var oNodeToRemove = $('rs' + id);
                        oNodeToRemove.parentNode.removeChild(oNodeToRemove);
                        var table = $('mysearchestable');
                        if(table.rows.length > 0) {
                            table.getElementsByTagName('tr')[table.rows.length-1].style.borderBottom = '0';
                        } else {
                            showstuff('nosearches');
                        }
                     }
                });
}
function savesearch(query, baseurl, savetext) {
    url = baseurl + 'js/callbacks/savesearches.php?' + query + '&search_name=' + encodeURIComponent($('save_search').value);
    if($('save_search').value != '') {
        var old_text = $('lblsaveas').firstChild.nodeValue;
        $('lblsaveas').firstChild.nodeValue = savetext;
        var myAjax = new Ajax.Request(url, {method: 'get',
                     onComplete:function()
                     {
                        $('lblsaveas').firstChild.nodeValue=old_text;
                        var myAjax2 = new Ajax.Updater('mysearches', baseurl + 'js/callbacks/getsearches.php', { method: 'get'});
                     }
                     });
    }
}
function activelink(id) {
    if($(id).className == 'active') {
        $(id).className = 'inactive';
    } else {
        $(id).className = 'active';
    }
}
var useAltForKeyboardNavigation = false;  // Set this to true if you don't want to kill
                                         // Firefox's find as you type

function emptyElement(el) {
    while(el.firstChild) {
        emptyElement(el.firstChild);
        var oNodeToRemove = el.firstChild;
        oNodeToRemove.parentNode.removeChild(oNodeToRemove);
    }
}
function showPreview(textfield, baseurl, field)
{
    var preview = $(field);
    emptyElement(preview);

    var img = document.createElement('img');
    img.src = baseurl + 'themes/CleanFS/ajax_load.gif';
    img.id = 'temp_img';
    img.alt = 'Loading...';
    preview.appendChild(img);

    var text = $(textfield).value;
    text = encodeURIComponent(text);
    var url = baseurl + 'js/callbacks/getpreview.php';
    var myAjax = new Ajax.Updater(field, url, {parameters:'text=' + text, method: 'post'});

    if (text == '') {
        hidestuff(field);
    } else {
        showstuff(field);
    }
}
function checkname(value){
    // FIXME: If username contains anything that is not a number or a digit, then show an error and don't let them register
    var re=/^[A-Za-z0-9]*$/;

    if (re.test(value)==false)
    {
        $('username').style.color ='red';
        $('buSubmit').style.visibility = 'hidden';
        $('errormessage').innerHTML = booler.substring(6,booler.length);
    }
    // Otherwise check if username already exists
    else
    {
      new Ajax.Request('js/callbacks/searchnames.php?name='+value, {onSuccess: function(t){ allow(t.responseText); } });
    }
}
function allow(booler){
    if(booler.indexOf('false') > -1) {
        $('username').style.color ='red';
        $('buSubmit').style.visibility = 'hidden';
        $('errormessage').innerHTML = booler.substring(6,booler.length);
    }
    else {
        $('username').style.color ='green';
        $('buSubmit').style.visibility = 'visible';
        $('errormessage').innerHTML = '';
    }
}
function getHistory(task_id, baseurl, field, details)
{
    var url = baseurl + 'js/callbacks/gethistory.php?task_id=' + task_id;
    if (details) {
        url += '&details=' + details;
    }
    var myAjax = new Ajax.Updater(field, url, { method: 'get'});
}

/*********  Permissions popup  ***********/

function createClosure(obj, method) {
    return (function() { obj[method](); });
}

function Perms(id) {
    this.div = $(id);
}

Perms.prototype.timeout = null;
Perms.prototype.div     = null;

Perms.prototype.clearTimeout = function() {
    if (this.timeout) {
        clearTimeout(this.timeout);
        this.timeout = null;
    }
}

Perms.prototype.do_later = function(action) {
    this.clearTimeout();
    closure = createClosure(this, action);
    this.timeout = setTimeout(closure, 400);
}

Perms.prototype.show = function() {
    this.clearTimeout();
    this.div.style.display = 'block';
    this.div.style.visibility = 'visible';
}

Perms.prototype.hide = function() {
    this.clearTimeout();
    this.div.style.display = 'none';
}

// Replaces the currently selected text with the passed text.
function replaceText(text, textarea)
{
	textarea = document.getElementById( textarea );
	// Attempt to create a text range (IE).
	if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange)
	{
		var caretPos = textarea.caretPos;

		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		caretPos.select();
	}
	// Mozilla text range replace.
	else if (typeof(textarea.selectionStart) != "undefined")
	{
		var begin = textarea.value.substr(0, textarea.selectionStart);
		var end = textarea.value.substr(textarea.selectionEnd);
		var scrollPos = textarea.scrollTop;

		textarea.value = begin + text + end;

		if (textarea.setSelectionRange)
		{
			textarea.focus();
			textarea.setSelectionRange(begin.length + text.length, begin.length + text.length);
		}
		textarea.scrollTop = scrollPos;
	}
    else if (document.selection) {
        textarea.focus();
        sel=document.selection.createRange();
        sel.text=text;
    }
	// Just put it on the end.
	else
	{
		textarea.value += text;
		textarea.focus(textarea.value.length - 1);
	}
}


// Surrounds the selected text with text1 and text2.
function surroundText(text1, text2, textarea)
{
	textarea = document.getElementById( textarea );
	// Can a text range be created?
	if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange)
	{
		var caretPos = textarea.caretPos;

		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text1 + caretPos.text + text2 + ' ' : text1 + caretPos.text + text2;
		caretPos.select();
	}
	// Mozilla text range wrap.
	else if (typeof(textarea.selectionStart) != "undefined")
	{
		var begin = textarea.value.substr(0, textarea.selectionStart);
		var selection = textarea.value.substr(textarea.selectionStart, textarea.selectionEnd - textarea.selectionStart);
		var end = textarea.value.substr(textarea.selectionEnd);
		var newCursorPos = textarea.selectionStart;
		var scrollPos = textarea.scrollTop;

		textarea.value = begin + text1 + selection + text2 + end;

		if (textarea.setSelectionRange)
		{
			if (selection.length == 0)
				textarea.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
			else
				textarea.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);
			textarea.focus();
		}
		textarea.scrollTop = scrollPos;
	}
    else if(document.selection) {
        textarea.focus();
        var sampleText = 'TEXT';
        var currentRange = document.selection.createRange();
        var selection = currentRange.text;
        var replaced = true;
        if(!selection) {
               replaced=false;
               selection = sampleText;
        }
        if(selection.charAt(selection.length-1)==" "){
               selection=selection.substring(0,selection.length-1);
               currentRange.text = text1 + selection + text2 + " ";
        }
        else
        {
               currentRange.text = text1 + selection + text2;
        }
        if(!replaced){
               // If putting in sample text (i.e. insert) adjust range start and end
               currentRange.moveStart('character',-text.length-text2.length);
               currentRange.moveEnd('character',-text2.length);
        }
        currentRange.select();
    }
	// Just put them on the end, then.
	else
	{
		textarea.value += text1 + text2;
		textarea.focus(textarea.value.length - 1);
	}
}

function stopBubble(e) {
	if (!e) { var e = window.event; }
	e.cancelBubble = true;
	if (e.stopPropagation) { e.stopPropagation(); }
}

//login box toggling
login_box_status = false;
var toggleLoginBox = function(el){
    //this would turn the box on
    if(login_box_status == false){
        el.addClassName('active');
        showstuff('loginbox');
    //this would turn the box off
    } else {
        el.removeClassName('active');
        hidestuff('loginbox');
    }
    //toggle functionality
    if(login_box_status == true) login_box_status = false;
    else login_box_status = true;
    //return false to stop event bubbling
    return false;
}
