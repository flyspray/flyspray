/**
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_config.html

	// The toolbar groups arrangement, optimized for a single toolbar row.
	config.toolbarGroups = [
		//{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'forms' },
		{ name: 'styles' },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'links' },
		{ name: 'insert' },
		//{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		{ name: 'about' }
	];

	// The default plugins included in the basic setup define some buttons that
	// are not needed in a basic editor. They are removed here.
	//config.removeButtons = 'Cut,Copy,Paste,Undo,Redo,Anchor,Underline,Strike,Subscript,Superscript';
	config.removeButtons = 'Anchor,Underline,Subscript,Superscript';

	// Dialog windows are also simplified.
	config.removeDialogTabs = 'link:advanced';

	// h1 for Flyspray page title
	// h2 for task title
	// But better allow them also for task description and comment for backward compatibility.
	config.format_tags = 'p;h1;h2;h3;h4;h5;pre';
	
	config.mentions=[
		{
			minChars: 0,
			marker: '@',
			feed: function( options, callback ) {
				var xhr = new XMLHttpRequest();

				xhr.onreadystatechange = function() {
					if ( xhr.readyState == 4 ) {
						if ( xhr.status == 200 ) {
							callback( JSON.parse( this.responseText ) );
						} else {
							callback( [] );
						}
					}
				}

				// @todo send project id to get the best matching users for the task/comment.
				params= 'username=' + encodeURIComponent( options.query );

				xhr.open( 'POST', 'js/callbacks/usersearch.php');
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhr.send(params);
			}
		}
	];
};
