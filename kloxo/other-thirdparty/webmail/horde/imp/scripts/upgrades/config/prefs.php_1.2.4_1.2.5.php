$contacts_app = $GLOBALS['registry']->hasInterface('contacts');
if (!$contacts_app && !$GLOBALS['registry']->hasPermission($contacts_app)) {
    unset($prefGroups['addressbooks']);
}

$_prefs['fckeditor_buttons']['value'] = "[['Source','FitWindow','-','Templates'],['Cut','Copy','Paste','PasteText','PasteWord'],['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],'/',['OrderedList','UnorderedList','-','Outdent','Indent','Blockquote'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],['Link','Unlink'],['Image','Flash','Table','Rule','Smiley','SpecialChar'],'/',['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],['TextColor','BGColor'],'/',['Style','FontFormat','FontName','FontSize']]";
// Use the following line for a very basic set of buttons:
// $_prefs['fckeditor_buttons']['value'] = "['Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink']";

$_prefs['nav_audio']['value'] = '';
