/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.language = "en";
	config.extraPlugins = 'tokens';

    config.toolbar = 'Custom';

    config.toolbar_Custom =
    [
        ['Source','tokens','Templates'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-'],
        ['Undo','Redo','-','Find','Replace'],
        ['Table','HorizontalRule','SpecialChar','PageBreak'],
        ['SelectAll','RemoveFormat'],
        '/',
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['BidiLtr', 'BidiRtl'],
        ['Link','Unlink','Anchor'],
        ['Styles','Format','Font','FontSize'],
        ['TextColor','BGColor'],
        ['Maximize', 'ShowBlocks']
    ];


};
