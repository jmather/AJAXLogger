/*
 * AJAXLogger
 * http://www.poweredbyjam.com/
 *
 * Copyright (c) 2009 Jacob Mather
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Date: 2009-08-01 10:18:21 -0400 (Mon, 1 Aug 2009)
 * Revision: 1
 */
		
function buildDialogs()
{
	doAppDialogBuild();
	doLoginDialogBuild();
	$('#login_dialog_password').keypress(function(event) { if (isEnter(event)) { doLoginDialogLogin();}});
}
function doLoginDialogShow()
{
	$('#Login_Dialog').dialog('open');
}
function doLoginDialogBuild() {
	var dlg_params = {
		autoOpen : false,
		overlay: { opacity: 0.5, background: "black" },
		closeOnEscape : true,
		draggable : false,
		modal : true,
		resizable : false,
		close : doLoginDialogClose,
		open : function() { authcode = null; $('#login_dialog_password').focus(); },
		width : 480,
		title : 'Login',
		buttons : {
			"Login" : function() {
				doLoginDialogLogin();
				$('#login_dialog_password').focus();
			}
		}
	};
	$('#Login_Dialog').dialog(dlg_params);
	$('#login_dialog_status').statusbox();
}
function doLoginDialogClose()
{
	$('#login_dialog_password').val('');
	$('#login_dialog_status').hide();
	$('#login_dialog_status').html('');
	if (authcode == null)
		setTimeout("$('#Login_Dialog').dialog('open');", 200);
}
function doAppDialogShow()
{
	$('#App_Dialog').dialog('open');	
}
function doAppDialogBuild() {
	var dlg_params = {
		autoOpen : false,
		closeOnEscape : true,
		overlay: { opacity: 0.5, background: "black" },
		draggable : false,
		modal : true,
		resizable : false,
		close : doAppDialogClose,
		width : 500,
		height: 500,
		title : 'AJAX Logger',
		buttons : {
			"Refresh" : function() {
				listWatcher.abort();
				runUpdate();
			}
		}
	};
	$('#App_Dialog').dialog(dlg_params);
	$('#status').statusbox();
}
function doAppDialogClose()
{
	$('#items').html('');
	$('#file_filter').val('');
	$('#function_filter').val('');
	lookup_file = '';
	lookup_function = '';
	last_file = '';
	last_function = '';
	listWatcher.abort();
	doLoginDialogShow();
	clearTimeout(refresh_timer);
}