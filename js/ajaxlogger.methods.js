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

function doLoginDialogLogin()
{
	$('#login_dialog_status').statusbox('display','Logging in...');
	var pass = $('#login_dialog_password').val();
	$.getJSON('ui.php', {task:'login',password:pass},doLoginDialogLoginCallback);
}
function doLoginDialogLoginCallback(res)
{
	if (res['status'] == true)
	{
		authcode = res['data']['id'];
		$('#Login_Dialog').dialog('close');
		doAppDialogShow();
		var acopts = {};
		acopts['cacheLength'] = 10;
		acopts['extraParams'] = { task: 'autocomplete_file', id:res['data']['id'] };
		acopts['mustMatch'] = true;
		acopts['scroll'] = false;
		acopts['selectFirst'] = false;
		$('#file_filter').autocomplete('ui.php', acopts);
		$('#file_filter').unbind('result');
		$('#file_filter').result(function(event, data, formatted) { if (data) { lookup_file = formatted; runUpdate(); }});
		var acopts2 = {};
		acopts2['cacheLength'] = 10;
		acopts2['extraParams'] = { task: 'autocomplete_function', id:res['data']['id'] };
		acopts2['mustMatch'] = true;
		acopts2['scroll'] = false;
		acopts2['selectFirst'] = false;
		$('#function_filter').autocomplete('ui.php', acopts2);
		$('#function_filter').unbind('result');
		$('#function_filter').result(function(event, data, formatted) { if (data) { lookup_function = formatted; runUpdate(); }});
		$('#file_filter').focus();
		$('#file_filter').select();
		$('#filters input').focus(function() { $(this).select(); });
		startUpdate();
	} else {
		$('#login_dialog_status').statusbox('display',res['msg']);
	}
}
function clearFileFilter()
{
	lookup_file = '';
	$('#file_filter').val('');
	$('#file_filter').focus();
	runUpdate();
}
function clearFunctionFilter()
{
	lookup_function = '';
	$('#function_filter').val('');
	$('#function_filter').focus();
	runUpdate();
}
function setFileFilterById(id)
{
	var o = $('#'+id+' .file a');
	var fi = o.html();
	$('#file_filter').val(fi);
	lookup_file = fi;
	last_update = 0;
	runUpdate();
}
function setFunctionFilterById(id)
{
	var o = $('#'+id+' .function a');
	var fi = o.html();
	$('#function_filter').val(fi);
	lookup_function = fi;
	last_update = 0;
	runUpdate();
}