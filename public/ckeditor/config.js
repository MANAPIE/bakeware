/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.language = 'ko';
	
	config.toolbarGroups = [
		{ name: 'clipboard', groups: [ 'undo', 'clipboard' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'links', groups: [ 'links' ] },
		'/',
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'paragraph', groups: [ 'align', 'bidi', 'indent', 'list', 'blocks', 'paragraph' ] },
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];
	
	config.removeButtons = 'Font,About,Templates,Save,NewPage,Preview,Print,Scayt,HiddenField,Flash,Language,Image';
	
	config.disallowedContent = 'img{width,height}';
	config.image_previewText = '';
	
	config.contentsCss = '/style/editor.css';
	
	config.dialog_noConfirmCancel = true;
	
	config.extraPlugins = 'doksoft_easy_image';
	config.removePlugins = 'autosave,autoGrow';
	
	config.filebrowserUploadUrl = '/upload/image';
};
		
CKEDITOR.on('instanceReady',function(){
	$('.cke_button__doksoft_easy_image').append('<div style="position:relative" onclick="$(this).fadeOut();"><div style="position:absolute;top:13px;left:2px;width:0;height:0;border-bottom:5px solid #999;border-right:5px solid transparent"></div><div style="position:absolute;top:18px;left:2px;background:#999;color:#fff;padding:2px 3px;font-size:10px">이미지 삽입</div></div>');
});
CKEDITOR.on('dialogDefinition', function (ev) {
	var dialogName = ev.data.name;
	var dialog = ev.data.definition.dialog;
	
	if (dialogName == 'image') {
		dialog.on('show', function () {
			this.selectPage('Upload');
		});
	}
});
