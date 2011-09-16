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
					Ajaxlogin.fn.encryptString(input.pass, function(res){
						input.pass = res;
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
				});
			},
			showSignupForm: function(response) {
				$(tx_ajaxlogin.statusLabel).html(tx_ajaxlogin.ll.status_unauthorized);
				$(tx_ajaxlogin.placeholder).html(response.responseText).find("a[rel^='tx_ajaxlogin']").Ajaxlogin();
				
				var formEl = $('#' + response.getResponseHeader('X-Ajaxlogin-formToken'));
				
				formEl.submit(function(event) {
					event.preventDefault();
					var input = Ajaxlogin.fn.resolveFormData($(this));
					
					Ajaxlogin.fn.encryptString(input['tx_ajaxlogin_widget[user][password]'], function(res) {
						input['tx_ajaxlogin_widget[user][password]'] = res;
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
				});
			},
			showUserInfo: function(response) {
				$(tx_ajaxlogin.statusLabel).html(tx_ajaxlogin.ll.status_authenticated);
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
			encryptString: function(val, callback) {
				var loaded = 0;
				$.each(tx_ajaxlogin.scripts.rsaauth, function(i, v) {
					$.ajax({
						url: v,
						cache: true,
						dataType: 'script',
						success: function() {
							loaded++;
							
							if(loaded == tx_ajaxlogin.scripts.rsaauth.length) {
								$.ajax({
									url: tx_ajaxlogin.api.Utility.createEncryptionkey,
									cache: false,
									success: function(response) {
										var rsa = new RSAKey();
										rsa.setPublic(response.n, response.e);
										var res = rsa.encrypt(val);
										if (res) {
											res = 'rsa:' + hex2b64(res);
											callback.call(this, res);
										}
									}
								});
							}
						}
					});
				});
				return val;
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
	
	$(document).ready(function() {
		$(tx_ajaxlogin.editPasswordForm).live('submit.tx_ajaxlogin', function(event) {
			event.preventDefault();
			var input = Ajaxlogin.fn.resolveFormData($(this));
			
			var pw = {
				n: input['tx_ajaxlogin_widget[password][new]'],
				c: input['tx_ajaxlogin_widget[password][check]']
			};
			
			Ajaxlogin.fn.encryptString($.param(pw), function(res) {
				input['tx_ajaxlogin_widget[password][encrypted]'] = res;
				input['tx_ajaxlogin_widget[password][check]'] = '';
				input['tx_ajaxlogin_widget[password][new]'] = '';
				$.ajax({
					url: tx_ajaxlogin.api.User.updatePassword,
					cache: false,
					type: 'POST',
					data: input,
					error: function(a,b,c) {
						$(tx_ajaxlogin.profileSection).replaceWith(a.responseText);
					},
					success: function(a,b,c) {
						$(tx_ajaxlogin.profileSection).replaceWith(c.responseText);
						$(document).unbind('submit.tx_ajaxlogin');
					}
				});
			});
		});
	});
})(jQuery);