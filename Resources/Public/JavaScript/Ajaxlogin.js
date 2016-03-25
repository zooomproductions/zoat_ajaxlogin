var Ajaxlogin = Ajaxlogin || {};

( function( $ ) {
    Ajaxlogin = {
        _eventListeners: {},

        // Localstorage dedicated to cache login states and according responses
        storage: {

            // Null implementation for browsers that don't support localStorage
            // this will be replaced later on if the browser supports it
            set: function() {},
            get: function() {},
            isSet: function() { return false; },
            removeAll: function() {}
        },
        User: {
            info: function() {
                if ( tx_zoatajaxlogin.api.User.info ) {
                    if ( !Ajaxlogin.storage.isSet( 'responseHeader' ) ) {
                        $.ajax({
                            url: tx_zoatajaxlogin.api.User.info,
                            cache: false,
                            error: function( a, b, c ) {

                                // Login form, if user is not logged in
                                Ajaxlogin.storage.set( 'showView', a );
                                Ajaxlogin.storage.set( 'responseToken', a.getResponseHeader( 'X-Ajaxlogin-formToken' ) );
                                Ajaxlogin.storage.set( 'responseHeader', a.getResponseHeader( 'X-Ajaxlogin-view' ) );

                                Ajaxlogin.fn.showView( a );
                                Ajaxlogin.event.fire( 'widget_load' );
                            },
                            success: function( a, b, c ) {

                                // Information page, if user is logged in
                                Ajaxlogin.storage.removeAll();
                                Ajaxlogin.storage.set( 'showView', c );
                                Ajaxlogin.storage.set( 'responseToken', c.getResponseHeader( 'X-Ajaxlogin-formToken' ) );
                                Ajaxlogin.storage.set( 'responseHeader', c.getResponseHeader( 'X-Ajaxlogin-view' ) );

                                Ajaxlogin.fn.showView( c );
                                Ajaxlogin.event.fire( 'widget_load' );
                            }
                        });
                    } else {
                        Ajaxlogin.fn.showView();
                    }
                } else {
                    // Check the plugin.tx_zoatajaxlogin.view.ajaxPid property
                }
            },
            login: function() {
                if ( tx_zoatajaxlogin.api.User.login ) {
                    $.ajax({
                        url: tx_zoatajaxlogin.api.User.login,
                        cache: false,
                        error: function( a, b, c ) {
                            Ajaxlogin.fn.showView( a );
                            Ajaxlogin.event.fire( 'widget_load' );
                        },
                        success: function( a, b, c ) {
                            Ajaxlogin.fn.showView( c );
                            Ajaxlogin.event.fire( 'widget_load' );
                        }
                    });
                }
            },
            logout: function() {
                if ( tx_zoatajaxlogin.api.User.logout ) {
                    $.ajax({
                        url: tx_zoatajaxlogin.api.User.logout,
                        cache: false,
                        error: function( a, b, c ) {
                            Ajaxlogin.fn.showView( a );
                            Ajaxlogin.event.fire( 'widget_load' );
                        },
                        success: function( a, b, c ) {
                            Ajaxlogin.event.fire( 'logout_success', [ c ] );
                            Ajaxlogin.fn.showView( c );
                            Ajaxlogin.event.fire( 'widget_load' );
                        }
                    });
                }
            },
            'new': function() {
                if ( tx_zoatajaxlogin.api.User[ 'new' ] ) {
                    Ajaxlogin.storage.removeAll(); // Remove "responseHeader"
                    $.ajax({
                        url: tx_zoatajaxlogin.api.User[ 'new' ],
                        cache: false,
                        error: function( a, b, c ) {
                            Ajaxlogin.fn.showView( a );
                            Ajaxlogin.event.fire( 'widget_load' );
                        },
                        success: function( a, b, c ) {
                            Ajaxlogin.fn.showView( c );
                            Ajaxlogin.event.fire( 'widget_load' );
                        }
                    });
                }
            },
            forgotPassword: function() {
                if ( tx_zoatajaxlogin.api.User.forgotPassword ) {
                    Ajaxlogin.storage.removeAll(); // Remove "responseHeader"
                    $.ajax({
                        url: tx_zoatajaxlogin.api.User.forgotPassword,
                        cache: false,
                        error: function( a, b, c ) {
                            Ajaxlogin.fn.showView( a );
                            Ajaxlogin.event.fire( 'widget_load' );
                        },
                        success: function( a, b, c ) {
                            Ajaxlogin.fn.showView( c );
                            Ajaxlogin.event.fire( 'widget_load' );
                        }
                    });
                }
            }
        },
        fn: {
            showView: function( c ) {
                var view;
                if ( Ajaxlogin.storage.isSet( 'responseHeader' ) ) {
                    view = Ajaxlogin.storage.get( 'responseHeader' );
                } else {
                    view = c.getResponseHeader( 'X-Ajaxlogin-view' );
                }
                switch ( view ) {
                    case 'login':
                        Ajaxlogin.fn.showLoginForm( c );
                        break;
                    case 'info':
                        Ajaxlogin.fn.showUserInfo( c );
                        break;
                    case 'new':
                        Ajaxlogin.fn.showSignupForm( c );
                        break;
                    case 'forgotPassword':
                        Ajaxlogin.fn.showForgotPasswordForm( c );
                        break;
                    default:
                        break;
                }
            },
            showLoginForm: function( response ) {
                var view, token;
                if ( Ajaxlogin.storage.isSet( 'showView' ) ) {
                    view = Ajaxlogin.storage.get( 'showView' ).responseText;
                } else {
                    view = response.responseText;
                }

                $( tx_zoatajaxlogin.statusLabel ).html( tx_zoatajaxlogin.ll.status_unauthorized );
                $( tx_zoatajaxlogin.placeholder ).html( view ).find( 'a[rel^=\'tx_zoatajaxlogin\']' ).Ajaxlogin();

                if ( Ajaxlogin.storage.isSet( 'responseToken' ) ) {
                    token = Ajaxlogin.storage.get( 'responseToken' );
                } else {
                    token = response.getResponseHeader( 'X-Ajaxlogin-formToken' );
                }

                var formEl = $( '#' + token );
                formEl.submit( function( event ) {

                    // Stop the form from submitting.
                    event.preventDefault();

                    var formReady = $.Deferred();

                    // If the object exists, use rsaauth extension to encrypt the password.
                    if ( typeof TYPO3FrontendLoginFormRsaEncryptionAjax !== 'undefined' &&
                         typeof TYPO3FrontendLoginFormRsaEncryptionPublicKeyUrl !== 'undefined' ) {
                        // Set the form ready object to the one returned from the encrypt function
                        formReady = TYPO3FrontendLoginFormRsaEncryptionAjax.encrypt( this, TYPO3FrontendLoginFormRsaEncryptionPublicKeyUrl );
                    } else {
                        formReady.resolve();
                    }

                    // When the async stuff is done, then submit the form.
                    formReady.done(function() {

                        // Get all the form data to submit.
                        var input = Ajaxlogin.fn.resolveFormData( formEl );
                        // Submit the form with ajax
                        $.ajax({
                            url: tx_zoatajaxlogin.api.User.authenticate,
                            cache: false,
                            type: 'POST',
                            data: $.extend({
                                logintype: 'login',
                                pid: tx_zoatajaxlogin.storagePid,
                                referer: window.location.href,
                                redirectUrl: tx_zoatajaxlogin.redirect_url
                            }, input ),
                            error: function( a, b, c ) {

                                // If login is invalid
                                Ajaxlogin.event.fire( 'login_error', [ a ] );
                                Ajaxlogin.fn.showView( a );
                            },
                            success: function( a, b, c ) {

                                // If login is valid
                                Ajaxlogin.event.fire( 'login_success', [ c ] );
                                Ajaxlogin.fn.showView( c );
                            }
                        });
                    });
                });
            },
            showSignupForm: function( response ) {
                $( tx_zoatajaxlogin.statusLabel ).html( tx_zoatajaxlogin.ll.status_unauthorized );
                $( tx_zoatajaxlogin.placeholder ).html( response.responseText ).find( 'a[rel^=\'tx_zoatajaxlogin\']' ).Ajaxlogin();

                var formEl = $( '#' + response.getResponseHeader( 'X-Ajaxlogin-formToken' ) );

                formEl.submit( function( event ) {
                    event.preventDefault();

                    if ( Ajaxlogin.validate.signup( $( this ) ) ) {
                        var input = Ajaxlogin.fn.resolveFormData( $( this ) );

                        $.ajax({
                            url: tx_zoatajaxlogin.api.User.create,
                            cache: false,
                            type: 'POST',
                            data: $.extend({
                                referer: window.location.href,
                                redirectUrl: tx_zoatajaxlogin.redirect_url
                            }, input ),
                            error: function( a, b, c ) {
                                Ajaxlogin.event.fire( 'signup_error', [ a ] );
                                Ajaxlogin.fn.showView( a );
                            },
                            success: function( a, b, c ) {
                                Ajaxlogin.event.fire( 'signup_success', [ c ] );
                                Ajaxlogin.fn.showView( c );
                            }
                        });
                    }
                });
            },
            showUserInfo: function( response ) {
                var view;
                if ( Ajaxlogin.storage.isSet( 'showView' ) ) {
                    view = Ajaxlogin.storage.get( 'showView' ).responseText;
                } else {
                    view = response.responseText;
                }

                $( tx_zoatajaxlogin.statusLabel ).html( tx_zoatajaxlogin.ll.status_authenticated );
                $( tx_zoatajaxlogin.placeholder ).html( view ).find( 'a[rel^=\'tx_zoatajaxlogin\']' ).Ajaxlogin();
            },
            showForgotPasswordForm: function( response ) {
                $( tx_zoatajaxlogin.statusLabel ).html( tx_zoatajaxlogin.ll.status_unauthorized );
                $( tx_zoatajaxlogin.placeholder ).html( response.responseText ).find( 'a[rel^=\'tx_zoatajaxlogin\']' ).Ajaxlogin();

                var formEl = $( '#' + response.getResponseHeader( 'X-Ajaxlogin-formToken' ) );

                formEl.submit( function( event ) {
                    event.preventDefault();
                    var input = Ajaxlogin.fn.resolveFormData( $( this ) );

                    $.ajax({
                        url: tx_zoatajaxlogin.api.User.resetPassword,
                        cache: false,
                        type: 'POST',
                        data: input,
                        error: function( a, b, c ) {
                            Ajaxlogin.fn.showView( a );
                            Ajaxlogin.event.fire( 'widget_load' );
                        },
                        success: function( a, b, c ) {
                            Ajaxlogin.fn.showView( c );
                            Ajaxlogin.event.fire( 'widget_load' );
                        }
                    });
                });
            },
            resolveFormData: function( formEl ) {
                var input = {};
                formEl.find( 'input[type!="checkbox"]' ).each( function() {
                    var key = $( this ).attr( 'name' );
                    input[ key ] = $.trim( $( this ).val() );
                });
                formEl.find( 'input[type="checkbox"]:checked' ).each( function() {
                    var key = $( this ).attr( 'name' );
                    input[ key ] = $.trim( $( this ).val() );
                });
                return input;
            },
            doReloadOrRedirect: function( response ) {
                var redirectUrl = response.getResponseHeader( 'X-Ajaxlogin-redirectUrl' );

                // Remove logintype form redirect url @see #35589
                var logoutRegexp = /[&]{0,1}logintype=logout/;
                if ( redirectUrl ) {
                    window.location.href = redirectUrl;
                } else if ( tx_zoatajaxlogin.doReloadOnSuccess == 1 ) {
                    var url = window.location.href.replace( logoutRegexp, '' );
                    window.location.href = url;
                }
            }
        },
        validate: {
            signup: function( form ) {

                var result = true;

                var errorClassName = tx_zoatajaxlogin.validation.errorClassName;

                $.each( tx_zoatajaxlogin.validation.confirmationFieldsets, function() {

                    var field = true;
                    var value = form.find( this[ 0 ] ).val();
                    var check = form.find( this[ 1 ] ).val();

                    if ( !value ) {
                        result = false;
                        field = false;
                    } else if ( value != check ) {
                        result = false;
                        field = false;
                    }

                    if ( field === false ) {
                        form.find( this[ 0 ] ).addClass( errorClassName );
                        form.find( this[ 1 ] ).addClass( errorClassName );
                    } else {
                        form.find( this[ 0 ] ).removeClass( errorClassName );
                        form.find( this[ 1 ] ).removeClass( errorClassName );
                    }
                });

                return result;
            }
        },
        event: {
            addListener: function( type, listener ) {
                if ( typeof Ajaxlogin._eventListeners[ type ] == 'undefined' ) {
                    Ajaxlogin._eventListeners[ type ] = [];
                }

                Ajaxlogin._eventListeners[ type ].push( listener );
            },
            addListenerOnce: function( type, listener ) {
                var add = true;

                if ( typeof Ajaxlogin._eventListeners[ type ] == 'undefined' ) {
                    Ajaxlogin._eventListeners[ type ] = [];
                }

                var listeners = Ajaxlogin._eventListeners[ type ];
                for ( var i = 0, len = listeners.length; i < len; i++ ) {
                    if ( listeners[ i ] === listener ) {
                        add = false;
                        break;
                    }
                }

                if ( add ) {
                    Ajaxlogin._eventListeners[ type ].push( listener );
                }
            },
            fire: function( event, args ) {
                if ( typeof event == 'string' ) {
                    event = { type: event };
                }
                if ( !event.target ) {
                    event.target = this;
                }

                if ( !event.type ) {  //Falsy
                    throw new Error( 'Event object missing \'type\' property.' );
                }

                if ( Ajaxlogin._eventListeners[ event.type ] instanceof Array ) {
                    var listeners = Ajaxlogin._eventListeners[ event.type ];
                    for ( var i = 0, len = listeners.length; i < len; i++ ) {
                        if ( typeof listeners[ i ] === 'object' || typeof listeners[ i ] === 'function' ) {
                            listeners[ i ].apply( event, args || [] ); // IE requires args to be an array (not undefined)
                        }
                    }
                }
            },
            removeListener: function( type, listener ) {
                if ( Ajaxlogin._eventListeners[ type ] instanceof Array ) {
                    var listeners = Ajaxlogin._eventListeners[ type ];
                    for ( var i = 0, len = listeners.length; i < len; i++ ) {
                        if ( listeners[ i ] === listener ) {
                            listeners.splice( i, 1 );
                            break;
                        }
                    }
                }
            }
        },
        Cookie: {
            create: function( name, value, days ) {
                if ( days ) {
                    var date = new Date();
                    date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
                    var expires = '; expires=' + date.toGMTString();
                } else var expires = '';
                document.cookie = name + '=' + value + expires + '; path=/';
            },
            read: function( name ) {
                var nameEQ = name + '=';
                var ca = document.cookie.split( ';' );
                for ( var i = 0; i < ca.length; i++ ) {
                    var c = ca[ i ];
                    while ( c.charAt( 0 ) == ' ' ) c = c.substring( 1, c.length );
                    if ( c.indexOf( nameEQ ) == 0 ) return c.substring( nameEQ.length, c.length );
                }
                return null;
            },
            erase: function( name ) {
                this.create( name, '', -1 );
            }
        }
    };

    if ( window.localStorage && JSON ) {

        // If: browser supports localStorage, replace the dummy Ajaxlogin.storage. with a useful one
        Ajaxlogin.storage = {

            // Don't use sessionStorage - it is not shared amongst browser tabs
            store: window.localStorage,
            defaultTTL: 300,
            get: function( key ) {
                var d = this.store.getItem( key );
                if ( d ) {
                    d = JSON.parse( d );
                    if ( ( new Date ).getTime() > d.ts + this.defaultTTL * 1000 ) {

                        // If: cache expired
                        this.store.removeItem( key );
                        return;
                    }
                    return d.value;
                }
            },
            set: function( key, value ) {
                var d = {
                    value: value,
                    ts: ( new Date ).getTime() // Milliseconds!!
                };
                this.store.setItem( key, JSON.stringify( d ) );
            },
            isSet: function( key ) {
                return this.get( key ) != undefined;
            },
            removeAll: function() {
                this.store.removeItem( 'responseHeader' );
                this.store.removeItem( 'responseToken' );
                this.store.removeItem( 'showView' );
            }
        };

        if ( Ajaxlogin.storage.isSet( 'responseHeader' ) ) {

            // Clear localStorage if the "ajaxlogin_status" cookie was changed
            // This is necessary to recognize a browser restart (session cookie is removed, but localStorage persists)
            if ( Ajaxlogin.Cookie.read( 'ajaxlogin_status' ) != '1' ) {
                if ( Ajaxlogin.storage.get( 'responseHeader' ) != 'login' ) {
                    Ajaxlogin.storage.removeAll();
                }
            }
        }
        if ( !Ajaxlogin.Cookie.read( 'PHPSESSID' ) ) {

            // PHPSESSID is required for the RSA login. Never cache if that cookie is not set, because login will fail.
            Ajaxlogin.storage.removeAll();
        }
    }

    Ajaxlogin.event.addListener( 'login_success', Ajaxlogin.fn.doReloadOrRedirect );
    Ajaxlogin.event.addListener( 'logout_success', Ajaxlogin.fn.doReloadOrRedirect );
    Ajaxlogin.event.addListener( 'signup_success', Ajaxlogin.fn.doReloadOrRedirect );

    Ajaxlogin.event.addListener( 'login_success', function() {
        Ajaxlogin.storage.removeAll();
        Ajaxlogin.Cookie.create( 'ajaxlogin_status', '1' );
    });
    Ajaxlogin.event.addListener( 'login_error', function() {
        Ajaxlogin.storage.removeAll();
    });
    Ajaxlogin.event.addListener( 'logout_success', function() {
        Ajaxlogin.storage.removeAll();
        Ajaxlogin.Cookie.erase( 'ajaxlogin_status' );
    });

    $.fn.Ajaxlogin = function() {
        this.click( function( event ) {
            event.preventDefault();

            var actionRegExp = /\[(?:.*)\]/;
            var res = actionRegExp.exec( $( this ).attr( 'rel' ) );
            switch ( res.toString() ) {
                case '[signup]':
                    Ajaxlogin.User[ 'new' ]();
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

    $( document ).ready( Ajaxlogin.User.info );

    $( document ).ready( function() {
        if ( Ajaxlogin.Cookie.read( 'ajaxlogin_status' ) == '1' ) {

            // TODO: add removal of the ajaxlogin_status cookie on logout action on server and ONLY THEN uncomment this
            // NOTE: not the most important feature, as it's kinda buggy, disabling it atm
            //$(tx_zoatajaxlogin.statusLabel).html('<a href="'+tx_zoatajaxlogin.accountPage+'">' + tx_zoatajaxlogin.ll.status_authenticated+'</a>');
        }
    });
})( jQuery );
