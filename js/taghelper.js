document.addEventListener('DOMContentLoaded', taghelperevents);

function taghelperevents() {
	renderTags();
	var tags = document.getElementById('tags');
	if (tags === null) {
		return false;
	}

	tags.addEventListener('change', renderTags);

	var availtags=document.querySelectorAll('#availtaglist i');
	if (availtags) {
		var atcount=availtags.length;
		for (let i = 0; i < atcount; i++) {
			availtags[i].addEventListener('click', addTag);
		}
	}
	var addedtags = document.querySelector('#tagrender i');
	if (addedtags) {
		var addcount = addedtags.length;
		for (let j = 0; j < addcount; j++) {
			addedtags[j].addEventListener('click', removeTag);
		}
	}
}

function renderTags(event) {
	var tags = document.getElementById('tags');
	if (tags === null) {
		return false;
	}
	tags=tags.value;

	var taglist=tags.split(';');
	//console.log(taglist);
	var tagrenderarea=document.getElementById('tagrender');
	//console.log(tagrenderarea);
	while (tagrenderarea.firstChild) {
		tagrenderarea.removeChild(tagrenderarea.firstChild);
	}

	for (tag of taglist) {
		if (tag!='') {
			var newtag = document.createElement('i');
			newtag.setAttribute('title', tag);
			newtag.setAttribute('class', 'tag');
			newtag.content='X';
			// todo: get tagid, and class/style info from available tag list
			var addedtag=tagrenderarea.appendChild(newtag);
			addedtag.addEventListener('click', removeTag);
			console.log(newtag);
		}
	}
}

function addTag(event) {
	var tags = document.getElementById('tags').value;
	var taglist=tags.split(';');

	var index = taglist.findIndex(tag => tag === event.target.getAttribute('title'));

	console.log(index);
	if (index >= 0){
		// exists
		return false;
	} else {
		if (tags !='') {                        
			document.getElementById('tags').value += ';' + event.target.getAttribute('title');
		} else {
			document.getElementById('tags').value += event.target.getAttribute('title');
		}
		//console.log(event.target);
	}
	renderTags();
}

function removeTag(event) {
	var oldtags = document.getElementById('tags').value;
	var oldtaglist = oldtags.split(';');
	var newstring = '';
	//console.log(event.target.title);
	for (oldtag of oldtaglist) {
		if (oldtag != '') {
			if (oldtag == event.target.title) {
				/* nothing */
				//console.log('remove');
			} else {
				if (newstring!='') {
					newstring += ';';
				}
				newstring += oldtag;
				//console.log('keep!');
			}
		}
	}
	event.target.remove();
	document.getElementById('tags').value = newstring;
}
