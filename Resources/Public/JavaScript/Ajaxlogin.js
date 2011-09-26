var Ajaxlogin = Ajaxlogin || {};

(function($) {
	Ajaxlogin = {
		_eventListeners: {},
		User: {
			info: function() {
				if ( tx_ajaxlogin.api.User.info ) {
					$.ajax({
						url: tx_ajaxlogin.api.User.info,
						cache: false,
						error: function(a,b,c) {
							Ajaxlogin.fn.showLoginForm(a);
							Ajaxlogin.event.fire('widget_load');
						},
						success: function(a,b,c) {
							Ajaxlogin.fn.showUserInfo(c);
							Ajaxlogin.event.fire('widget_load');
						}
					});
				} else {
					// check the plugin.tx_ajaxlogin.view.ajaxPid property
				}
			},
			login: function() {
				if ( tx_ajaxlogin.api.User.login ) {
					$.ajax({
						url: tx_ajaxlogin.api.User.login,
						cache: false,
						success: function(a,b,c) {
							Ajaxlogin.fn.showLoginForm(c);
							Ajaxlogin.event.fire('widget_load');
						}
					});
				}
			},
			logout: function() {
				if ( tx_ajaxlogin.api.User.logout ) {
					$.ajax({
						url: tx_ajaxlogin.api.User.logout,
						cache: false,
						success: function(a,b,c) {
							Ajaxlogin.fn.showLoginForm(c);
							Ajaxlogin.event.fire('logout_success', [c]);
							Ajaxlogin.event.fire('widget_load');
						}
					});
				}
			},
			'new': function() {
				if ( tx_ajaxlogin.api.User['new'] ) {
					$.ajax({
						url: tx_ajaxlogin.api.User['new'],
						cache: false,
						success: function(a,b,c) {
							Ajaxlogin.fn.showSignupForm(c);
							Ajaxlogin.event.fire('widget_load');
						}
					});
				}
			},
			forgotPassword: function() {
				if ( tx_ajaxlogin.api.User.forgotPassword ) {
					$.ajax({
						url: tx_ajaxlogin.api.User.forgotPassword,
						cache: false,
						success: function(a,b,c) {
							Ajaxlogin.fn.showForgotPasswordForm(c);
							Ajaxlogin.event.fire('widget_load');
						}
					});
				}
			}
		},
		fn: {
			showLoginForm: function(response) {
				$(tx_ajaxlogin.statusLabel).html(tx_ajaxlogin.ll.status_unauthorized);
				$(tx_ajaxlogin.placeholder).html(response.responseText).find("a[rel^='tx_ajaxlogin']").Ajaxlogin();
				
				var formEl = $('#' + response.getResponseHeader('X-Ajaxlogin-formToken'));
				
				formEl.submit(function(event) {
					event.preventDefault();
					var input = Ajaxlogin.fn.resolveFormData($(this));
					$.ajax({
						url: tx_ajaxlogin.api.User.authenticate,
						cache: false,
						type: 'POST',
						data: $.extend({
							logintype: 'login',
							pid: tx_ajaxlogin.storagePid,
							referer: window.location.href,
							redirectUrl: tx_ajaxlogin.redirect_url
						}, input),
						error: function(a,b,c) {
							Ajaxlogin.event.fire('login_error', [a]);
							Ajaxlogin.fn.showLoginForm(a);
						},
						success: function(a,b,c){
							Ajaxlogin.event.fire('login_success', [c]);
							Ajaxlogin.fn.showUserInfo(c);
						}
					});
				});
			},
			showSignupForm: function(response) {
				$(tx_ajaxlogin.statusLabel).html(tx_ajaxlogin.ll.status_unauthorized);
				$(tx_ajaxlogin.placeholder).html(response.responseText).find("a[rel^='tx_ajaxlogin']").Ajaxlogin();
				
				var formEl = $('#' + response.getResponseHeader('X-Ajaxlogin-formToken'));
				
				formEl.submit(function(event) {
					event.preventDefault();
					
					if(Ajaxlogin.validate.signup($(this))) {
						var input = Ajaxlogin.fn.resolveFormData($(this));
						
						$.ajax({
							url: tx_ajaxlogin.api.User.create,
							cache: false,
							type: 'POST',
							data: $.extend({
								referer: window.location.href,
								redirectUrl: tx_ajaxlogin.redirect_url
							}, input),
							error: function(a,b,c) {
								Ajaxlogin.event.fire('signup_error', [a]);
								Ajaxlogin.fn.showSignupForm(a);
							},
							success: function(a,b,c) {
								Ajaxlogin.event.fire('signup_success', [c]);
								Ajaxlogin.fn.showUserInfo(c);
							}
						});
					}
				});
			},
			showUserInfo: function(response) {
				$(tx_ajaxlogin.statusLabel).html('<a href="'+tx_ajaxlogin.accountPage+'">' + tx_ajaxlogin.ll.status_authenticated+'</a>');
				$(tx_ajaxlogin.placeholder).html(response.responseText).find("a[rel^='tx_ajaxlogin']").Ajaxlogin();
			},
			showForgotPasswordForm: function(response) {
				$(tx_ajaxlogin.placeholder).html(response.responseText).find("a[rel^='tx_ajaxlogin']").Ajaxlogin();
				
				var formEl = $('#' + response.getResponseHeader('X-Ajaxlogin-formToken'));
				
				formEl.submit(function(event) {
					event.preventDefault();
					var input = Ajaxlogin.fn.resolveFormData($(this));
					
					$.ajax({
						url: tx_ajaxlogin.api.User.resetPassword,
						cache: false,
						type: 'POST',
						data: input,
						error: function(a,b,c) {
							Ajaxlogin.fn.showForgotPasswordForm(a);
							Ajaxlogin.event.fire('widget_load');
						},
						success: function(a,b,c) {
							Ajaxlogin.fn.showForgotPasswordForm(c);
							Ajaxlogin.event.fire('widget_load');
						}
					});
				});
			},
			resolveFormData: function(formEl) {
				var input = {};
				formEl.find('input').each(function() {
					var key = $(this).attr('name');					
					input[key] = $.trim( $(this).val() );
				});
				return input;
			},
			doReloadOrRedirect: function(response) {
				var redirectUrl = response.getResponseHeader('X-Ajaxlogin-redirectUrl');
				
				if (redirectUrl) {
					window.location.href = redirectUrl;
				} else if (tx_ajaxlogin.doReloadOnSuccess == 1) {
					top.location.reload();
				}
			}
		},
		validate: {
			signup: function(form) {
				return true;
				
				var result = true;
				
				$.each(tx_ajaxlogin.validation.confirmationFieldsets, function() {
					var field = true;					
					var value = form.find(this[0]).val();
					var check = form.find(this[1]).val();
					
					if(!value){
						result = false;
						field = false;
					} else if(value != check) {
						result = false;
						field = false;
					}
					
					if(field === false) {
						form.find(this[0]).addClass('tx-ajaxlogin-form-error');
						form.find(this[1]).addClass('tx-ajaxlogin-form-error');
					} else {
						form.find(this[0]).removeClass('tx-ajaxlogin-form-error');
						form.find(this[1]).removeClass('tx-ajaxlogin-form-error');
					}
				});
				
				return result;
			}
		},
		event: {
			addListener: function(type, listener) {
				if (typeof Ajaxlogin._eventListeners[type] == "undefined"){
					Ajaxlogin._eventListeners[type] = [];
		        }

				Ajaxlogin._eventListeners[type].push(listener);
			},
			addListenerOnce: function(type, listener) {
				var add = true;
				
				if (typeof Ajaxlogin._eventListeners[type] == "undefined"){
					Ajaxlogin._eventListeners[type] = [];
		        }
				
				var listeners = Ajaxlogin._eventListeners[type];
				for (var i=0, len=listeners.length; i < len; i++){
					if (listeners[i] === listener){
	                    add = false;
	                    break;
	                }
	            }
				
				if(add) {
					Ajaxlogin._eventListeners[type].push(listener);
				}
			},
			fire: function(event, args) {
				if (typeof event == "string"){
		            event = { type: event };
		        }
		        if (!event.target){
		            event.target = this;
		        }

		        if (!event.type){  //falsy
		            throw new Error("Event object missing 'type' property.");
		        }

		        if (Ajaxlogin._eventListeners[event.type] instanceof Array){
		            var listeners = Ajaxlogin._eventListeners[event.type];
		            for (var i=0, len=listeners.length; i < len; i++){
		                if(listeners[i]) {
		                	listeners[i].apply(event, args);
		                }
		            }
		        }
			},
			removeListener: function(type, listener) {
				if (Ajaxlogin._eventListeners[type] instanceof Array){
		            var listeners = Ajaxlogin._eventListeners[type];
		            for (var i=0, len=listeners.length; i < len; i++){
		                if (listeners[i] === listener){
		                    listeners.splice(i, 1);
		                    break;
		                }
		            }
		        }
			}
		},
		Cookie: {
			create: function(name, value, days){
				if (days) {
					var date = new Date();
					date.setTime(date.getTime()+(days*24*60*60*1000));
					var expires = "; expires="+date.toGMTString();
				}
				else var expires = "";
				document.cookie = name+"="+value+expires+"; path=/";
			},
			read: function(name){
				var nameEQ = name + "=";
				var ca = document.cookie.split(';');
				for(var i=0;i < ca.length;i++) {
					var c = ca[i];
					while (c.charAt(0)==' ') c = c.substring(1,c.length);
					if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
				}
				return null;
			},
			erase: function(name){
				this.create(name,"",-1);
			}
		}
	};

	Ajaxlogin.event.addListener('login_success', Ajaxlogin.fn.doReloadOrRedirect);
	Ajaxlogin.event.addListener('logout_success', Ajaxlogin.fn.doReloadOrRedirect);
	Ajaxlogin.event.addListener('signup_success', Ajaxlogin.fn.doReloadOrRedirect);

	Ajaxlogin.event.addListener('login_success', function() {
		Ajaxlogin.Cookie.create('ajaxlogin_status', '1');
	});
	Ajaxlogin.event.addListener('logout_success', function() {
		Ajaxlogin.Cookie.erase('ajaxlogin_status');
	});
	
	$.fn.Ajaxlogin = function() {
		this.click(function(event) {
			event.preventDefault();
			
			var actionRegExp = /\[(?:.*)\]/;
			var res = actionRegExp.exec($(this).attr('rel'));
			switch(res.toString()) {
				case '[signup]':
					Ajaxlogin.User['new']();
				break;
				case '[forgot_password]':
					Ajaxlogin.User.forgotPassword();
				break;
				case '[login]':
					Ajaxlogin.User.login();
				break;
				case '[logout]':
					Ajaxlogin.User.logout();
				break;
			}
		});
	};
	
	$(document).ready(Ajaxlogin.User.info);
	
	$(document).ready(function() {
		if(Ajaxlogin.Cookie.read('ajaxlogin_status') == '1') {
			$(tx_ajaxlogin.statusLabel).html('<a href="'+tx_ajaxlogin.accountPage+'">' + tx_ajaxlogin.ll.status_authenticated+'</a>');
		}
	});
})(jQuery);