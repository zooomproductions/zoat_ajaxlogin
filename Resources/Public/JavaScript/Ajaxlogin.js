(function($) {
	var Ajaxlogin = {
		User: {
			info: function() {
				$.ajax({
					url: tx_ajaxlogin.api.User.info,
					cache: false,
					error: function(a,b,c) {
						Ajaxlogin.fn.showLoginForm(a);
					},
					success: function(a,b,c) {
						Ajaxlogin.fn.showUserInfo(c);
					}
				});
			},
			login: function() {
				$.ajax({
					url: tx_ajaxlogin.api.User.login,
					cache: false,
					success: function(a,b,c) {
						Ajaxlogin.fn.showLoginForm(c);
					}
				});
			},
			logout: function() {
				$.ajax({
					url: tx_ajaxlogin.api.User.logout,
					cache: false,
					success: function(a,b,c) {
						Ajaxlogin.fn.doReloadOrRedirect();
						Ajaxlogin.fn.showLoginForm(c);
					}
				});
			},
			'new': function() {
				$.ajax({
					url: tx_ajaxlogin.api.User['new'],
					cache: false,
					success: function(a,b,c) {
						Ajaxlogin.fn.showSignupForm(c);
					}
				});
			},
			forgotPassword: function() {
				$.ajax({
					url: tx_ajaxlogin.api.User.forgotPassword,
					cache: false,
					success: function(a,b,c) {
						Ajaxlogin.fn.showForgotPasswordForm(c);
					}
				});
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
							pid: tx_ajaxlogin.storagePid
						}, input),
						error: function(a,b,c) {
							Ajaxlogin.fn.showLoginForm(a);
						},
						success: function(a,b,c){
							Ajaxlogin.fn.doReloadOrRedirect();
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
					var input = Ajaxlogin.fn.resolveFormData($(this));
					
					$.ajax({
						url: tx_ajaxlogin.api.User.create,
						cache: false,
						type: 'POST',
						data: input,
						error: function(a,b,c) {
							Ajaxlogin.fn.showSignupForm(a);
						},
						success: function(a,b,c) {
							Ajaxlogin.fn.showSignupForm(c);
						}
					});
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
						},
						success: function(a,b,c) {
							Ajaxlogin.fn.showForgotPasswordForm(c);
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
			doReloadOrRedirect: function() {
				if(tx_ajaxlogin.doReloadOnSuccess == 1) {
					window.location.href = window.location.href;
				}
			}
		}
	};
	
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
})(jQuery);