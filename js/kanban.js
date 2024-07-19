var dragged;
var colslength;

var stoppropag= function(e){
	e.stopImmediatePropagation();
}

var itemdragstart = function(e){
	// copy reservation item values to drag item
	dragged = event.target;
	// give original item a different css look, like opacity:0.4
	console.log(dragged);

	cols=document.querySelectorAll('.kanbanboard .collist');
	colslength=cols.length;
	for (let i = 0; i < colslength; i++) {
		cols[i].classList.add("dropzone");
	}
}

var itemdrag = function(e){
	console.log('drag isdrag:'+isdrag);
	if(isdrag===true){
		//move drag item
		this.style.backgroundColor=Math.rand(255);
	}
}

var itemdragend= function() {
	cols=document.querySelectorAll('.kanbanboard .collist');
	colslength=cols.length;
	for (let i = 0; i < colslength; i++) {
		cols[i].classList.remove("dropzone");
	}
}

document.addEventListener('DOMContentLoaded', eventregistering);

function eventregistering(){
	tasks=document.querySelectorAll('.kanbanboard .collist .task');
	taskslength=tasks.length;
	for (var i = 0; i < taskslength; i++) {
		tasks[i].addEventListener("click", stoppropag);
		tasks[i].addEventListener("mousemove", stoppropag);

		// TODO: only apply to tasks which the current user allowed to modify.
		tasks[i].addEventListener("dragstart", itemdragstart);
		tasks[i].addEventListener("dragend", itemdragend);
	}

	cols=document.querySelectorAll('.kanbanboard .collist');
	colslength=tasks.length;
	for (var i = 0; i < cols.length; i++) {
		cols[i].addEventListener("dragenter", function () {
			this.classList.add('over');
		});

		cols[i].addEventListener('dragleave', function () {
			this.classList.remove('over');
		});

		cols[i].addEventListener('dragover', function (evt) {
			evt.preventDefault();
		});
	}

	document.addEventListener('drop', function( event ) {
		console.log('droplistener:');
		console.log(event);
		console.log(this);
		console.log(event.target.className);
		// prevent default action (e.g open as link for some elements)
		event.preventDefault();
		// move dragged elem to the selected drop target
		console.log(event.target);
		//console.log(event.target.attributes['class'].value);
		console.log(dragged);

		if ( event.target.classList.contains('dropzone')) {
			dragged.parentNode.removeChild( dragged );
			event.target.appendChild( dragged );
		}
	}, false);

	switches = document.querySelectorAll('.kanbanboard .col > .colname > label');

	for (i = 0; i < switches.length; i++) {
		switches[i].addEventListener('click', function (e) {
			e.preventDefault();

			var t = this.parentNode.parentNode;
			var fa = this.children[0];

			if (t.classList.contains('collapsed')) {
				t.classList.remove('collapsed');
				fa.classList.replace('fa-maximize', 'fa-minimize');
			} else {
				t.classList.add('collapsed');
				fa.classList.replace('fa-minimize', 'fa-maximize');
			}
		});
	}
}
