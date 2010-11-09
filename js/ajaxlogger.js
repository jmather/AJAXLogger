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

var refresh_timer = null;
var authcode = null;
var last_update = 0;
var last_idx = 0;
var last_file = '';
var last_function = '';
var lookup_file = '';
var lookup_function = '';
var listWatcher = null;
$(function() {
	listWatcher = $.manageAjax.create('listWatcher',{queue: true,maxRequests:1});
	buildDialogs();
	doLoginDialogShow();
});

function isEnter(e) {
    if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
            return true;
    }
    return false;
}
function startUpdate()
{
	last_update = 0;
	last_file = '';
	last_function = '';
	runUpdate();
}
function runUpdate()
{
	listWatcher.abort();
	if (refresh_timer)
		clearTimeout(refresh_timer);
	var d = {};
	d['task'] = 'getList';
	d['id'] = authcode;
	d['file'] = lookup_file;
	d['function'] = lookup_function;

	if (last_file != d['file'] || last_function != d['function'])
	{
		$('#items').html('');
		last_update = 0;
	}
	last_file = d['file'];
	last_function = d['function'];
	d['last_update'] = last_update;
	d['last_id'] = last_idx;
	
	var req = {
		success: runUpdateCallback,
		cache: false,
		data: d,
		error: runUpdate,
		global: false,
		url: 'ui.php',
		dataType: 'json'
	};
	
	listWatcher.add(req);
//	$.getJSON('ui.php', d, runUpdateCallback);
}
function runUpdateCallback(res)
{
	if (res['status'] == 'failure')
	{
		$('#status').statusbox('display', res['msg'], true);
		return;
	}
	var ul = $('#items'); 
	var oldhtml = ul.html();
	var lihtml = '';
	var addhtml = '';
	
	for (var i in res['data']['data'])
	{
		var o = res['data']['data'][i];
		var D = new Date();
		D.setTime(parseInt(o['ts'])*1000);
		var r = {
				id: o['ts']+'_'+o['id'],
				file: o['file'],
				func: o['function'],
				message: o['message'],
				timestamp: D.toLocaleString()
		};
		addhtml += $('#entry_template').templater(r);
	}
	
	ul.html(addhtml+oldhtml);
	$('#items li:odd').css('background-color', '#ffffff');
	$('#items li:even').css('background-color', '#dddddd');
	if (oldhtml != '')
	{
		for (var i in res['data']['data'])
		{
			var o = res['data']['data'][i];
			var mid = o['ts']+'_'+o['id'];
			$('#'+mid).effect('highlight', {}, 2000);
		}
	}
	last_update = res['data']['time'];
	refresh_timer = setTimeout(runUpdate, 3000);
}