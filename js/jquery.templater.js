/*
 * Templater jQuery Plugin
 * http://www.poweredbyjam.com/
 *
 * To use:
 * 
 * HTML:
 * 
 * <div id="template_container">
 *   <a href="__LINK__" title="__TITLE__">__TITLE__</a>
 * </div>
 *   
 * var params = {link:'http://www.example.com',title:'some title'};
 * var link_html = $('#template_container').templater(params);
 *
 * Copyright (c) 2009 Jacob Mather
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Date: 2009-08-01 10:18:21 -0400 (Mon, 1 Aug 2009)
 * Revision: 1
 */
(function($){
$.fn.templater = function(data)
{
	var rep = {};
	for(var i in data)
	{
		rep['__'+i.toUpperCase()+'__'] = data[i];
	}
	var out = $(this).html();
	for (var i in rep)
	{
		var re = new RegExp(i, 'g');
		out = out.replace(re, rep[i]);
	}
	return out;
};
})(jQuery);