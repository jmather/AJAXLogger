/*
 * Status Box jQuery Plugin
 * http://www.poweredbyjam.com/
 *
 * To init:
 * Call $('#element').statusbox();
 *
 * To update:
 * Call $('#element').statusbox('display', 'This Message');
 * 
 * Open, Close, and Colide all pass 1 parameter, the jQuery object being manipulated.
 * 
 * To set a new open style:
 * $('#element').statusbox('open', function(obj) { obj.show('slow'); });
 *
 * Copyright (c) 2009 Jacob Mather
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Date: 2009-08-01 10:18:21 -0400 (Mon, 1 Aug 2009)
 * Revision: 1
 */
(function($) {
	$.widget('ui.statusbox', {
		_init : function() {
			this.element.hide().attr('role', 'statusbox');
			this.options['timer'] = null;
		},
		destroy : function() {
			$.widget.prototype.destroy.apply(this, arguments);
			return this;
		},
		_setData : function(key, value) {
			if (key == 'msg') {
				this.html(value);
			} else {
				this.options[key] = value;
			}
		},
		display : function(message, noclear) {
			var tF = function(obj) {
				return function() {
					obj.options['close'](obj.element);
					obj.options['timer'] = null;
				};
			};
			if (this.options['timer'] != null) {
				clearTimeout(this.options['timer']);
				this.element.html(message);
				this.options['collide'](this.element);
			} else {
				this.element.html(message);
				this.options['open'](this.element);
			}
			if (noclear == 'undefined' || noclear == null || (noclear && noclear == false))
				this.options['timer'] = setTimeout(tF(this),
					this.options['duration']);
		}
	});
	$.extend($.ui.statusbox, {
		version : '1.0.0',
		eventPrefix : 'statusbox',
		defaults : {
			duration : 2000,
			open : function(obj) {
				obj.slideDown('slow');
			},
			close : function(obj) {
				obj.slideUp('slow');
			},
			collide : function(obj) {
				obj.effect('highlight', {}, 2000);
			}
		}
	});

})(jQuery);